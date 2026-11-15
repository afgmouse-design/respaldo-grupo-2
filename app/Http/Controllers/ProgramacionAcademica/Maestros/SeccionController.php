<?php

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Seccion\StoreSeccionRequest;
use App\Http\Requests\ProgramacionAcademica\Seccion\UpdateSeccionRequest;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\CohorteSede;
use App\Models\ProgramacionAcademica\Maestros\LapsoAcademico;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\SeccionHorario;
use App\Models\ProgramacionAcademica\SeccionUnidadDocente;
use App\Models\User;
use App\Services\ProgramacionAcademica\MotorAntiChoqueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Gestión Integral de Secciones, Asignación Docente y Horarios.
 * Módulo: Programación Académica / Oferta y Planificación.
 *
 * Flujo:
 * 1. Creación de sección vacía con amarre a cohorte por sede y malla curricular.
 * 2. Asignación docente y materias de la malla curricular respectiva.
 * 3. Asignación de bloques horarios con motor anti-choque de profesor y aulas.
 * 4. Consulta y visualización del horario del estudiante.
 */
class SeccionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Seccion::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'laa_id', 'pnf_id', 'sed_id', 'turno_sec']);

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/index', [
            'secciones' => Seccion::obtenerPaginados($filtros, $perPage),
            'lapsos'    => LapsoAcademico::where('estado_la', true)->orderBy('id_la', 'desc')->get(['id_la', 'nombre_la']),
            'pnfs'      => DB::table('pnf')->where('status', true)->whereNull('deleted_at')->orderBy('nombre_pnf')->get(['id_pnf', 'nombre_pnf', 'codigo_pnf']),
            'sedes'     => DB::table('sede')->where('status', true)->whereNull('deleted_at')->orderBy('nombre_sed')->get(['id_sed', 'nombre_sed']),
            'filters'   => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Seccion::class);

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/create', [
            'lapsos'   => LapsoAcademico::where('estado_la', true)->orderBy('id_la', 'desc')->get(['id_la', 'nombre_la']),
            'pnfs'     => DB::table('pnf')->where('status', true)->whereNull('deleted_at')->orderBy('nombre_pnf')->get(['id_pnf', 'nombre_pnf', 'codigo_pnf']),
            'sedes'    => DB::table('sede')->where('status', true)->whereNull('deleted_at')->orderBy('nombre_sed')->get(['id_sed', 'nombre_sed']),
            'cohortes' => CohorteSede::where('status', true)->orderBy('id_cos', 'desc')->get(['id_cos', 'nombre_cos', 'sep_id']),
            'mallas'   => MallaCurricular::where('status_id', 1)->orderBy('nombre_mac')->get(['id_mac', 'nombre_mac', 'pnf_id']),
        ]);
    }

    public function show(int $seccion): Response
    {
        $registro = Seccion::with([
            'lapsoAcademico',
            'pnf',
            'sede',
            'cohorteSede',
            'mallaCurricular',
            'unidadesDocentes.mallaCurricularDetalle.unidadCurricular',
            'unidadesDocentes.docente',
            'unidadesDocentes.horarios.aula.tipoAula',
        ])->where('id_sec', $seccion)->firstOrFail();

        $this->authorize('view', $registro);

        // Aulas de la misma sede física para la planificación de horarios
        $aulasDisponibles = Aula::with('tipoAula')
            ->where('sed_id', $registro->sed_id)
            ->where('status', true)
            ->orderBy('codigo_aula_aul')
            ->get();

        // Unidades curriculares disponibles de la malla curricular de la sección
        $materiasMalla = [];
        if ($registro->mac_id) {
            $materiasMalla = MallaCurricularDetalle::with(['unidadCurricular', 'trayecto'])
                ->where('mac_id', $registro->mac_id)
                ->where('status_id', 1)
                ->get();
        }

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/show', [
            'seccion'          => $registro,
            'aulas'            => $aulasDisponibles,
            'materiasMalla'    => $materiasMalla,
        ]);
    }

    public function edit(int $seccion): Response
    {
        $registro = Seccion::where('id_sec', $seccion)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/edit', [
            'seccion'  => $registro,
            'lapsos'   => LapsoAcademico::where('estado_la', true)->orderBy('id_la', 'desc')->get(['id_la', 'nombre_la']),
            'pnfs'     => DB::table('pnf')->where('status', true)->whereNull('deleted_at')->orderBy('nombre_pnf')->get(['id_pnf', 'nombre_pnf', 'codigo_pnf']),
            'sedes'    => DB::table('sede')->where('status', true)->whereNull('deleted_at')->orderBy('nombre_sed')->get(['id_sed', 'nombre_sed']),
            'cohortes' => CohorteSede::where('status', true)->orderBy('id_cos', 'desc')->get(['id_cos', 'nombre_cos', 'sep_id']),
            'mallas'   => MallaCurricular::where('status_id', 1)->orderBy('nombre_mac')->get(['id_mac', 'nombre_mac', 'pnf_id']),
        ]);
    }

    public function store(StoreSeccionRequest $request): RedirectResponse
    {
        $this->authorize('create', Seccion::class);

        try {
            $data = $request->validated();
            if (empty($data['codigo_sec'])) {
                $data['codigo_sec'] = $data['nombre_sec'];
            }

            Seccion::create($data + [
                'status'     => true,
                'status_id'  => 1,
                'created_id' => auth()->id(),
            ]);

            return redirect()->route('seccion.index')->with('success', 'Sección registrada exitosamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors(['error' => 'Ocurrió un error al registrar la sección: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateSeccionRequest $request, int $seccion): RedirectResponse
    {
        $registro = Seccion::where('id_sec', $seccion)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $registro->update($request->validated() + [
                'updated_id' => auth()->id(),
            ]);

            return redirect()->route('seccion.index')->with('success', 'Sección actualizada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors(['error' => 'Ocurrió un error al actualizar la sección.']);
        }
    }

    public function destroy(int $seccion): RedirectResponse
    {
        $registro = Seccion::where('id_sec', $seccion)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Seccion::alternarEstatus($seccion, $nuevoEstado);

            return redirect()->back()->with('success', $nuevoEstado === 1
                ? 'Sección activada correctamente.'
                : 'Sección desactivada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'No se pudo alternar el estatus de la sección.']);
        }
    }

    /**
     * Búsqueda reactiva de docentes por cédula, nombre o correo.
     */
    public function buscarDocente(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $docentes = User::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                      ->orWhere('email', 'ilike', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($docentes);
    }

    /**
     * Asignar Unidad Curricular y Docente titular a la Sección.
     */
    public function asignarDocente(Request $request, int $seccion): RedirectResponse
    {
        $sec = Seccion::findOrFail($seccion);

        $validated = $request->validate([
            'mcd_id'            => ['required', 'integer', 'exists:malla_curricular_detalle,id_mcd'],
            'docente_id'        => ['required', 'integer', 'exists:users,id'],
            'tipo_contratacion' => ['nullable', 'string', 'max:50'],
            'horas_semanales'   => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        try {
            SeccionUnidadDocente::updateOrCreate(
                [
                    'sec_id' => $sec->id_sec,
                    'mcd_id' => $validated['mcd_id'],
                ],
                [
                    'docente_id'        => $validated['docente_id'],
                    'tipo_contratacion' => $validated['tipo_contratacion'] ?? 'Titular',
                    'horas_semanales'   => $validated['horas_semanales'] ?? 4,
                    'status'            => true,
                    'status_id'         => 1,
                    'created_id'        => auth()->id(),
                ]
            );

            return redirect()->back()->with('success', 'Unidad Curricular y Docente asignados correctamente a la sección.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Error al asignar docente: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar asignación de unidad y docente de la sección.
     */
    public function eliminarAsignacionDocente(int $id): RedirectResponse
    {
        try {
            $asignacion = SeccionUnidadDocente::findOrFail($id);
            $asignacion->delete();

            return redirect()->back()->with('success', 'Materia desvinculada de la sección exitosamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar la asignación: ' . $e->getMessage()]);
        }
    }

    /**
     * Agregar Bloque Horario a una Unidad de la Sección con validación Anti-Choque.
     */
    public function agregarHorario(Request $request, int $seccion, MotorAntiChoqueService $motor): RedirectResponse
    {
        $sec = Seccion::findOrFail($seccion);

        $validated = $request->validate([
            'sud_id'      => ['required', 'integer', 'exists:seccion_unidad_docente,id_sud'],
            'aul_id'      => ['required', 'integer', 'exists:aula,id_aul'],
            'dia_semana'  => ['required', 'integer', 'min:1', 'max:6'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin'    => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo_sesion' => ['nullable', 'string', 'in:Teorica,Practica/Lab'],
        ]);

        $sud = SeccionUnidadDocente::findOrFail($validated['sud_id']);

        // Ejecutar Motor Anti-Choque en tiempo real
        $motor->validarDisponibilidad(
            $sec->laa_id,
            $sud->docente_id,
            $validated['aul_id'],
            $validated['dia_semana'],
            $validated['hora_inicio'],
            $validated['hora_fin']
        );

        try {
            SeccionHorario::create([
                'sud_id'      => $validated['sud_id'],
                'aul_id'      => $validated['aul_id'],
                'dia_semana'  => $validated['dia_semana'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin'    => $validated['hora_fin'],
                'tipo_sesion' => $validated['tipo_sesion'] ?? 'Teorica',
                'status'      => true,
                'status_id'   => 1,
                'created_id'  => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Bloque horario registrado correctamente sin colisiones.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Error al registrar horario: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar Bloque Horario.
     */
    public function eliminarHorario(int $id): RedirectResponse
    {
        try {
            $bloque = SeccionHorario::findOrFail($id);
            $bloque->delete();

            return redirect()->back()->with('success', 'Bloque horario eliminado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar el bloque horario.']);
        }
    }

    /**
     * Vista Standalone: Consulta de Horario del Estudiante por Sección y Semestre.
     */
    public function horarioEstudiante(Request $request): Response
    {
        $codigoSec = trim((string) $request->input('codigo_sec'));
        $semestre  = (int) $request->input('semestre');

        $seccionEncontrada = null;
        $horarioGrid = [];

        if (!empty($codigoSec)) {
            $seccionEncontrada = Seccion::with([
                'lapsoAcademico',
                'pnf',
                'sede',
                'mallaCurricular',
                'unidadesDocentes.mallaCurricularDetalle.unidadCurricular',
                'unidadesDocentes.docente',
                'unidadesDocentes.horarios.aula.tipoAula',
            ])
            ->where(function ($q) use ($codigoSec) {
                $q->where('codigo_sec', 'ilike', $codigoSec)
                  ->orWhere('nombre_sec', 'ilike', "%{$codigoSec}%");
            })
            ->when($semestre > 0, fn ($q) => $q->where('semestre_sec', $semestre))
            ->first();

            if ($seccionEncontrada) {
                // Aplanar lista de bloques para cuadrícula
                foreach ($seccionEncontrada->unidadesDocentes as $ud) {
                    $materiaNombre = $ud->mallaCurricularDetalle?->unidadCurricular?->nombre_unc ?? 'Materia';
                    $materiaCodigo = $ud->mallaCurricularDetalle?->unidadCurricular?->codigo_unc ?? '';
                    $docenteNombre = $ud->docente?->name ?? 'Por asignar';

                    foreach ($ud->horarios as $h) {
                        $horarioGrid[] = [
                            'id'             => $h->id_sho,
                            'dia_semana'     => $h->dia_semana,
                            'hora_inicio'    => substr($h->hora_inicio, 0, 5),
                            'hora_fin'       => substr($h->hora_fin, 0, 5),
                            'tipo_sesion'    => $h->tipo_sesion,
                            'materia'        => $materiaNombre,
                            'codigo_materia' => $materiaCodigo,
                            'docente'        => $docenteNombre,
                            'aula'           => $h->aula?->codigo_aula_aul ?? 'Aula N/A',
                            'tipo_aula'      => $h->aula?->tipoAula?->nombre_tia ?? 'Ambiente',
                        ];
                    }
                }
            }
        }

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/HorarioEstudiante', [
            'seccion'    => $seccionEncontrada,
            'horario'    => $horarioGrid,
            'filtros'    => [
                'codigo_sec' => $codigoSec,
                'semestre'   => $semestre ?: null,
            ],
        ]);
    }
}
