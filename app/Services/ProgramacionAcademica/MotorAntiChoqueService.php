<?php

namespace App\Services\ProgramacionAcademica;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Servicio: Motor de Restricciones Anti-Choque en Tiempo Real.
 * Módulo: Programación Académica.
 *
 * Valida matemáticamente y en base de datos la superposición de horarios
 * para docentes y espacios físicos (aulas y laboratorios) en el lapso lectivo.
 */
class MotorAntiChoqueService
{
    /**
     * Valida la disponibilidad de docente y aula antes de registrar o actualizar un bloque de clase.
     *
     * @throws ValidationException Si se detecta colisión temporal.
     */
    public function validarDisponibilidad(
        int $lapsoId,
        ?int $docenteId,
        int $aulaId,
        int $diaSemana,
        string $horaInicio,
        string $horaFin,
        ?int $horarioIdExcluir = null
    ): void {
        // REGLA OBLIGATORIA: El docente es requisito indispensable para guardar el horario
        if (empty($docenteId)) {
            throw ValidationException::withMessages([
                'docente_id' => 'Restricción Obligatoria Violada: No se puede programar ni guardar un bloque de horario sin un docente titular vinculado por cédula.'
            ]);
        }

        // 1. CONTROL DOCENTE: Anti-choque de profesor
        $choqueDocente = DB::table('seccion_horario as sho')
            ->join('seccion_unidad_docente as sud', 'sho.sud_id', '=', 'sud.id_sud')
            ->join('seccion as sec', 'sud.sec_id', '=', 'sec.id_sec')
            ->join('malla_curricular_detalle as mcd', 'sud.mcd_id', '=', 'mcd.id_mcd')
            ->join('unidad_curricular as unc', 'mcd.unc_id', '=', 'unc.id_unc')
            ->join('users as u', 'sud.docente_id', '=', 'u.id')
            ->where('sec.laa_id', $lapsoId)
            ->where('sud.docente_id', $docenteId)
            ->where('sho.dia_semana', $diaSemana)
            ->when($horarioIdExcluir, fn ($q) => $q->where('sho.id_sho', '!=', $horarioIdExcluir))
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->where('sho.hora_inicio', '<', $horaFin)
                  ->where('sho.hora_fin', '>', $horaInicio);
            })
            ->select('sec.nombre_sec', 'unc.nombre_unc', 'sho.hora_inicio', 'sho.hora_fin', 'u.name')
            ->first();

        if ($choqueDocente) {
            throw ValidationException::withMessages([
                'docente_id' => "Alerta de Choque Docente: El profesor {$choqueDocente->name} ya tiene clase asignada ese día de {$choqueDocente->hora_inicio} a {$choqueDocente->hora_fin} con '{$choqueDocente->nombre_unc}' en la sección {$choqueDocente->nombre_sec}."
            ]);
        }

        // 2. CONTROL DE ESPACIO: Anti-choque de aula o laboratorio
        $choqueAula = DB::table('seccion_horario as sho')
            ->join('seccion_unidad_docente as sud', 'sho.sud_id', '=', 'sud.id_sud')
            ->join('seccion as sec', 'sud.sec_id', '=', 'sec.id_sec')
            ->join('aula as aul', 'sho.aul_id', '=', 'aul.id_aul')
            ->where('sec.laa_id', $lapsoId)
            ->where('sho.aul_id', $aulaId)
            ->where('sho.dia_semana', $diaSemana)
            ->when($horarioIdExcluir, fn ($q) => $q->where('sho.id_sho', '!=', $horarioIdExcluir))
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->where('sho.hora_inicio', '<', $horaFin)
                  ->where('sho.hora_fin', '>', $horaInicio);
            })
            ->select('aul.codigo_aula_aul', 'sec.nombre_sec', 'sho.hora_inicio', 'sho.hora_fin')
            ->first();

        if ($choqueAula) {
            throw ValidationException::withMessages([
                'aul_id' => "Alerta de Choque de Espacio: El ambiente {$choqueAula->codigo_aula_aul} ya se encuentra ocupado por la sección {$choqueAula->nombre_sec} en el horario de {$choqueAula->hora_inicio} a {$choqueAula->hora_fin}."
            ]);
        }
    }
}

