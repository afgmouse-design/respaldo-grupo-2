<?php

/**
 * Propósito: Controlador integral de Secciones (`seccion`), asignación de Carga Académica (`carga_academica` con `pep_id`) y Horarios (`horario`).
 * Historial: Actualizado para eliminar `SeccionHorario`, `SeccionUnidadDocente`, `pnf` y booleano `status`, operando sobre `Programa`, `TipoSeccion`, `Turno`, `CargaAcademica` (`pep_id -> persona_perfil.id_pep`), `Horario` y `MotorAntiChoqueService`.
 */

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Seccion\StoreSeccionRequest;
use App\Http\Requests\ProgramacionAcademica\Seccion\UpdateSeccionRequest;
use App\Models\Academico\LapsoAcademico;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\Personas\PersonaPerfil;
use App\Models\ProgramacionAcademica\CargaAcademica;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\CohorteSede;
use App\Models\ProgramacionAcademica\Maestros\Horario;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\Maestros\TipoSeccion;
use App\Models\ProgramacionAcademica\Maestros\Turno;
use App\Services\ProgramacionAcademica\MotorAntiChoqueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SeccionController extends Controller
{
    /**
     * Carga catálogos activos (`status_id = 1`) requeridos en index, create y edit.
     */
    private function cargarCatalogos(): array
    {
        $programas = DB::table('programa')
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->orderBy('nombre_prg')
            ->get(['id_prg', 'nombre_prg', 'codigo_prg', 'codigo_ministerio_prg']);

        return [
            'lapsos'       => LapsoAcademico::where('status_id', 1)->orderBy('id_laa', 'desc')->get(['id_laa', 'nombre_laa']),
            'programas'    => $programas,
            'pnfs'         => $programas,
            'sedes'        => DB::table('sede')->where('status_id', 1)->whereNull('deleted_at')->orderBy('nombre_sed')->get(['id_sed', 'nombre_sed']),
            'cohortes'     => CohorteSede::where('status_id', 1)->orderBy('id_cos', 'desc')->get(['id_cos', 'nombre_cos', 'sep_id']),
            'mallas'       => MallaCurricular::where('status_id', 1)->orderBy('nombre_mac')->get(['id_mac', 'nombre_mac', 'prg_id']),
            'tiposSeccion' => TipoSeccion::where('status_id', 1)->orderBy('id_tis')->get(['id_tis', 'codigo_tis', 'nombre_tis']),
            'turnos'       => Turno::where('status_id', 1)->orderBy('id_tur')->get(['id_tur', 'codigo_tur', 'nombre_tur']),
        ];
    }

    /**
     * Resuelve FKs `tis_id` y `tur_id` delegando al helper compartido del modelo `Seccion`.
     */
    private function normalizarFksCatalogo(array $data): array
    {
        return Seccion::normalizarFksCatalogo($data);
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Seccion::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'status_id', 'laa_id', 'prg_id', 'pnf_id', 'sed_id', 'tis_id', 'tur_id', 'turno_sec']);
        if (empty($filtros['prg_id']) && !empty($filtros['pnf_id'])) {
            $filtros['prg_id'] = $filtros['pnf_id'];
        }

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/index', array_merge([
            'secciones' => Seccion::obtenerPaginados($filtros, $perPage),
            'filters'   => $filtros,
        ], $this->cargarCatalogos()));
    }

    public function create(): Response
    {
        $this->authorize('create', Seccion::class);

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/create', $this->cargarCatalogos());
    }

    public function show(int $seccion): Response
    {
        $registro = Seccion::with([
            'lapsoAcademico',
            'programa',
            'sede',
            'tipoSeccion',
            'turno',
            'cohorteSede',
            'mallaCurricular',
            'cargasAcademicas.unidadCurricular',
            'cargasAcademicas.docentePerfil.persona',
            'cargasAcademicas.horarios.aula.tipoAula',
        ])->where('id_sec', $seccion)->firstOrFail();

        $this->authorize('view', $registro);

        $aulasDisponibles = Aula::with('tipoAula')
            ->where('sed_id', $registro->sed_id)
            ->where('status_id', 1)
            ->orderBy('codigo_aula_aul')
            ->get();

        $materiasMalla = $registro->mac_id
            ? MallaCurricularDetalle::with(['unidadCurricular', 'trayecto'])
                ->where('mac_id', $registro->mac_id)
                ->where('status_id', 1)
                ->get()
            : [];

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/show', [
            'seccion'       => $registro,
            'aulas'         => $aulasDisponibles,
            'materiasMalla' => $materiasMalla,
            'docentes'      => PersonaPerfil::obtenerDocentesActivosParaSelect(),
        ]);
    }

    public function edit(int $seccion): Response
    {
        $registro = Seccion::where('id_sec', $seccion)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/edit', array_merge([
            'seccion' => $registro,
        ], $this->cargarCatalogos()));
    }

    public function store(StoreSeccionRequest $request): RedirectResponse
    {
        $this->authorize('create', Seccion::class);

        try {
            $data = $this->normalizarFksCatalogo($request->validated());
            if (empty($data['codigo_sec'])) {
                $data['codigo_sec'] = $data['nombre_sec'];
            }

            DB::transaction(function () use ($data) {
                Seccion::liberarNombreSiEliminado(
                    (string) $data['nombre_sec'],
                    (int) $data['laa_id'],
                    (int) $data['sed_id']
                );

                Seccion::create($data + [
                    'status_id'  => Seccion::STATUS_ACTIVO,
                    'created_id' => auth()->id(),
                    'updated_id' => auth()->id(),
                ]);
            });

            return redirect()->route('seccion.index')->with('success', 'Sección registrada exitosamente.');
        } catch (\DomainException $e) {
            return redirect()->back()->withInput()->withErrors(['nombre_sec' => $e->getMessage()]);
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
            $data = $this->normalizarFksCatalogo($request->validated());

            DB::transaction(function () use ($registro, $data) {
                Seccion::liberarNombreSiEliminado(
                    (string) $data['nombre_sec'],
                    (int) $data['laa_id'],
                    (int) $data['sed_id'],
                    (int) $registro->id_sec
                );

                $registro->update($data + ['updated_id' => auth()->id()]);
            });

            return redirect()->route('seccion.index')->with('success', 'Sección actualizada correctamente.');
        } catch (\DomainException $e) {
            return redirect()->back()->withInput()->withErrors(['nombre_sec' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->withErrors(['error' => 'Ocurrió un error al actualizar la sección.']);
        }
    }

    /**
     * Si recibe `eliminar=1`, ejecuta baja lógica en interfaz (`deleted_at` + `status_id = 2`);
     * de lo contrario invierte únicamente el estatus lógico (`status_id` 1 <-> 2).
     *
     * Historial:
     * - 2026-11-16: Separada la eliminación lógica (`eliminar=1`) del toggle de estado.
     */
    public function destroy(Request $request, int $seccion): RedirectResponse
    {
        $registro = Seccion::where('id_sec', $seccion)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            if ($request->boolean('eliminar')) {
                Seccion::ejecutarBajaLogicaModelo($registro, auth()->id(), Seccion::STATUS_INACTIVO);

                return redirect()->back()->with('success', "La sección '{$registro->nombre_sec}' ha sido eliminada correctamente.");
            }

            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Seccion::alternarEstatus($seccion, $nuevoEstado);

            return redirect()->back()->with(
                'success',
                $nuevoEstado === 1
                    ? 'Sección activada correctamente.'
                    : 'Sección desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la solicitud sobre la sección.']);
        }
    }

    /**
     * Búsqueda AJAX de docentes activos sobre `persona_perfil` (`id_pep`) + `persona`.
     */
    public function buscarDocente(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $docentes = DB::table('persona_perfil as pep')
            ->join('persona as per', 'per.id_per', '=', 'pep.per_id')
            ->join('perfil as prf', 'prf.id_prf', '=', 'pep.prf_id')
            ->where('pep.status_id', 1)
            ->whereNull('pep.deleted_at')
            ->where('per.status_id', 1)
            ->whereNull('per.deleted_at')
            ->where(function ($w) {
                $w->whereRaw('upper(prf.codigo_prf) = ?', ['DOCENTE'])
                    ->orWhere('prf.nombre_prf', 'ilike', '%docente%');
            })
            ->where(function ($query) use ($q) {
                $query->where('per.cedula_per', 'ilike', "%{$q}%")
                    ->orWhere('per.nombres_per', 'ilike', "%{$q}%")
                    ->orWhere('per.apellidos_per', 'ilike', "%{$q}%")
                    ->orWhere('per.correo_per', 'ilike', "%{$q}%");
            })
            ->select(
                'pep.id_pep',
                'per.id_per',
                'per.cedula_per',
                'per.nombres_per',
                'per.apellidos_per',
                'per.correo_per',
                DB::raw("TRIM(per.nombres_per || ' ' || per.apellidos_per) as nombre_completo")
            )
            ->limit(15)
            ->get();

        return response()->json($docentes);
    }

    /**
     * Asigna o actualiza una Carga Académica (`carga_academica`) vinculando Sección + Unidad Curricular + Docente (`pep_id`).
     */
    /**
     * Historial:
     *  - 2026-11-16: Delega validaciones de unicidad `(sec_id, unc_id)`, docente activo y tope
     *    horario por dedicación en el lapso a `CargaAcademica::validarReglasAsignacion` y evita
     *    sobrescritura silenciosa de docentes duplicados.
     */
    public function asignarDocente(Request $request, int $seccion): RedirectResponse
    {
        $sec = Seccion::findOrFail($seccion);
        $this->authorize('update', $sec);

        if (!$request->filled('pep_id') && $request->filled('docente_pep_id')) {
            $request->merge(['pep_id' => $request->input('docente_pep_id')]);
        }

        $validated = $request->validate([
            'unc_id'                => ['required_without:mcd_id', 'nullable', 'integer', Rule::exists('unidad_curricular', 'id_unc')->whereNull('deleted_at')],
            'mcd_id'                => ['required_without:unc_id', 'nullable', 'integer', Rule::exists('malla_curricular_detalle', 'id_mcd')->whereNull('deleted_at')],
            'pep_id'                => ['required', 'integer', Rule::exists('persona_perfil', 'id_pep')->whereNull('deleted_at')],
            'tipo_contratacion_caa' => ['nullable', 'string', 'max:50'],
            'tipo_contratacion'     => ['nullable', 'string', 'max:50'],
            'horas_semanales_caa'   => ['nullable', 'integer', 'min:1', 'max:40'],
            'horas_semanales'       => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);

        try {
            $uncId = !empty($validated['unc_id'])
                ? (int) $validated['unc_id']
                : (int) MallaCurricularDetalle::findOrFail($validated['mcd_id'])->unc_id;

            $pepId = (int) $validated['pep_id'];
            $horas = (int) ($validated['horas_semanales_caa'] ?? $validated['horas_semanales'] ?? 4);
            $tipoContratacion = trim((string) ($validated['tipo_contratacion_caa'] ?? $validated['tipo_contratacion'] ?? 'Titular')) ?: 'Titular';

            $validacion = CargaAcademica::validarReglasAsignacion((int) $sec->id_sec, $uncId, $pepId, $horas);
            if (!empty($validacion['errors'])) {
                return redirect()->back()->withInput()->withErrors($validacion['errors']);
            }

            CargaAcademica::create([
                'sec_id'                => $sec->id_sec,
                'unc_id'                => $uncId,
                'pep_id'                => $pepId,
                'tipo_contratacion_caa' => $tipoContratacion,
                'horas_semanales_caa'   => $horas,
                'status_id'             => CargaAcademica::STATUS_ACTIVO,
                'created_id'            => auth()->id(),
                'updated_id'            => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Unidad Curricular y Docente asignados correctamente a la sección.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Error al asignar docente: ' . $e->getMessage()]);
        }
    }

    /**
     * Desactiva y aplica SoftDelete sobre la Carga Académica de la sección.
     */
    public function eliminarAsignacionDocente(int $id): RedirectResponse
    {
        try {
            $asignacion = CargaAcademica::findOrFail($id);
            $asignacion->update(['status_id' => 2, 'updated_id' => auth()->id()]);
            $asignacion->delete();

            return redirect()->back()->with('success', 'Materia desvinculada de la sección exitosamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar la asignación: ' . $e->getMessage()]);
        }
    }

    /**
     * Registra un bloque horario en `horario` previa validación con MotorAntiChoqueService.
     */
    public function agregarHorario(Request $request, int $seccion, MotorAntiChoqueService $motor): RedirectResponse
    {
        $sec = Seccion::findOrFail($seccion);
        $this->authorize('update', $sec);

        if (!$request->filled('caa_id') && $request->filled('sud_id')) {
            $request->merge(['caa_id' => $request->input('sud_id')]);
        }

        $validated = $request->validate([
            'caa_id'      => ['required', 'integer', Rule::exists('carga_academica', 'id_caa')->whereNull('deleted_at')],
            'aul_id'      => ['required', 'integer', Rule::exists('aula', 'id_aul')->whereNull('deleted_at')],
            'dia_semana'  => ['required'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin'    => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo_sesion' => ['nullable', 'string', 'max:50'],
        ]);

        $carga = CargaAcademica::where('id_caa', $validated['caa_id'])
            ->where('sec_id', $sec->id_sec)
            ->whereNull('deleted_at')
            ->firstOrFail();

        try {
            DB::transaction(function () use ($sec, $carga, $validated, $motor) {
                $motor->validarDisponibilidad([
                    'laa_id'          => (int) $sec->laa_id,
                    'pep_id'          => (int) $carga->pep_id,
                    'aul_id'          => (int) $validated['aul_id'],
                    'sec_id'          => (int) $sec->id_sec,
                    'dia_semana_hor'  => $validated['dia_semana'],
                    'hora_inicio_hor' => $validated['hora_inicio'],
                    'hora_fin_hor'    => $validated['hora_fin'],
                ]);

                Horario::create([
                    'caa_id'          => $carga->id_caa,
                    'aul_id'          => (int) $validated['aul_id'],
                    'dia_semana_hor'  => $motor->normalizarDiaSemana($validated['dia_semana']),
                    'hora_inicio_hor' => $validated['hora_inicio'] . ':00',
                    'hora_fin_hor'    => $validated['hora_fin'] . ':00',
                    'tipo_sesion_hor' => $validated['tipo_sesion'] ?? 'Teorica',
                    'status_id'       => Horario::STATUS_ACTIVO,
                    'created_id'      => auth()->id(),
                    'updated_id'      => auth()->id(),
                ]);
            });

            return redirect()->back()->with('success', 'Bloque horario registrado correctamente sin colisiones.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Error al registrar horario: ' . $e->getMessage()]);
        }
    }

    /**
     * Desactiva y aplica SoftDelete sobre un bloque en `horario`.
     */
    public function eliminarHorario(int $id): RedirectResponse
    {
        try {
            $bloque = Horario::findOrFail($id);
            $bloque->update(['status_id' => 2, 'updated_id' => auth()->id()]);
            $bloque->delete();

            return redirect()->back()->with('success', 'Bloque horario eliminado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar el bloque horario.']);
        }
    }

    /**
     * Consulta del Horario del Estudiante por Sección y Semestre.
     */
    public function horarioEstudiante(Request $request): Response
    {
        $codigoSec = trim((string) $request->input('codigo_sec'));
        $semestre = (int) $request->input('semestre');

        $seccionEncontrada = null;
        $horarioGrid = [];

        if (!empty($codigoSec)) {
            $seccionEncontrada = Seccion::with([
                'lapsoAcademico',
                'programa',
                'sede',
                'tipoSeccion',
                'turno',
                'mallaCurricular',
                'cargasAcademicas' => fn ($q) => $q->where('status_id', 1),
                'cargasAcademicas.unidadCurricular',
                'cargasAcademicas.docentePerfil.persona',
                'cargasAcademicas.horarios' => fn ($q) => $q->where('status_id', 1),
                'cargasAcademicas.horarios.aula.tipoAula',
            ])
                ->where(function ($q) use ($codigoSec) {
                    $q->where('codigo_sec', 'ilike', $codigoSec)
                        ->orWhere('nombre_sec', 'ilike', "%{$codigoSec}%");
                })
                ->when($semestre > 0, fn ($q) => $q->where('semestre_sec', $semestre))
                ->first();

            if ($seccionEncontrada) {
                foreach ($seccionEncontrada->cargasAcademicas as $caa) {
                    $materiaNombre = $caa->unidadCurricular?->nombre_unc ?? 'Materia';
                    $materiaCodigo = $caa->unidadCurricular?->codigo_unc ?? '';
                    $persona = $caa->docentePerfil?->persona;
                    $docenteNombre = $persona
                        ? trim("{$persona->nombres_per} {$persona->apellidos_per}")
                        : 'Por asignar';

                    foreach ($caa->horarios as $h) {
                        $horarioGrid[] = [
                            'id'             => $h->id_hor,
                            'dia_semana'     => $h->dia_semana_hor,
                            'hora_inicio'    => substr((string) $h->hora_inicio_hor, 0, 5),
                            'hora_fin'       => substr((string) $h->hora_fin_hor, 0, 5),
                            'tipo_sesion'    => $h->tipo_sesion_hor ?? 'Teorica',
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
            'seccion' => $seccionEncontrada,
            'horario' => $horarioGrid,
            'filtros' => [
                'codigo_sec' => $codigoSec,
                'semestre'   => $semestre ?: null,
            ],
        ]);
    }
}
