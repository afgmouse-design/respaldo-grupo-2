<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\ProgramacionAcademica\CargaAcademica;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Horario (tabla `horario` en PostgreSQL).
 * Módulo: Programación Académica (Grupo 3: Ángel Gonzales - Oferta y Horarios).
 *
 * Propósito: Define la asignación de bloques semanales (día, hora inicio, hora fin)
 * asociando una carga académica (sección-materia-docente) con un ambiente/aula físico.
 * Baja lógica mediante `status` (bool) y `status_id` (1 activo, 2 inactivo).
 */
class Horario extends Model
{
    use RegistraEnBitacora;

    protected $table = 'horario';

    protected $primaryKey = 'id_hor';

    protected $moduloAuditoria = 'Horario';

    protected $fillable = [
        'dia_semana_hor',
        'hora_inicio_hor',
        'hora_fin_hor',
        'caa_id',
        'aul_id',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_hor'    => 'integer',
        'caa_id'    => 'integer',
        'aul_id'    => 'integer',
        'status'    => 'boolean',
        'status_id' => 'integer',
    ];

    /**
     * Relación con Carga Académica (asignación sección + materia + docente).
     */
    public function cargaAcademica(): BelongsTo
    {
        return $this->belongsTo(CargaAcademica::class, 'caa_id', 'id_caa');
    }

    /**
     * Relación con Aula (espacio físico asignado).
     */
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'aul_id', 'id_aul');
    }

    /**
     * Listado paginado con filtros dinámicos (search, status, dia_semana, aul_id).
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search    = $filtros['search'] ?? null;
        $status    = $filtros['status'] ?? null;
        $diaSemana = $filtros['dia_semana'] ?? null;
        $aulId     = $filtros['aul_id'] ?? null;

        $query = DB::table('horario')
            ->leftJoin('carga_academica', 'carga_academica.id_caa', '=', 'horario.caa_id')
            ->leftJoin('seccion', 'seccion.id_sec', '=', 'carga_academica.sec_id')
            ->leftJoin('unidad_curricular', 'unidad_curricular.id_unc', '=', 'carga_academica.unc_id')
            ->leftJoin('aula', 'aula.id_aul', '=', 'horario.aul_id')
            ->select(
                'horario.*',
                'horario.id_hor as id',
                'seccion.nombre_sec',
                'seccion.codigo_sec',
                'unidad_curricular.nombre_unc',
                'unidad_curricular.codigo_unc',
                'aula.codigo_aula_aul as nombre_aula',
                'aula.tipo_espacio_aul'
            )
            ->whereNull('horario.deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('horario.dia_semana_hor', 'ilike', "%{$search}%")
                  ->orWhere('seccion.nombre_sec', 'ilike', "%{$search}%")
                  ->orWhere('seccion.codigo_sec', 'ilike', "%{$search}%")
                  ->orWhere('unidad_curricular.nombre_unc', 'ilike', "%{$search}%")
                  ->orWhere('aula.codigo_aula_aul', 'ilike', "%{$search}%");
            });
        }

        if ($status !== null && $status !== 'all' && $status !== '') {
            $statusId = $status === 'activo' || $status === '1' || $status === true ? 1 : 2;
            $query->where('horario.status_id', $statusId);
        }

        if ($diaSemana && $diaSemana !== 'all') {
            $query->where('horario.dia_semana_hor', $diaSemana);
        }

        if ($aulId && $aulId !== 'all') {
            $query->where('horario.aul_id', $aulId);
        }

        return $query->orderBy('horario.dia_semana_hor', 'asc')
            ->orderBy('horario.hora_inicio_hor', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Alterna el estatus lógico de un horario.
     */
    public static function alternarEstatus(int $id, int $nuevoEstado): bool
    {
        return (bool) DB::table('horario')
            ->where('id_hor', $id)
            ->update([
                'status_id'  => $nuevoEstado,
                'status'     => $nuevoEstado === 1,
                'updated_id' => auth()->id(),
                'updated_at' => now(),
            ]);
    }
}

