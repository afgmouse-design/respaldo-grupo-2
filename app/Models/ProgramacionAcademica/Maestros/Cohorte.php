<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Academico\LapsoAcademico;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Cohorte (tabla `cohorte`).
 *
 * Propósito: Agrupación temporal de estudiantes de nuevo ingreso vinculada a un Lapso Académico.
 * Baja lógica mediante `status_id` (1 activo, 2 inactivo) y regla de única cohorte activa.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id` sin booleano `status`, `SoftDeletes`);
 *    se corrigió el join y la validación ascendente sobre `lapso_academico.id_laa` y `nombre_laa`
 *    (antes consultaba columnas inexistentes `id_la`, `nombre_la` y `estado_la`).
 */
class Cohorte extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'cohorte';

    protected $primaryKey = 'id_coh';

    protected $moduloAuditoria = 'Cohorte';

    protected $fillable = [
        'nombre_coh',
        'fecha_inicio_coh',
        'laa_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_coh'           => 'integer',
        'fecha_inicio_coh' => 'date',
        'laa_id'           => 'integer',
        'status_id'        => 'integer',
    ];

    /**
     * Relación con el Lapso Académico de la cohorte.
     */
    public function lapsoAcademico(): BelongsTo
    {
        return $this->belongsTo(LapsoAcademico::class, 'laa_id', 'id_laa');
    }

    /**
     * Relación con las asignaciones de esta cohorte por sede y programa.
     */
    public function cohortesSede(): HasMany
    {
        return $this->hasMany(CohorteSede::class, 'coh_id', 'id_coh');
    }

    /**
     * Scope para cohortes activas (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Listado paginado con filtros (`search`, `status`, `laa_id`).
     * Expone `id` (alias de `id_coh`) y `nombre_laa`.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);
        $laaId    = $filtros['laa_id'] ?? null;

        return DB::table('cohorte')
            ->leftJoin('lapso_academico', 'lapso_academico.id_laa', '=', 'cohorte.laa_id')
            ->select(
                'cohorte.*',
                'cohorte.id_coh as id',
                'lapso_academico.nombre_laa',
                'lapso_academico.codigo_laa'
            )
            ->whereNull('cohorte.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('cohorte.status_id', $statusId))
            ->when(! empty($laaId) && $laaId !== 'all', fn ($q) => $q->where('cohorte.laa_id', (int) $laaId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('cohorte.nombre_coh', 'ilike', "%{$search}%")
                        ->orWhere('lapso_academico.nombre_laa', 'ilike', "%{$search}%")
                        ->orWhere('lapso_academico.codigo_laa', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('cohorte.id_coh', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica: fija `status_id` (1 = activo, 2 = inactivo).
     * Aplica la regla "Única Cohorte Activa", protección ascendente hacia el Lapso Académico
     * y desactivación en cascada sobre `cohorte_sede`.
     *
     * @throws \DomainException
     */
    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($id, $estado, $userId) {
            $registro = static::query()->where('id_coh', $id)->firstOrFail();
            $usuarioId = $userId ?? auth()->id();

            if ($estado === self::STATUS_ACTIVO) {
                if (! empty($registro->laa_id)) {
                    $lapso = DB::table('lapso_academico')
                        ->where('id_laa', $registro->laa_id)
                        ->whereNull('deleted_at')
                        ->first();

                    if (! $lapso || (int) $lapso->status_id !== self::STATUS_ACTIVO) {
                        throw new \DomainException('No se puede activar la cohorte porque el Lapso Académico al que pertenece se encuentra inactivo.');
                    }
                }

                $otraActiva = static::query()
                    ->where('id_coh', '!=', $id)
                    ->where('status_id', self::STATUS_ACTIVO)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($otraActiva) {
                    throw new \DomainException('Ya existe otra Cohorte activa en el sistema. Desactive la cohorte en curso antes de activar otra.');
                }
            }

            $actualizado = (bool) $registro->update([
                'status_id'  => $estado,
                'updated_id' => $usuarioId,
            ]);

            if ($estado === self::STATUS_INACTIVO) {
                DB::table('cohorte_sede')
                    ->where('coh_id', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'status_id'  => self::STATUS_INACTIVO,
                        'updated_id' => $usuarioId,
                        'updated_at' => now(),
                    ]);
            }

            return $actualizado;
        });
    }
}
