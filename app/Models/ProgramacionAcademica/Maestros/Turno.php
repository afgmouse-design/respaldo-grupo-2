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
 * Modelo: Turno Académico (tabla `turno`).
 *
 * Propósito: Catálogo maestro de turnos académicos (Diurno, Nocturno, Fines de Semana).
 * Utiliza únicamente las columnas oficiales (`codigo_tur`, `nombre_tur`) y las extensiones aprobadas
 * (`hora_inicio_tur`, `hora_fin_tur`, `descripcion_tur`), excluyendo las columnas legacy (`codigo`, `nombre`, `status`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, eliminación de uso de columnas legacy duplicadas).
 *  - 2026-11-16: Añadido `HandlesSoftDeleteWithStatus` y `liberarValoresUnicosSiEliminados()`.
 */
class Turno extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    /**
     * Libera `codigo_tur` y `nombre_tur` si pertenecen a turnos previamente eliminados lógicamente.
     */
    public static function liberarValoresUnicosSiEliminados(string $codigo, string $nombre, ?int $ignoreId = null): void
    {
        static::liberarValorUnicoEliminado(
            'turno',
            'id_tur',
            'codigo_tur',
            trim($codigo),
            $ignoreId,
            20,
            "Ya existe un turno activo con el código '{$codigo}'."
        );

        static::liberarValorUnicoEliminado(
            'turno',
            'id_tur',
            'nombre_tur',
            trim($nombre),
            $ignoreId,
            100,
            "Ya existe un turno activo con el nombre '{$nombre}'."
        );
    }

    protected $table = 'turno';

    protected $primaryKey = 'id_tur';

    protected $moduloAuditoria = 'Turno';

    protected $fillable = [
        'codigo_tur',
        'nombre_tur',
        'hora_inicio_tur',
        'hora_fin_tur',
        'descripcion_tur',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tur'    => 'integer',
        'status_id' => 'integer',
    ];

    /**
     * Secciones vinculadas al turno.
     */
    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'tur_id', 'id_tur');
    }

    /**
     * Scope para turnos activos (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Listado de turnos activos para selectores.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('turno')
            ->select(['id_tur', 'codigo_tur', 'nombre_tur', 'hora_inicio_tur', 'hora_fin_tur'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_tur')
            ->get();
    }

    /**
     * Listado paginado con filtros (`search`, `status`).
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        return DB::table('turno')
            ->select([
                'id_tur',
                'id_tur as id',
                'codigo_tur',
                'nombre_tur',
                'hora_inicio_tur',
                'hora_fin_tur',
                'descripcion_tur',
                'status_id',
                'created_at',
                'updated_at',
            ])
            ->whereNull('deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('codigo_tur', 'ilike', "%{$search}%")
                        ->orWhere('nombre_tur', 'ilike', "%{$search}%")
                        ->orWhere('descripcion_tur', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('id_tur', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica sobre `status_id` (1 = activo, 2 = inactivo).
     */
    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_tur', $id)->firstOrFail();

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
