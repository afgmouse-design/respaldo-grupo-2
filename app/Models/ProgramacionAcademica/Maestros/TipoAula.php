<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Tipo de Aula (tabla `tipo_aula`).
 *
 * Propósito: Catálogo independiente que clasifica los ambientes físicos
 * (aula teórica, laboratorio, taller, etc.). Es referenciado por `aula.tia_id`.
 * Usa borrado lógico mediante el campo booleano `status` (true = Activo).
 */
class TipoAula extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'tipo_aula';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_tia';

    /** Módulo reportado en la bitácora de auditoría. */
    protected $moduloAuditoria = 'Tipo de Aula';

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_tia',
        'codigo_tia',
        'descripcion_tia',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos. */
    protected $casts = [
        'id_tia'    => 'integer',
        'status'    => 'boolean',
        'status_id' => 'integer',
    ];

    /**
     * Obtiene los tipos de aula paginados con filtros dinámicos (búsqueda y estatus).
     * Expone `id` como alias de `id_tia` para los componentes genéricos de tabla.
     *
     * @param array $filtros search (texto) y status ('all', '1', '0'; por defecto solo activos).
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $status = $filtros['status'] ?? null;

        return DB::table('tipo_aula')
            ->select('tipo_aula.*', 'id_tia as id')
            ->whereNull('deleted_at')
            ->when($status !== null && $status !== '' && $status !== 'all', fn ($q) => $q->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN)))
            ->when(!empty($search), function ($q) use ($search) {
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
     * Baja/alta lógica: fija status_id (1 = activo, 2 = inactivo) y sincroniza el booleano `status`.
     * Usa Eloquent para que RegistraEnBitacora audite el cambio.
     */
    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_tia', $id)->firstOrFail();

        return $registro->update([
            'status_id'  => $estado,
            'status'     => $estado === 1,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}

