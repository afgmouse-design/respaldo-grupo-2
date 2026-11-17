<?php

namespace App\Models\OrganizacionInstitucional\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: CondicionSede (tabla `condicion_sede`).
 *
 * Propósito: Catálogo institucional que clasifica la condición o estatus operativo
 * de la universidad y sus sedes en `sogac_mio.sql` (ej. Principal, Extensión, Núcleo).
 *
 * Historial:
 *  - 2026-11-14: Creación reemplazando el modelo legacy `Condicion` (`condicion`),
 *    alineado a `condicion_sede(id_cse)` y `status_id`.
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica
 *    conservando el registro en PostgreSQL.
 */
class CondicionSede extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'condicion_sede';
    protected $primaryKey = 'id_cse';
    protected $moduloAuditoria = 'CondicionSede';

    protected $fillable = [
        'nombre_cse',
        'descripcion_cse',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_cse'    => 'integer',
        'status_id' => 'integer',
    ];

    public function sedes(): HasMany
    {
        return $this->hasMany(Sede::class, 'cse_id', 'id_cse');
    }

    public function universidades(): HasMany
    {
        return $this->hasMany(Universidad::class, 'cse_id', 'id_cse');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('condicion_sede')
            ->select(['id_cse', 'nombre_cse'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_cse', 'asc')
            ->get();
    }

    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        return DB::table('condicion_sede')
            ->select('condicion_sede.*', 'condicion_sede.id_cse as id')
            ->whereNull('condicion_sede.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('condicion_sede.status_id', $statusId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('condicion_sede.nombre_cse', 'ilike', "%{$search}%")
                        ->orWhere('condicion_sede.descripcion_cse', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('condicion_sede.id_cse', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public static function alternarEstatus(int $id, bool $activo, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_cse', $id)->firstOrFail();

        return $registro->update([
            'status_id'  => $activo ? self::STATUS_ACTIVO : self::STATUS_INACTIVO,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
