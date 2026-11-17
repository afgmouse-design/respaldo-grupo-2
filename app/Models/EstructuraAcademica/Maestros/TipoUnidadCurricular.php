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
 * Modelo: Tipo de Unidad Curricular (tabla `tipo_unidad_curricular`).
 *
 * Propósito: Gestiona las clasificaciones o naturalezas de unidades curriculares.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, sin `status` booleano).
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class TipoUnidadCurricular extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'tipo_unidad_curricular';
    protected $primaryKey = 'id_tuc';
    protected $moduloAuditoria = 'TipoUnidadCurricular';

    protected $fillable = [
        'nombre_tuc',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tuc'    => 'integer',
        'status_id' => 'integer',
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

        $query = DB::table('tipo_unidad_curricular')
            ->select([
                'id_tuc',
                'nombre_tuc',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ])
            ->whereNull('deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId));

        if (! empty($search)) {
            $query->where('nombre_tuc', 'ilike', "%{$search}%");
        }

        $paginator = $query->orderBy('id_tuc', 'desc')
            ->paginate($porPagina)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->id_tuc = (int) $item->id_tuc;
            $item->status_id = (int) $item->status_id;

            return $item;
        });

        return $paginator;
    }

    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = DB::table('tipo_unidad_curricular')->where('id_tuc', $id)->whereNull('deleted_at')->first();
        if (! $registro) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id]);
        }

        DB::table('tipo_unidad_curricular')
            ->where('id_tuc', $id)
            ->update([
                'status_id'  => $estado,
                'updated_id' => $userId ?? auth()->id(),
                'updated_at' => now(),
            ]);

        return true;
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('tipo_unidad_curricular')
            ->select(['id_tuc', 'nombre_tuc'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_tuc', 'asc')
            ->get();
    }
}
