<?php

namespace App\Http\Controllers\ProgramacionAcademica;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\StoreAsignacionDirectaHorarioRequest;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\SeccionHorario;
use App\Models\ProgramacionAcademica\SeccionUnidadDocente;
use App\Services\ProgramacionAcademica\MotorAntiChoqueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador para la gestión de Espacios y Tiempo (Turnos y Horarios).
 */
class EspacioTiempoController extends Controller
{
    /**
     * Turno: Definición de jornadas y franjas horarias desde la tabla PostgreSQL `turno`.
     */
    public function turnoIndex(): Response
    {
        $registros = \App\Models\ProgramacionAcademica\Maestros\Turno::where('status_id', 1)
            ->orderBy('id_tur')
            ->get();

        $turnos = $registros->map(function ($t) {
            $bloques = [];
            if (in_array($t->codigo_tur, ['DIU', 'MAT'])) {
                $bloques = [
                    ['numero' => 1, 'hora' => '07:00 - 07:45', 'tipo' => 'Académico'],
                    ['numero' => 2, 'hora' => '07:45 - 08:30', 'tipo' => 'Académico'],
                    ['numero' => 3, 'hora' => '08:40 - 09:25', 'tipo' => 'Académico'],
                    ['numero' => 4, 'hora' => '09:25 - 10:10', 'tipo' => 'Académico'],
                    ['numero' => 5, 'hora' => '10:15 - 11:00', 'tipo' => 'Académico'],
                    ['numero' => 6, 'hora' => '11:00 - 11:45', 'tipo' => 'Académico'],
                    ['numero' => 7, 'hora' => '11:45 - 12:30', 'tipo' => 'Académico'],
                ];
            } elseif ($t->codigo_tur === 'VES') {
                $bloques = [
                    ['numero' => 1, 'hora' => '13:00 - 13:45', 'tipo' => 'Académico'],
                    ['numero' => 2, 'hora' => '13:45 - 14:30', 'tipo' => 'Académico'],
                    ['numero' => 3, 'hora' => '14:40 - 15:25', 'tipo' => 'Académico'],
                    ['numero' => 4, 'hora' => '15:25 - 16:10', 'tipo' => 'Académico'],
                    ['numero' => 5, 'hora' => '16:15 - 17:00', 'tipo' => 'Académico'],
                    ['numero' => 6, 'hora' => '17:00 - 17:45', 'tipo' => 'Académico'],
                    ['numero' => 7, 'hora' => '17:45 - 18:30', 'tipo' => 'Académico'],
                ];
            } else {
                $bloques = [
                    ['numero' => 1, 'hora' => '18:30 - 19:15', 'tipo' => 'Académico'],
                    ['numero' => 2, 'hora' => '19:15 - 20:00', 'tipo' => 'Académico'],
                    ['numero' => 3, 'hora' => '20:00 - 20:45', 'tipo' => 'Académico'],
                    ['numero' => 4, 'hora' => '20:45 - 21:30', 'tipo' => 'Académico'],
                    ['numero' => 5, 'hora' => '21:30 - 22:00', 'tipo' => 'Académico'],
                ];
            }

            return [
                'id_tur'      => $t->id_tur,
                'codigo'      => $t->codigo_tur === 'DIU' ? 'MAT' : $t->codigo_tur,
                'nombre'      => $t->nombre_tur,
                'hora_inicio' => substr((string) $t->hora_inicio_tur, 0, 5),
                'hora_fin'    => substr((string) $t->hora_fin_tur, 0, 5),
                'bloques'     => $bloques,
                'descripcion' => $t->descripcion_tur,
                'color'       => 'amber',
            ];
        });

        return Inertia::render('ProgramacionAcademica/EspaciosTiempo/Turno', [
            'turnos' => $turnos,
        ]);
    }

    /**
     * Horarios: Programador general de cuadrícula horaria de secciones y espacios.
     */
    public function horariosIndex(Request $request): Response
    {
        $seccionId = $request->input('seccion_id');
        $aulaId = $request->input('aula_id');

        $secciones = Seccion::query()
            ->with([
                'cohorteSede.cohorte',
                'mallaCurricular.detalles.unidadCurricular'
            ])
            ->where('status', true)
            ->orderBy('nombre_sec')
            ->get();

        $aulas = Aula::query()
            ->where('status', true)
            ->orderBy('codigo_aula_aul')
            ->get();

        $docentes = DB::table('users as u')
            ->leftJoin('persona as per', function ($join) {
                $join->on('per.per_email', '=', 'u.email')
                     ->orOn(DB::raw("TRIM(LOWER(per.per_nombres || ' ' || per.per_apellidos))"), '=', DB::raw("TRIM(LOWER(u.name))"));
            })
            ->select(
                'u.id',
                'u.name',
                'u.email',
                DB::raw("COALESCE(per.per_cedula, per.per_documento, '') as cedula")
            )
            ->orderBy('u.name')
            ->get();

        $horariosQuery = SeccionHorario::query()
            ->with([
                'seccionUnidadDocente.seccion',
                'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                'seccionUnidadDocente.docente',
                'aula',
            ])
            ->where('status', true);

        if ($seccionId) {
            $horariosQuery->whereHas('seccionUnidadDocente', function ($q) use ($seccionId) {
                $q->where('sec_id', $seccionId);
            });
        }

        if ($aulaId) {
            $horariosQuery->where('aul_id', $aulaId);
        }

        $horarios = $horariosQuery->get();

        return Inertia::render('ProgramacionAcademica/EspaciosTiempo/Horarios', [
            'secciones' => $secciones,
            'aulas'     => $aulas,
            'docentes'  => $docentes,
            'horarios'  => $horarios,
            'filters'   => $request->only(['seccion_id', 'aula_id']),
        ]);
    }

    /**
     * Asignación Directa de Horario por Bloque (Formulario Unificado).
     * Previene registros huérfanos mediante una transacción atómica que enlaza
     * la Sección, Unidad Curricular, Docente Titular, Aula y Bloque temporal,
     * previa validación con el Motor Anti-Choque.
     */
    public function asignarHorarioDirecto(
        StoreAsignacionDirectaHorarioRequest $request,
        MotorAntiChoqueService $antiChoque
    ): RedirectResponse {
        $data = $request->validated();
        $seccion = Seccion::findOrFail($data['sec_id']);

        // 1. Validación en tiempo real del Motor Anti-Choque
        $antiChoque->validarDisponibilidad(
            lapsoId: (int) $seccion->laa_id,
            docenteId: (int) $data['docente_id'],
            aulaId: (int) $data['aul_id'],
            diaSemana: (int) $data['dia_semana'],
            horaInicio: $data['hora_inicio'] . ':00',
            horaFin: $data['hora_fin'] . ':00'
        );

        // 2. Transacción atómica en Base de Datos
        DB::transaction(function () use ($data) {
            $sud = SeccionUnidadDocente::firstOrCreate(
                [
                    'sec_id' => $data['sec_id'],
                    'mcd_id' => $data['mcd_id'],
                ],
                [
                    'docente_id' => $data['docente_id'],
                    'status'     => true,
                ]
            );

            // Si el registro ya existía pero con otro profesor, actualizar al asignado
            if ((int) $sud->docente_id !== (int) $data['docente_id'] || !$sud->status) {
                $sud->update([
                    'docente_id' => $data['docente_id'],
                    'status'     => true,
                ]);
            }

            // Crear el bloque de horario amarrado a la relación SUD garantizada
            SeccionHorario::create([
                'sud_id'      => $sud->id_sud,
                'aul_id'      => $data['aul_id'],
                'dia_semana'  => $data['dia_semana'],
                'hora_inicio' => $data['hora_inicio'] . ':00',
                'hora_fin'    => $data['hora_fin'] . ':00',
                'tipo_sesion' => $data['tipo_sesion'],
                'status'      => true,
            ]);
        });

        return redirect()->back()->with('success', '¡Bloque de horario asignado exitosamente! Relación 100% vinculada y validada sin colisiones.');
    }

    /**
     * Eliminación de un bloque de horario asignado.
     */
    public function eliminarBloqueHorario(int $id): RedirectResponse
    {
        $bloque = SeccionHorario::findOrFail($id);
        $bloque->delete();

        return redirect()->back()->with('success', 'Bloque de horario eliminado correctamente.');
    }
}

