<?php

namespace App\Models\EstructuraAcademica\Maestros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Tipo de Período / Modalidad Académica (tabla `tipo_periodo`).
 *
 * Propósito: Define las modalidades temporales académicas (Semestral, Anual, Trayecto, Trimestral).
 *
 * Actualización 2026-10-22 (Campo Nomenclatura para Semestre, Trimestre o Anual):
 * Se agregó el atributo 'nomenclatura' al fillable y a las consultas obtenerActivosParaSelect()
 * y obtenerPaginados() para identificar visualmente la modalidad temporal en la UI.
 */
class TipoPeriodo extends Model
{
    /** Nombre explícito de la tabla (singular). */
    protected $table = 'tipo_periodo';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_tpd';

    /** Desactivación de timestamps automáticos. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_tpd',
        'codigo_tpd',
        'nomenclatura',
        'duracion_semanas_tpd',
        'descripcion_tpd',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos. */
    protected $casts = [
        'id_tpd'               => 'integer',
        'duracion_semanas_tpd' => 'integer',
        'status'               => 'boolean',
        'status_id'            => 'integer',
    ];

    /** Atributos virtuales calculados. */
    protected $appends = ['est_tpd'];

    /**
     * Accesor para el estatus reactivo del tipo de período.
     */
    public function getEstTpdAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Obtiene los tipos de período activos para selectores dependientes mediante Query Builder puro.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('tipo_periodo')
            ->select([
                'id_tpd',
                'nombre_tpd',
                'codigo_tpd',
                'nomenclatura',
                'duracion_semanas_tpd',
            ])
            ->where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('nombre_tpd', 'asc')
            ->get();
    }

    /**
     * Obtiene los tipos de período paginados con filtros dinámicos mediante Query Builder puro.
     * Retorna estrictamente los registros activos (status = true).
     */
    public static function obtenerPaginados(array|string|null $filtros = [], int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $search = is_array($filtros) ? ($filtros['search'] ?? null) : $filtros;
        $status = is_array($filtros) ? ($filtros['status'] ?? null) : null;

        $query = DB::table('tipo_periodo')
            ->select([
                'id_tpd',
                'nombre_tpd',
                'codigo_tpd',
                'nomenclatura',
                'duracion_semanas_tpd',
                'descripcion_tpd',
                'status',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ])
            ->whereNull('deleted_at');

        if ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_tpd', 'ilike', "%{$search}%")
                  ->orWhere('codigo_tpd', 'ilike', "%{$search}%")
                  ->orWhere('nomenclatura', 'ilike', "%{$search}%")
                  ->orWhere('descripcion_tpd', 'ilike', "%{$search}%");
            });
        }

        return $query->orderBy('id_tpd', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }
}

