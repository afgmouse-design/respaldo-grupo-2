<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Tipo de Sección (tabla `tipo_seccion` en PostgreSQL).
 * Módulo 9: Oferta, Planificación e Inscripción.
 */
class TipoSeccion extends Model
{
    use RegistraEnBitacora;

    protected $table = 'tipo_seccion';

    protected $primaryKey = 'id_tis';

    protected $moduloAuditoria = 'Tipo de Seccion';

    protected $fillable = [
        'codigo_tis',
        'nombre_tis',
        'descripcion_tis',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tis'    => 'integer',
        'status'    => 'boolean',
        'status_id' => 'integer',
    ];

    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $status = $filtros['status'] ?? null;

        return DB::table('tipo_seccion')
            ->select('tipo_seccion.*', 'id_tis as id')
            ->whereNull('deleted_at')
            ->when($status !== null && $status !== '' && $status !== 'all', fn ($q) => $q->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN)))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('nombre_tis', 'ilike', "%{$search}%")
                      ->orWhere('codigo_tis', 'ilike', "%{$search}%")
                      ->orWhere('descripcion_tis', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('id_tis', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_tis', $id)->firstOrFail();

        return $registro->update([
            'status_id'  => $estado,
            'status'     => $estado === 1,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
