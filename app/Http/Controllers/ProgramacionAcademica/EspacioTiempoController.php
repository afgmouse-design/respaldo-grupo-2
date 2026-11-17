<?php

/**
 * Propósito: Controlador para la planificación y asignación directa de Espacios y Tiempo (Turnos y Horarios).
 * Historial: Actualizado para operar sobre `Horario` + `CargaAcademica` (`pep_id -> persona_perfil.id_pep`), eliminar `SeccionHorario`/`SeccionUnidadDocente`/`users.name`, y validar colisiones vía `MotorAntiChoqueService`.
 */

namespace App\Http\Controllers\ProgramacionAcademica;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\StoreAsignacionDirectaHorarioRequest;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\Personas\PersonaPerfil;
use App\Models\ProgramacionAcademica\CargaAcademica;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\Horario;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\Maestros\Turno;
use App\Services\ProgramacionAcademica\MotorAntiChoqueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EspacioTiempoController extends Controller
{
    /**
     * Construye la matriz unificada de turnos activos y sus bloques horarios académicos.
     *
     * @return array{turnos: \Illuminate\Support\Collection<int, array<string, mixed>>, bloques: array<int, array<string, mixed>>}
     */
    private function construirMatrizTurnos(): array
    {
        $registros = Turno::where('status_id', Turno::STATUS_ACTIVO)->orderBy('id_tur')->get();
        $bloquesPlanos = [];

        $turnos = $registros->map(function (Turno $t) use (&$bloquesPlanos) {
            $codigoCanonico = $t->codigo_tur === 'DIU' ? 'MAT' : $t->codigo_tur;

            if (in_array($t->codigo_tur, ['DIU', 'MAT'], true)) {
                $franjas = [
                    ['numero' => 1, 'hora_inicio' => '07:00', 'hora_fin' => '07:45', 'tipo' => 'Académico'],
                    ['numero' => 2, 'hora_inicio' => '07:45', 'hora_fin' => '08:30', 'tipo' => 'Académico'],
                    ['numero' => 3, 'hora_inicio' => '08:40', 'hora_fin' => '09:25', 'tipo' => 'Académico'],
                    ['numero' => 4, 'hora_inicio' => '09:25', 'hora_fin' => '10:10', 'tipo' => 'Académico'],
                    ['numero' => 5, 'hora_inicio' => '10:15', 'hora_fin' => '11:00', 'tipo' => 'Académico'],
                    ['numero' => 6, 'hora_inicio' => '11:00', 'hora_fin' => '11:45', 'tipo' => 'Académico'],
                    ['numero' => 7, 'hora_inicio' => '11:45', 'hora_fin' => '12:30', 'tipo' => 'Académico'],
                ];
            } elseif ($t->codigo_tur === 'VES') {
                $franjas = [
                    ['numero' => 1, 'hora_inicio' => '13:00', 'hora_fin' => '13:45', 'tipo' => 'Académico'],
                    ['numero' => 2, 'hora_inicio' => '13:45', 'hora_fin' => '14:30', 'tipo' => 'Académico'],
                    ['numero' => 3, 'hora_inicio' => '14:40', 'hora_fin' => '15:25', 'tipo' => 'Académico'],
                    ['numero' => 4, 'hora_inicio' => '15:25', 'hora_fin' => '16:10', 'tipo' => 'Académico'],
                    ['numero' => 5, 'hora_inicio' => '16:15', 'hora_fin' => '17:00', 'tipo' => 'Académico'],
                    ['numero' => 6, 'hora_inicio' => '17:00', 'hora_fin' => '17:45', 'tipo' => 'Académico'],
                    ['numero' => 7, 'hora_inicio' => '17:45', 'hora_fin' => '18:30', 'tipo' => 'Académico'],
                ];
            } else {
                $franjas = [
                    ['numero' => 1, 'hora_inicio' => '18:30', 'hora_fin' => '19:15', 'tipo' => 'Académico'],
                    ['numero' => 2, 'hora_inicio' => '19:15', 'hora_fin' => '20:00', 'tipo' => 'Académico'],
                    ['numero' => 3, 'hora_inicio' => '20:00', 'hora_fin' => '20:45', 'tipo' => 'Académico'],
                    ['numero' => 4, 'hora_inicio' => '20:45', 'hora_fin' => '21:30', 'tipo' => 'Académico'],
                    ['numero' => 5, 'hora_inicio' => '21:30', 'hora_fin' => '22:00', 'tipo' => 'Académico'],
                ];
            }

            $bloquesTurno = collect($franjas)->map(function (array $b) use ($t, $codigoCanonico, &$bloquesPlanos) {
                $item = [
                    'numero'       => $b['numero'],
                    'hora'         => "{$b['hora_inicio']} - {$b['hora_fin']}",
                    'hora_inicio'  => $b['hora_inicio'],
                    'hora_fin'     => $b['hora_fin'],
                    'tipo'         => $b['tipo'],
                    'id_tur'       => $t->id_tur,
                    'turno_codigo' => $codigoCanonico,
                    'turno_nombre' => $t->nombre_tur,
                ];
                $bloquesPlanos[] = $item;

                return $item;
            })->all();

            return [
                'id_tur'      => $t->id_tur,
                'codigo'      => $codigoCanonico,
                'nombre'      => $t->nombre_tur,
                'hora_inicio' => substr((string) $t->hora_inicio_tur, 0, 5),
                'hora_fin'    => substr((string) $t->hora_fin_tur, 0, 5),
                'bloques'     => $bloquesTurno,
                'descripcion' => $t->descripcion_tur,
                'color'       => 'amber',
            ];
        });

        return [
            'turnos'  => $turnos,
            'bloques' => $bloquesPlanos,
        ];
    }

    /**
     * Turno: Definición de jornadas y franjas horarias desde la tabla PostgreSQL `turno`.
     */
    public function turnoIndex(): Response
    {
        $matriz = $this->construirMatrizTurnos();

        return Inertia::render('ProgramacionAcademica/EspaciosTiempo/Turno', [
            'turnos' => $matriz['turnos'],
        ]);
    }

    /**
     * Devuelve la matriz de turnos, bloques académicos y bloques horarios asignados para la grilla semanal.
     *
     * Historial:
     *  - 2026-11-16: Método `indexHorarios` implementado exponiendo `turnos`, `bloques` y `horarios` con autorización por Policy.
     */
    public function indexHorarios(Request $request): Response
    {
        $this->authorize('viewAny', Horario::class);

        $seccionId = $request->input('seccion_id');
        $aulaId = $request->input('aula_id');

        $secciones = Seccion::query()
            ->with([
                'cohorteSede.cohorte',
                'mallaCurricular.detalles.unidadCurricular',
            ])
            ->where('status_id', Seccion::STATUS_ACTIVO)
            ->orderBy('nombre_sec')
            ->get();

        $aulas = Aula::query()
            ->where('status_id', Aula::STATUS_ACTIVO)
            ->orderBy('codigo_aula_aul')
            ->get();

        $docentes = PersonaPerfil::obtenerDocentesActivosParaSelect();

        $horarios = Horario::query()
            ->with([
                'cargaAcademica.seccion',
                'cargaAcademica.unidadCurricular',
                'cargaAcademica.docentePerfil.persona',
                'aula',
            ])
            ->where('status_id', Horario::STATUS_ACTIVO)
            ->when($seccionId, fn ($q) => $q->whereHas('cargaAcademica', fn ($c) => $c->where('sec_id', $seccionId)))
            ->when($aulaId, fn ($q) => $q->where('aul_id', $aulaId))
            ->orderBy('dia_semana_hor')
            ->orderBy('hora_inicio_hor')
            ->get();

        $matriz = $this->construirMatrizTurnos();

        return Inertia::render('ProgramacionAcademica/EspaciosTiempo/Horarios', [
            'turnos'    => $matriz['turnos'],
            'bloques'   => $matriz['bloques'],
            'secciones' => $secciones,
            'aulas'     => $aulas,
            'docentes'  => $docentes,
            'horarios'  => $horarios,
            'filters'   => $request->only(['seccion_id', 'aula_id']),
        ]);
    }

    /**
     * Vista Interactiva: Corrección de Horario en Grilla Semanal (Lunes a Domingo).
     *
     * Propósito: Expone catálogos de Lapsos, Sedes, Secciones, Unidades Curriculares, Docentes, Aulas
     * y bloques semanales para asignar o corregir horarios desde celdas de la matriz con feedback
     * inmediato del Motor Anti-Choque.
     *
     * Historial:
     *  - 2026-11-16: Creación del endpoint `correccionHorario` para `EspaciosTiempo/CorreccionHorario.vue`.
     */
    public function correccionHorario(Request $request): Response
    {
        $this->authorize('viewAny', Horario::class);

        $laaId = $request->filled('laa_id') ? (int) $request->input('laa_id') : null;
        $sedId = $request->filled('sed_id') ? (int) $request->input('sed_id') : null;
        $secId = $request->filled('sec_id') ? (int) $request->input('sec_id') : null;

        $lapsos = \App\Models\Academico\LapsoAcademico::query()
            ->where('status_id', \App\Models\Academico\LapsoAcademico::STATUS_ACTIVO)
            ->orderByDesc('id_laa')
            ->get(['id_laa', 'codigo_laa', 'nombre_laa']);

        $sedes = DB::table('sede')
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->orderBy('nombre_sed')
            ->get(['id_sed', 'nombre_sed']);

        $secciones = Seccion::query()
            ->with([
                'lapsoAcademico:id_laa,codigo_laa,nombre_laa',
                'sede:id_sed,nombre_sed',
                'turno:id_tur,codigo_tur,nombre_tur',
                'mallaCurricular.detalles.unidadCurricular:id_unc,codigo_unc,nombre_unc',
                'cargasAcademicas' => fn ($q) => $q->whereNull('deleted_at')
                    ->with([
                        'unidadCurricular:id_unc,codigo_unc,nombre_unc',
                        'docentePerfil.persona:id_per,cedula_per,nombres_per,apellidos_per,doc_ded_id',
                    ]),
            ])
            ->where('status_id', Seccion::STATUS_ACTIVO)
            ->orderBy('nombre_sec')
            ->get();

        $unidadesCurriculares = \App\Models\EstructuraAcademica\Maestros\UnidadCurricular::query()
            ->where('status_id', 1)
            ->orderBy('codigo_unc')
            ->get(['id_unc', 'codigo_unc', 'nombre_unc', 'tuc_id']);

        $aulas = Aula::query()
            ->where('status_id', Aula::STATUS_ACTIVO)
            ->orderBy('codigo_aula_aul')
            ->get(['id_aul', 'codigo_aula_aul', 'capacidad_maxima_aul', 'tipo_espacio_aul', 'sed_id']);

        $horarios = Horario::query()
            ->with([
                'cargaAcademica.seccion:id_sec,codigo_sec,nombre_sec,laa_id,sed_id,semestre_sec,trayecto_sec',
                'cargaAcademica.unidadCurricular:id_unc,codigo_unc,nombre_unc',
                'cargaAcademica.docentePerfil.persona:id_per,cedula_per,nombres_per,apellidos_per',
                'aula:id_aul,codigo_aula_aul,capacidad_maxima_aul,sed_id',
            ])
            ->where('status_id', Horario::STATUS_ACTIVO)
            ->when($secId, fn ($q) => $q->whereHas('cargaAcademica', fn ($c) => $c->where('sec_id', $secId)))
            ->when($laaId, fn ($q) => $q->whereHas('cargaAcademica.seccion', fn ($s) => $s->where('laa_id', $laaId)))
            ->when($sedId, fn ($q) => $q->whereHas('cargaAcademica.seccion', fn ($s) => $s->where('sed_id', $sedId)))
            ->orderBy('dia_semana_hor')
            ->orderBy('hora_inicio_hor')
            ->get();

        $matriz = $this->construirMatrizTurnos();

        return Inertia::render('ProgramacionAcademica/EspaciosTiempo/CorreccionHorario', [
            'turnos'               => $matriz['turnos'],
            'bloques'              => $matriz['bloques'],
            'lapsos'               => $lapsos,
            'sedes'                => $sedes,
            'secciones'            => $secciones,
            'unidadesCurriculares' => $unidadesCurriculares,
            'docentes'             => PersonaPerfil::obtenerDocentesActivosParaSelect(),
            'aulas'                => $aulas,
            'horarios'             => $horarios,
            'filters'              => [
                'laa_id' => $laaId ?? '',
                'sed_id' => $sedId ?? '',
                'sec_id' => $secId ?? '',
            ],
        ]);
    }

    /**
     * Alias compatible con rutas previas hacia `indexHorarios`.
     */
    public function horariosIndex(Request $request): Response
    {
        return $this->indexHorarios($request);
    }

    /**
     * Valida carga docente + `MotorAntiChoqueService` y persiste de forma atómica el bloque horario directo.
     *
     * Historial:
     *  - 2026-11-16: Expuesto como `guardarHorarioDirecto()` con autorización `HorarioPolicy@create`,
     *    reutilizando `MotorAntiChoqueService::validarDisponibilidad` y `CargaAcademica::validarReglasAsignacion`.
     */
    public function guardarHorarioDirecto(
        StoreAsignacionDirectaHorarioRequest $request,
        MotorAntiChoqueService $antiChoque
    ): RedirectResponse {
        $this->authorize('create', Horario::class);

        $data = $request->validated();
        $seccion = Seccion::findOrFail($data['sec_id']);
        $mcd = ! empty($data['mcd_id']) ? MallaCurricularDetalle::findOrFail($data['mcd_id']) : null;
        $uncId = ! empty($data['unc_id']) ? (int) $data['unc_id'] : (int) $mcd->unc_id;
        $pepId = (int) $data['pep_id'];
        $horas = (int) ($data['horas_semanales_caa'] ?? $mcd?->htea_mcd ?? 4);
        $tipoContratacion = trim((string) ($data['tipo_contratacion_caa'] ?? 'Titular')) ?: 'Titular';

        DB::transaction(function () use ($data, $seccion, $uncId, $pepId, $horas, $tipoContratacion, $antiChoque) {
            $userId = auth()->id();

            $antiChoque->validarDisponibilidad([
                'laa_id'          => (int) $seccion->laa_id,
                'pep_id'          => $pepId,
                'aul_id'          => (int) $data['aul_id'],
                'sec_id'          => (int) $seccion->id_sec,
                'dia_semana_hor'  => $data['dia_semana'],
                'hora_inicio_hor' => $data['hora_inicio'] . ':00',
                'hora_fin_hor'    => $data['hora_fin'] . ':00',
            ]);

            $cargaExistente = CargaAcademica::query()
                ->where('sec_id', (int) $seccion->id_sec)
                ->where('unc_id', $uncId)
                ->where('pep_id', $pepId)
                ->where('status_id', CargaAcademica::STATUS_ACTIVO)
                ->whereNull('deleted_at')
                ->first();

            if ($cargaExistente) {
                $carga = $cargaExistente;
            } else {
                $validacion = CargaAcademica::validarReglasAsignacion((int) $seccion->id_sec, $uncId, $pepId, $horas);
                if (! empty($validacion['errors'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages($validacion['errors']);
                }

                $carga = CargaAcademica::create([
                    'sec_id'                => (int) $seccion->id_sec,
                    'unc_id'                => $uncId,
                    'pep_id'                => $pepId,
                    'tipo_contratacion_caa' => $tipoContratacion,
                    'horas_semanales_caa'   => $horas,
                    'status_id'             => CargaAcademica::STATUS_ACTIVO,
                    'created_id'            => $userId,
                    'updated_id'            => $userId,
                ]);
            }

            Horario::create([
                'caa_id'          => $carga->id_caa,
                'aul_id'          => (int) $data['aul_id'],
                'dia_semana_hor'  => $antiChoque->normalizarDiaSemana($data['dia_semana']),
                'hora_inicio_hor' => $data['hora_inicio'] . ':00',
                'hora_fin_hor'    => $data['hora_fin'] . ':00',
                'tipo_sesion_hor' => $data['tipo_sesion'] ?? 'Teorica',
                'status_id'       => Horario::STATUS_ACTIVO,
                'created_id'      => $userId,
                'updated_id'      => $userId,
            ]);
        });

        return redirect()->back()->with('success', '¡Bloque de horario asignado exitosamente! Carga académica y bloque horario validados sin colisiones.');
    }

    /**
     * Alias compatible hacia `guardarHorarioDirecto`.
     */
    public function asignarHorarioDirecto(
        StoreAsignacionDirectaHorarioRequest $request,
        MotorAntiChoqueService $antiChoque
    ): RedirectResponse {
        return $this->guardarHorarioDirecto($request, $antiChoque);
    }

    /**
     * Desactiva y aplica baja lógica en interfaz sobre un bloque horario.
     */
    public function eliminarBloqueHorario(int $id): RedirectResponse
    {
        $bloque = Horario::findOrFail($id);
        $this->authorize('delete', $bloque);

        Horario::ejecutarBajaLogicaModelo($bloque, auth()->id(), Horario::STATUS_INACTIVO);

        return redirect()->back()->with('success', 'Bloque de horario eliminado correctamente.');
    }
}
