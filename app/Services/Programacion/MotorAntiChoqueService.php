<?php

namespace App\Services\Programacion;

use App\Models\ProgramacionAcademica\SeccionHorario;
use App\Models\ProgramacionAcademica\SeccionUnidadDocente;
use DomainException;

/**
 * Servicio: Motor Anti-Choque y Validación de Restricciones (SOGAC).
 *
 * Módulo: Programación Académica / Planificación y Oferta.
 * Propósito: Valida en tiempo real que no existan conflictos de horario:
 * 1. Conflicto Docente: Impide que un profesor tenga dos clases asignadas a la misma hora en cualquier sección.
 * 2. Conflicto de Espacio Físico (Aula): Impide que un aula o laboratorio sea asignada a dos secciones al mismo tiempo.
 */
class MotorAntiChoqueService
{
    /**
     * Mapeo de días de la semana legibles a números o cadenas.
     */
    protected static array $nombresDias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
        '1' => 'Lunes',
        '2' => 'Martes',
        '3' => 'Miércoles',
        '4' => 'Jueves',
        '5' => 'Viernes',
        '6' => 'Sábado',
        '7' => 'Domingo',
        'Lunes' => 'Lunes',
        'Martes' => 'Martes',
        'Miércoles' => 'Miércoles',
        'Jueves' => 'Jueves',
        'Viernes' => 'Viernes',
        'Sábado' => 'Sábado',
        'Domingo' => 'Domingo',
    ];

    /**
     * Valida la disponibilidad horaria del docente y del aula física.
     *
     * @param int $sudId Identificador de la relación Sección-Unidad-Docente
     * @param int $aulaId Identificador del Aula / Laboratorio físico
     * @param int|string $diaSemana Día de la clase (1=Lunes a 6=Sábado o nombre)
     * @param string $horaInicio Hora de inicio en formato HH:MM o HH:MM:SS
     * @param string $horaFin Hora de culminación en formato HH:MM o HH:MM:SS
     * @param int|null $horarioIdExcluir ID del bloque horario actual en caso de edición
     *
     * @throws DomainException Si se detecta un solapamiento docente o de espacio
     * @return bool True si no hay colisiones
     */
    public function validarDisponibilidad(
        int $sudId,
        int $aulaId,
        int|string $diaSemana,
        string $horaInicio,
        string $horaFin,
        ?int $horarioIdExcluir = null
    ): bool {
        // Normalizar horas
        $horaInicioNorm = substr($horaInicio, 0, 5);
        $horaFinNorm = substr($horaFin, 0, 5);

        if ($horaInicioNorm >= $horaFinNorm) {
            throw new DomainException("La hora de inicio ({$horaInicioNorm}) debe ser menor que la hora de fin ({$horaFinNorm}).");
        }

        $sud = SeccionUnidadDocente::with([
            'docente',
            'seccion',
            'mallaCurricularDetalle.unidadCurricular'
        ])->findOrFail($sudId);

        $docenteId = $sud->docente_id;
        $nombreDia = self::$nombresDias[$diaSemana] ?? $diaSemana;

        // ==========================================================
        // 1. CONTROL DOCENTE (MOTOR ANTI-SOLAPAMIENTO DE PROFESOR)
        // ==========================================================
        if ($docenteId) {
            $choqueDocente = SeccionHorario::query()
                ->where('status', true)
                ->where('dia_semana', $diaSemana)
                ->when($horarioIdExcluir, fn($q) => $q->where('id_sho', '!=', $horarioIdExcluir))
                ->whereHas('seccionUnidadDocente', function ($q) use ($docenteId) {
                    $q->where('docente_id', $docenteId)
                      ->where('status', true);
                })
                ->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->where('hora_inicio', '<', $horaFin)
                      ->where('hora_fin', '>', $horaInicio);
                })
                ->with([
                    'seccionUnidadDocente.seccion',
                    'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                    'aula',
                ])
                ->first();

            if ($choqueDocente) {
                $profesorNombre = $sud->docente?->name ?? 'El profesor';
                $materiaOcupada = $choqueDocente->seccionUnidadDocente?->mallaCurricularDetalle?->unidadCurricular?->nombre_unc ?? 'Asignatura';
                $seccionOcupada = $choqueDocente->seccionUnidadDocente?->seccion?->nombre_sec ?? 'otra sección';
                $ini = substr($choqueDocente->hora_inicio, 0, 5);
                $fin = substr($choqueDocente->hora_fin, 0, 5);

                throw new DomainException(
                    "Alerta Docente Ocupado: {$profesorNombre} ya tiene clase programada el {$nombreDia} de {$ini} a {$fin} en '{$materiaOcupada}' ({$seccionOcupada})."
                );
            }
        }

        // ==========================================================
        // 2. CONTROL DE ESPACIOS (MOTOR ANTI-SOLAPAMIENTO DE AULAS)
        // ==========================================================
        $choqueAula = SeccionHorario::query()
            ->where('status', true)
            ->where('aul_id', $aulaId)
            ->where('dia_semana', $diaSemana)
            ->when($horarioIdExcluir, fn($q) => $q->where('id_sho', '!=', $horarioIdExcluir))
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->where('hora_inicio', '<', $horaFin)
                  ->where('hora_fin', '>', $horaInicio);
            })
            ->with([
                'seccionUnidadDocente.seccion',
                'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                'aula',
            ])
            ->first();

        if ($choqueAula) {
            $codigoAula = $choqueAula->aula?->codigo_aula_aul ?? "Aula #{$aulaId}";
            $seccionOcupada = $choqueAula->seccionUnidadDocente?->seccion?->nombre_sec ?? 'otra sección';
            $materiaOcupada = $choqueAula->seccionUnidadDocente?->mallaCurricularDetalle?->unidadCurricular?->nombre_unc ?? 'Asignatura';
            $ini = substr($choqueAula->hora_inicio, 0, 5);
            $fin = substr($choqueAula->hora_fin, 0, 5);

            throw new DomainException(
                "Alerta Espacio No Disponible: El espacio físico '{$codigoAula}' ya está ocupado el {$nombreDia} de {$ini} a {$fin} por la sección '{$seccionOcupada}' ({$materiaOcupada})."
            );
        }

        return true;
    }
}

