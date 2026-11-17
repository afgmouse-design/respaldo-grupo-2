<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Cohorte por Sede (tabla `cohorte_sede`).
 *
 * Propósito: Distribución de ingresantes por cohorte y oferta de sede-programa (`sep_id` -> `sede_programa.id_spr`).
 * Baja lógica mediante `status_id` (1 activo, 2 inactivo).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`sep_id -> sede_programa.id_spr`, `programa`, `status_id` sin booleano `status`, `SoftDeletes`);
 *    se corrigió la verificación de cohorte activa usando `status_id` y la relación `sedePrograma()`.
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class CohorteSede extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'cohorte_sede';

    protected $primaryKey = 'id_cos';

    protected $moduloAuditoria = 'Cohorte Sede';

    protected $fillable = [
        'nombre_cos',
        'coh_id',
        'sep_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_cos'    => 'integer',
        'coh_id'    => 'integer',
        'sep_id'    => 'integer',
        'status_id' => 'integer',
    ];

    /**
     * Relación con la Cohorte matriz.
     */
    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'coh_id', 'id_coh');
    }

    /**
     * Relación con la Oferta Sede-Programa (`sep_id` -> `sede_programa.id_spr`).
     */
    public function sedePrograma(): BelongsTo
    {
        return $this->belongsTo(SedePrograma::class, 'sep_id', 'id_spr');
    }

    /**
     * Scope para asignaciones activas (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Listado paginado con filtros (search, status, coh_id, sep_id).
     * Expone `id` (alias de `id_cos`), `nombre_coh`, `nombre_sed`, `nombre_prg` y `oferta_academica`.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);
        $cohId    = $filtros['coh_id'] ?? null;
        $sepId    = $filtros['sep_id'] ?? null;

        return DB::table('cohorte_sede')
            ->leftJoin('cohorte', 'cohorte.id_coh', '=', 'cohorte_sede.coh_id')
            ->leftJoin('sede_programa', 'sede_programa.id_spr', '=', 'cohorte_sede.sep_id')
            ->leftJoin('sede', 'sede.id_sed', '=', 'sede_programa.sed_id')
            ->leftJoin('programa', 'programa.id_prg', '=', 'sede_programa.prg_id')
            ->select(
                'cohorte_sede.*',
                'cohorte_sede.id_cos as id',
                'cohorte.nombre_coh',
                'sede.nombre_sed',
                'programa.nombre_prg',
                DB::raw("COALESCE(sede.nombre_sed || ' - ' || programa.nombre_prg, '—') as oferta_academica")
            )
            ->whereNull('cohorte_sede.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('cohorte_sede.status_id', $statusId))
            ->when(! empty($cohId) && $cohId !== 'all', fn ($q) => $q->where('cohorte_sede.coh_id', (int) $cohId))
            ->when(! empty($sepId) && $sepId !== 'all', fn ($q) => $q->where('cohorte_sede.sep_id', (int) $sepId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('cohorte_sede.nombre_cos', 'ilike', "%{$search}%")
                        ->orWhere('cohorte.nombre_coh', 'ilike', "%{$search}%")
                        ->orWhere('sede.nombre_sed', 'ilike', "%{$search}%")
                        ->orWhere('programa.nombre_prg', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('cohorte_sede.id_cos', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica: fija `status_id` (1 = activo, 2 = inactivo).
     * Impide activar si la Cohorte padre se encuentra inactiva.
     *
     * @throws \DomainException
     */
    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_cos', $id)->firstOrFail();

        if ($estado === self::STATUS_ACTIVO && ! empty($registro->coh_id)) {
            $cohorte = DB::table('cohorte')
                ->where('id_coh', $registro->coh_id)
                ->whereNull('deleted_at')
                ->first();

            if (! $cohorte || (int) $cohorte->status_id !== self::STATUS_ACTIVO) {
                throw new \DomainException('No se puede activar la asignación porque la Cohorte a la que pertenece se encuentra inactiva.');
            }
        }

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
