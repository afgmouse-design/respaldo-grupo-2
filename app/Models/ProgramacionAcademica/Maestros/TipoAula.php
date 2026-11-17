<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Tipo de Aula (tabla `tipo_aula`).
 *
 * Propósito: Catálogo independiente que clasifica los ambientes físicos
 * (aula teórica, laboratorio, taller, etc.). Es referenciado por `aula.tia_id`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a convenciones de `sogac_mio.sql` (`status_id` sin booleano `status`, `SoftDeletes`).
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class TipoAula extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'tipo_aula';

    protected $primaryKey = 'id_tia';

    protected $moduloAuditoria = 'Tipo de Aula';

    protected $fillable = [
        'nombre_tia',
        'codigo_tia',
        'descripcion_tia',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tia'    => 'integer',
        'status_id' => 'integer',
    ];

    /**
     * Relación con las aulas asociadas a este tipo de ambiente.
     */
    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'tia_id', 'id_tia');
    }

    /**
     * Scope para registros activos (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Obtiene los tipos de aula activos para selectores.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('tipo_aula')
            ->select(['id_tia', 'nombre_tia', 'codigo_tia'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_tia')
            ->get();
    }

    /**
     * Obtiene los tipos de aula paginados con filtros dinámicos (`search` y `status`/`status_id`).
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        return DB::table('tipo_aula')
            ->select('tipo_aula.*', 'id_tia as id')
            ->whereNull('deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('nombre_tia', 'ilike', "%{$search}%")
                        ->orWhere('codigo_tia', 'ilike', "%{$search}%")
                        ->orWhere('descripcion_tia', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('id_tia', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica: fija `status_id` (1 = activo, 2 = inactivo).
     */
    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_tia', $id)->firstOrFail();

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
