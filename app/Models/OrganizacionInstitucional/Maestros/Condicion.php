<?php

namespace App\Models\OrganizacionInstitucional\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Condición (tabla `condicion`).
 *
 * Propósito: Define el estado funcional o categoría institucional (ej. Sede Principal, Extensión).
 */
class Condicion extends Model
{
    use SoftDeletes, RegistraEnBitacora;

    protected $table = 'condicion';
    protected $primaryKey = 'id_cdn';
    protected $moduloAuditoria = 'Condicion';

    protected $fillable = [
        'nombre_cdn',
        'descripcion_cdn',
        'tco_id',
        'status',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Obtiene condiciones activas para selectores con Query Builder puro.
     */
    public static function obtenerActivosParaSelect(): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('condicion')
            ->select(['id_cdn', 'nombre_cdn'])
            ->where('status', true)
            ->orderBy('nombre_cdn', 'asc')
            ->get();
    }

    /**
     * Actualización: se añade el listado paginado con filtros (search, status, tco_id) para el CRUD con modales.
     * Motivo: ajuste/nueva funcionalidad (no corrige un bug). El código previo no se ve afectado.
     * Expone `id` como alias de `id_cdn` y el nombre del tipo de condición (`nombre_tco`).
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $status = $filtros['status'] ?? null;
        $tcoId  = $filtros['tco_id'] ?? null;

        return \Illuminate\Support\Facades\DB::table('condicion')
            ->leftJoin('tipo_condicion', 'tipo_condicion.id_tco', '=', 'condicion.tco_id')
            ->select('condicion.*', 'condicion.id_cdn as id', 'tipo_condicion.nombre_tco')
            ->whereNull('condicion.deleted_at')
            ->when($status !== null && $status !== '' && $status !== 'all', fn ($q) => $q->where('condicion.status', filter_var($status, FILTER_VALIDATE_BOOLEAN)))
            ->when(!empty($tcoId) && $tcoId !== 'all', fn ($q) => $q->where('condicion.tco_id', $tcoId))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('condicion.nombre_cdn', 'ilike', "%{$search}%")
                      ->orWhere('condicion.descripcion_cdn', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('condicion.id_cdn', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Actualización: baja/alta lógica sobre `status` (la tabla `condicion` no tiene `status_id`).
     * Usa Eloquent para que RegistraEnBitacora audite el cambio.
     */
    public static function alternarEstatus(int $id, bool $activo, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_cdn', $id)->firstOrFail();

        return $registro->update([
            'status'     => $activo,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}

