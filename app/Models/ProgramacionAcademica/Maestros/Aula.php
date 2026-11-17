<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\OrganizacionInstitucional\Maestros\Sede;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Aula (tabla `aula`).
 *
 * Propósito: Ambiente físico de una sede (`codigo_aula_aul`, `capacidad_maxima_aul`, `tipo_espacio_aul`),
 * clasificado por `TipoAula`. Baja lógica mediante `status_id` (1 activo, 2 inactivo).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id` sin booleano `status`, `SoftDeletes`, `UsesStatusCatalog`).
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class Aula extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'aula';

    protected $primaryKey = 'id_aul';

    protected $moduloAuditoria = 'Aula';

    protected $fillable = [
        'codigo_aula_aul',
        'capacidad_maxima_aul',
        'tipo_espacio_aul',
        'sed_id',
        'tia_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_aul'               => 'integer',
        'capacidad_maxima_aul' => 'integer',
        'sed_id'               => 'integer',
        'tia_id'               => 'integer',
        'status_id'            => 'integer',
    ];

    protected $appends = [
        'nombre_aul',
        'capacidad_aul',
    ];

    /**
     * Alias de solo lectura sobre `codigo_aula_aul` para compatibilidad de vistas y reportes.
     */
    public function getNombreAulAttribute(): ?string
    {
        return $this->codigo_aula_aul;
    }

    /**
     * Alias de solo lectura sobre `capacidad_maxima_aul`.
     */
    public function getCapacidadAulAttribute(): ?int
    {
        return $this->capacidad_maxima_aul;
    }

    /**
     * Relación con la Sede física del aula.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    /**
     * Relación con el Tipo de Aula.
     */
    public function tipoAula(): BelongsTo
    {
        return $this->belongsTo(TipoAula::class, 'tia_id', 'id_tia');
    }

    /**
     * Relación con los bloques horarios asignados a esta aula.
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'aul_id', 'id_aul');
    }

    /**
     * Scope para aulas activas (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Listado paginado con filtros (search, status, sed_id, tia_id).
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);
        $sedId    = $filtros['sed_id'] ?? null;
        $tiaId    = $filtros['tia_id'] ?? null;

        return DB::table('aula')
            ->leftJoin('sede', 'sede.id_sed', '=', 'aula.sed_id')
            ->leftJoin('tipo_aula', 'tipo_aula.id_tia', '=', 'aula.tia_id')
            ->select('aula.*', 'aula.id_aul as id', 'sede.nombre_sed', 'tipo_aula.nombre_tia')
            ->whereNull('aula.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('aula.status_id', $statusId))
            ->when(! empty($sedId) && $sedId !== 'all', fn ($q) => $q->where('aula.sed_id', (int) $sedId))
            ->when(! empty($tiaId) && $tiaId !== 'all', fn ($q) => $q->where('aula.tia_id', (int) $tiaId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('aula.codigo_aula_aul', 'ilike', "%{$search}%")
                        ->orWhere('aula.tipo_espacio_aul', 'ilike', "%{$search}%")
                        ->orWhere('sede.nombre_sed', 'ilike', "%{$search}%")
                        ->orWhere('tipo_aula.nombre_tia', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('aula.id_aul', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica: fija `status_id` (1 = activo, 2 = inactivo).
     */
    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_aul', $id)->firstOrFail();

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
