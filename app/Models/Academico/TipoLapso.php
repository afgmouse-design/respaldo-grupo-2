<?php

namespace App\Models\Academico;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Tipo de Lapso Académico (tabla `tipo_lapso`).
 *
 * Propósito: Catálogo maestro oficial en `sogac_mio.sql` que reemplaza al legacy
 * `tipo_periodo` / `tipo_lapsos` (Semestral, Trimestral, Anual, Trayecto).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `tipo_lapso(id_tla)` con las columnas aditivas
 *    aprobadas `nomenclatura_tla` y `descripcion_tla`, `status_id` y `SoftDeletes`.
 *  - 2026-11-16: Añadido `HandlesSoftDeleteWithStatus` y `liberarCodigoSiEliminado()`
 *    para baja lógica en interfaz y reutilización segura de `codigo_tla` UNIQUE.
 */
class TipoLapso extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    /**
     * Libera `codigo_tla` si pertenece a un tipo de lapso eliminado lógicamente (`tipo_lapso_codigo_tla_unique`).
     */
    public static function liberarCodigoSiEliminado(?string $codigo, ?int $ignoreId = null): void
    {
        if ($codigo === null || trim($codigo) === '') {
            return;
        }

        static::liberarValorUnicoEliminado(
            'tipo_lapso',
            'id_tla',
            'codigo_tla',
            trim($codigo),
            $ignoreId,
            20,
            "Ya existe un tipo de lapso activo con el código '{$codigo}'."
        );
    }

    protected $table = 'tipo_lapso';
    protected $primaryKey = 'id_tla';
    protected $moduloAuditoria = 'TipoLapso';

    protected $fillable = [
        'codigo_tla',
        'nombre_tla',
        'nomenclatura_tla',
        'duracion_semanas_tla',
        'descripcion_tla',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tla'               => 'integer',
        'duracion_semanas_tla' => 'integer',
        'status_id'            => 'integer',
    ];

    public function lapsosAcademicos(): HasMany
    {
        return $this->hasMany(LapsoAcademico::class, 'tla_id', 'id_tla');
    }

    public function trayectos(): HasMany
    {
        return $this->hasMany(Trayecto::class, 'tla_id', 'id_tla');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('tipo_lapso')
            ->select([
                'id_tla',
                'codigo_tla',
                'nombre_tla',
                'nomenclatura_tla',
                'duracion_semanas_tla',
            ])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_tla', 'asc')
            ->get();
    }

    public static function obtenerPaginados(array|string|null $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = is_array($filtros) ? ($filtros['search'] ?? null) : $filtros;
        $statusId = static::resolverFiltroStatusId(is_array($filtros) ? ($filtros['status'] ?? $filtros['status_id'] ?? null) : null);

        $query = DB::table('tipo_lapso')
            ->select('tipo_lapso.*', 'id_tla as id')
            ->whereNull('deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_tla', 'ilike', "%{$search}%")
                    ->orWhere('codigo_tla', 'ilike', "%{$search}%")
                    ->orWhere('nomenclatura_tla', 'ilike', "%{$search}%")
                    ->orWhere('descripcion_tla', 'ilike', "%{$search}%");
            });
        }

        if (is_array($filtros) && ! empty($filtros['fecha_inicio'])) {
            $fin = ! empty($filtros['fecha_fin']) ? $filtros['fecha_fin'] : now();
            $query->whereBetween('created_at', [$filtros['fecha_inicio'], $fin]);
        }

        return $query->orderBy('id_tla', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
