<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Turno (tabla `turno` en PostgreSQL).
 * Módulo 9: Oferta, Planificación e Inscripción.
 */
class Turno extends Model
{
    use RegistraEnBitacora;

    protected $table = 'turno';

    protected $primaryKey = 'id_tur';

    protected $moduloAuditoria = 'Turno';

    protected $fillable = [
        'codigo_tur',
        'nombre_tur',
        'hora_inicio_tur',
        'hora_fin_tur',
        'descripcion_tur',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tur'    => 'integer',
        'status'    => 'boolean',
        'status_id' => 'integer',
    ];

    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $status = $filtros['status'] ?? null;

        return DB::table('turno')
            ->select('turno.*', 'id_tur as id')
            ->whereNull('deleted_at')
            ->when($status !== null && $status !== '' && $status !== 'all', fn ($q) => $q->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN)))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('nombre_tur', 'ilike', "%{$search}%")
                      ->orWhere('codigo_tur', 'ilike', "%{$search}%")
                      ->orWhere('descripcion_tur', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('id_tur', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_tur', $id)->firstOrFail();

        return $registro->update([
            'status_id'  => $estado,
            'status'     => $estado === 1,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
