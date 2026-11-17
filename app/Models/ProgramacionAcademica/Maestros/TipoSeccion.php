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
 * Modelo: Tipo de Sección (tabla `tipo_seccion`).
 *
 * Propósito: Catálogo maestro de modalidades de sección (Regular, PER, Repitiente, Dirigido, Intensivo).
 *
 * Historial:
 *  - 2026-11-14: Alineado a convenciones de `sogac_mio.sql` (`status_id` sin booleano `status`, `SoftDeletes`, `UsesStatusCatalog`).
 *  - 2026-11-16: Añadido `HandlesSoftDeleteWithStatus` y `liberarCodigoSiEliminado()`.
 */
class TipoSeccion extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    /**
     * Libera `codigo_tis` si pertenece a un tipo de sección eliminado lógicamente.
     */
    public static function liberarCodigoSiEliminado(string $codigo, ?int $ignoreId = null): void
    {
        static::liberarValorUnicoEliminado(
            'tipo_seccion',
            'id_tis',
            'codigo_tis',
            trim($codigo),
            $ignoreId,
            20,
            "Ya existe un tipo de sección activo con el código '{$codigo}'."
        );
    }

    protected $table = 'tipo_seccion';

    protected $primaryKey = 'id_tis';

    protected $moduloAuditoria = 'TipoSeccion';

    protected $fillable = [
        'codigo_tis',
        'nombre_tis',
        'descripcion_tis',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tis'    => 'integer',
        'status_id' => 'integer',
    ];

    /**
     * Secciones asociadas a este tipo de sección.
     */
    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'tis_id', 'id_tis');
    }

    /**
     * Scope para tipos activos (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Listado de tipos de sección activos para selectores.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('tipo_seccion')
            ->select(['id_tis', 'codigo_tis', 'nombre_tis', 'descripcion_tis'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_tis')
            ->get();
    }

    /**
     * Listado paginado con filtros (`search`, `status`).
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        return DB::table('tipo_seccion')
            ->select('tipo_seccion.*', 'id_tis as id')
            ->whereNull('deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('codigo_tis', 'ilike', "%{$search}%")
                        ->orWhere('nombre_tis', 'ilike', "%{$search}%")
                        ->orWhere('descripcion_tis', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('id_tis', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica sobre `status_id` (1 = activo, 2 = inactivo).
     */
    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_tis', $id)->firstOrFail();

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
