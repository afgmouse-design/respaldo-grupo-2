<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Eje Curricular (tabla `eje_curricular`).
 *
 * Propósito: Gestiona los ejes curriculares formativos de los planes de estudio.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, sin `status` booleano)
 *    y corrección de filtro de estatus sobre `$busqueda`.
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class EjeCurricular extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'eje_curricular';
    protected $primaryKey = 'id_ecu';
    protected $moduloAuditoria = 'EjeCurricular';

    protected $fillable = [
        'nombre_ecu',
        'codigo_ecu',
        'descripcion_ecu',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_ecu'     => 'integer',
        'status_id'  => 'integer',
        'created_id' => 'integer',
        'updated_id' => 'integer',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array|string|null $busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $statusId = static::resolverFiltroStatusId(is_array($busqueda) ? ($busqueda['status'] ?? $busqueda['status_id'] ?? null) : null);

        $query = DB::table('eje_curricular')
            ->select([
                'id_ecu',
                'nombre_ecu',
                'codigo_ecu',
                'descripcion_ecu',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ])
            ->whereNull('deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_ecu', 'ilike', "%{$search}%")
                    ->orWhere('codigo_ecu', 'ilike', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('id_ecu', 'desc')
            ->paginate($porPagina)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->id_ecu = (int) $item->id_ecu;
            $item->status_id = (int) $item->status_id;

            return $item;
        });

        return $paginator;
    }

    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = DB::table('eje_curricular')->where('id_ecu', $id)->whereNull('deleted_at')->first();
        if (! $registro) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id]);
        }

        DB::table('eje_curricular')
            ->where('id_ecu', $id)
            ->update([
                'status_id'  => $estado,
                'updated_id' => $userId ?? auth()->id(),
                'updated_at' => now(),
            ]);

        return true;
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('eje_curricular')
            ->select(['id_ecu', 'codigo_ecu', 'nombre_ecu'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_ecu', 'asc')
            ->get();
    }
}
