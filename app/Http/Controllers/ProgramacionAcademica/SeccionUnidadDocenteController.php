<?php

namespace App\Http\Controllers\ProgramacionAcademica;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\CargaAcademica\StoreCargaAcademicaRequest;
use App\Http\Requests\ProgramacionAcademica\CargaAcademica\UpdateCargaAcademicaRequest;
use App\Models\Academico\LapsoAcademico;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use App\Models\Personas\PersonaPerfil;
use App\Models\ProgramacionAcademica\CargaAcademica;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Módulo Sección - Unidad Curricular - Docente (`carga_academica` / SUD).
 *
 * Propósito: Gestiona el CRUD completo de asignaciones docentes por sección y unidad curricular,
 * filtrando dinámicamente las unidades de la malla/semestre correspondiente y validando unicidad
 * `(sec_id, unc_id)`, estado activo del docente y tope máximo de horas según su dedicación laboral
 * en el lapso académico.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial del controlador CRUD dedicado para SUD (`SeccionUnidadDocente`).
 */
class SeccionUnidadDocenteController extends Controller
{
    private const TIPOS_CONTRATACION = [
        'Titular',
        'Contratado',
        'Suplente',
        'Interino',
        'Invitado',
    ];

    /**
     * Determina el `status_id` efectivo para filtrar el listado según la convención de `TabFilter`.
     */
    private function resolverStatusListado(mixed $rawStatus): ?int
    {
        if ($rawStatus === null || $rawStatus === '') {
            return CargaAcademica::STATUS_ACTIVO;
        }

        if ($rawStatus === 'all') {
            return null;
        }

        if ($rawStatus === '0' || $rawStatus === 0 || $rawStatus === '2' || $rawStatus === 2 || $rawStatus === false || $rawStatus === 'false') {
            return CargaAcademica::STATUS_INACTIVO;
        }

        return CargaAcademica::STATUS_ACTIVO;
    }

    /**
     * Carga catálogos enriquecidos para los formularios de creación y edición de asignaciones SUD.
     * Incluye, por cada sección activa, las unidades curriculares filtradas por su malla y semestre
     * junto a los IDs de unidades ya asignadas (para excluir duplicados en interfaz).
     */
    private function cargarCatalogosFormulario(): array
    {
        $secciones = Seccion::query()
            ->with([
                'lapsoAcademico:id_laa,codigo_laa,nombre_laa',
                'programa:id_prg,nombre_prg,siglas_prg',
                'sede:id_sed,nombre_sed',
                'turno:id_tur,nombre_tur',
                'mallaCurricular:id_mac,nombre_mac',
                'cargasAcademicas' => fn ($q) => $q->whereNull('deleted_at')->select(['id_caa', 'sec_id', 'unc_id', 'status_id']),
            ])
            ->where('status_id', Seccion::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderByDesc('id_sec')
            ->get([
                'id_sec',
                'codigo_sec',
                'nombre_sec',
                'semestre_sec',
                'trayecto_sec',
                'laa_id',
                'prg_id',
                'sed_id',
                'tur_id',
                'mac_id',
                'cupo_maximo_sec',
            ]);

        $macIds = $secciones->pluck('mac_id')->filter()->unique()->values();

        $detallesPorMalla = $macIds->isEmpty()
            ? collect()
            : MallaCurricularDetalle::query()
                ->with([
                    'unidadCurricular' => fn ($q) => $q->where('status_id', 1)
                        ->whereNull('deleted_at')
                        ->with('tipoUnidadCurricular:id_tuc,nombre_tuc'),
                    'trayecto:id_tra,codigo_tra,nombre_tra,numero_orden_tra',
                ])
                ->whereIn('mac_id', $macIds)
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->orderBy('semestre_mcd')
                ->orderBy('id_mcd')
                ->get()
                ->groupBy('mac_id');

        $seccionesEnriquecidas = $secciones->map(function (Seccion $sec) use ($detallesPorMalla) {
            $detallesMalla = $sec->mac_id ? $detallesPorMalla->get($sec->mac_id, collect()) : collect();
            $semestreSec = (int) ($sec->semestre_sec ?? 1);
            $trayectoSec = $sec->trayecto_sec !== null ? (int) $sec->trayecto_sec : null;

            $unidadesMallaSemestre = $detallesMalla
                ->filter(function (MallaCurricularDetalle $det) use ($semestreSec, $trayectoSec) {
                    if (! $det->unidadCurricular) {
                        return false;
                    }

                    $traOrden = $det->trayecto?->numero_orden_tra;
                    if ($trayectoSec === 0 && $traOrden !== null && (int) $traOrden === 0) {
                        return true;
                    }

                    if ($trayectoSec !== null && $trayectoSec > 0 && $traOrden !== null && (int) $traOrden === 0) {
                        return false;
                    }

                    $semDet = (int) ($det->semestre_mcd ?? 1);
                    $duracion = max(1, (int) ($det->duracion_semestres_mcd ?? 1));

                    return $semDet === $semestreSec || ($semestreSec > 1 && $duracion >= 2 && $semDet === ($semestreSec - 1));
                })
                ->unique('unc_id')
                ->map(function (MallaCurricularDetalle $det) {
                    $uc = $det->unidadCurricular;
                    $horasSugeridas = (int) ($det->htea_mcd ?: ($det->thte_mcd ?: 4));

                    return [
                        'id_unc'                 => (int) $uc->id_unc,
                        'codigo_unc'             => $uc->codigo_unc,
                        'nombre_unc'             => $uc->nombre_unc,
                        'horas_sugeridas'        => max(1, $horasSugeridas),
                        'tuc_nombre'             => $uc->tipoUnidadCurricular?->nombre_tuc,
                        'semestre_mcd'           => (int) ($det->semestre_mcd ?? 1),
                        'duracion_semestres_mcd' => (int) ($det->duracion_semestres_mcd ?? 1),
                        'origen_malla'           => true,
                    ];
                })
                ->values();

            return [
                'id_sec'                  => (int) $sec->id_sec,
                'codigo_sec'              => $sec->codigo_sec,
                'nombre_sec'              => $sec->nombre_sec,
                'semestre_sec'            => $semestreSec,
                'trayecto_sec'            => $trayectoSec,
                'laa_id'                  => $sec->laa_id ? (int) $sec->laa_id : null,
                'prg_id'                  => $sec->prg_id ? (int) $sec->prg_id : null,
                'sed_id'                  => $sec->sed_id ? (int) $sec->sed_id : null,
                'tur_id'                  => $sec->tur_id ? (int) $sec->tur_id : null,
                'mac_id'                  => $sec->mac_id ? (int) $sec->mac_id : null,
                'lapso_academico'         => $sec->lapsoAcademico,
                'programa'                => $sec->programa,
                'sede'                    => $sec->sede,
                'turno'                   => $sec->turno,
                'malla_curricular'        => $sec->mallaCurricular,
                'unidades_asignadas_ids'  => $sec->cargasAcademicas->pluck('unc_id')->map(fn ($id) => (int) $id)->values(),
                'unidades_malla_semestre' => $unidadesMallaSemestre,
                'tiene_detalles_malla'    => $detallesMalla->isNotEmpty(),
                'tiene_detalles_semestre' => $unidadesMallaSemestre->isNotEmpty(),
            ];
        })->values();

        $unidadesCatalogo = UnidadCurricular::query()
            ->with('tipoUnidadCurricular:id_tuc,nombre_tuc')
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->orderBy('codigo_unc')
            ->get(['id_unc', 'codigo_unc', 'nombre_unc', 'tuc_id'])
            ->map(function (UnidadCurricular $uc) {
                return [
                    'id_unc'          => (int) $uc->id_unc,
                    'codigo_unc'      => $uc->codigo_unc,
                    'nombre_unc'      => $uc->nombre_unc,
                    'horas_sugeridas' => 4,
                    'tuc_nombre'      => $uc->tipoUnidadCurricular?->nombre_tuc,
                    'origen_malla'    => false,
                ];
            })
            ->values();

        return [
            'secciones'            => $seccionesEnriquecidas,
            'unidadesCurriculares' => $unidadesCatalogo,
            'docentes'             => PersonaPerfil::obtenerDocentesActivosParaSelect(),
            'tiposContratacion'    => self::TIPOS_CONTRATACION,
        ];
    }

    /**
     * Invierte el estatus lógico de la carga académica validando capacidad horaria si se reactiva.
     */
    private function ejecutarCambioEstatus(CargaAcademica $registro): RedirectResponse
    {
        $nuevoEstado = (int) $registro->status_id === CargaAcademica::STATUS_ACTIVO
            ? CargaAcademica::STATUS_INACTIVO
            : CargaAcademica::STATUS_ACTIVO;

        if ($nuevoEstado === CargaAcademica::STATUS_ACTIVO) {
            $validacion = CargaAcademica::validarReglasAsignacion(
                (int) $registro->sec_id,
                (int) $registro->unc_id,
                (int) $registro->pep_id,
                (int) ($registro->horas_semanales_caa ?? 4),
                (int) $registro->id_caa,
                CargaAcademica::STATUS_ACTIVO
            );

            if (! empty($validacion['errors'])) {
                return redirect()->back()->withErrors(['error' => implode(' ', $validacion['errors'])]);
            }
        }

        $registro->update([
            'status_id'  => $nuevoEstado,
            'updated_id' => auth()->id(),
        ]);

        return redirect()->back()->with(
            'success',
            $nuevoEstado === CargaAcademica::STATUS_ACTIVO
                ? 'Asignación activada correctamente.'
                : 'Asignación desactivada correctamente.'
        );
    }

    /**
     * Listado paginado de asignaciones Sección - Unidad - Docente (`carga_academica`).
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CargaAcademica::class);

        $perPage = min(max((int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10), 1), 100);
        $filtros = $request->only(['search', 'sec_id', 'laa_id', 'status', 'status_id']);
        $rawStatus = $filtros['status'] ?? $filtros['status_id'] ?? null;
        $statusFiltro = $this->resolverStatusListado($rawStatus);

        $search = trim((string) ($filtros['search'] ?? ''));
        $secId = ! empty($filtros['sec_id']) && $filtros['sec_id'] !== 'all' ? (int) $filtros['sec_id'] : null;
        $laaId = ! empty($filtros['laa_id']) && $filtros['laa_id'] !== 'all' ? (int) $filtros['laa_id'] : null;

        $asignaciones = CargaAcademica::query()
            ->with([
                'seccion:id_sec,codigo_sec,nombre_sec,semestre_sec,trayecto_sec,laa_id,prg_id,sed_id,tur_id,mac_id',
                'seccion.lapsoAcademico:id_laa,codigo_laa,nombre_laa',
                'seccion.programa:id_prg,nombre_prg,siglas_prg',
                'seccion.sede:id_sed,nombre_sed',
                'seccion.turno:id_tur,nombre_tur',
                'unidadCurricular:id_unc,codigo_unc,nombre_unc,tuc_id',
                'unidadCurricular.tipoUnidadCurricular:id_tuc,nombre_tuc',
                'docentePerfil:id_pep,per_id,prf_id',
                'docentePerfil.persona:id_per,cedula_per,nombres_per,apellidos_per,correo_per,doc_ded_id',
                'docentePerfil.persona.dedicacionDocente:id_ded,nombre_ded,horas_academicas_ded',
            ])
            ->withCount([
                'horarios' => fn ($q) => $q->where('status_id', 1)->whereNull('deleted_at'),
            ])
            ->when($statusFiltro !== null, fn ($q) => $q->where('status_id', $statusFiltro))
            ->when($secId !== null, fn ($q) => $q->where('sec_id', $secId))
            ->when($laaId !== null, fn ($q) => $q->whereHas('seccion', fn ($sq) => $sq->where('laa_id', $laaId)))
            ->when($search !== '', function ($q) use ($search) {
                $patron = "%{$search}%";
                $q->where(function ($sub) use ($patron) {
                    $sub->where('tipo_contratacion_caa', 'ilike', $patron)
                        ->orWhereHas('seccion', fn ($sq) => $sq->where('nombre_sec', 'ilike', $patron)->orWhere('codigo_sec', 'ilike', $patron))
                        ->orWhereHas('unidadCurricular', fn ($uq) => $uq->where('codigo_unc', 'ilike', $patron)->orWhere('nombre_unc', 'ilike', $patron))
                        ->orWhereHas('docentePerfil.persona', function ($pq) use ($patron) {
                            $pq->where('cedula_per', 'ilike', $patron)
                                ->orWhere('nombres_per', 'ilike', $patron)
                                ->orWhere('apellidos_per', 'ilike', $patron)
                                ->orWhereRaw("TRIM(CONCAT(COALESCE(nombres_per, ''), ' ', COALESCE(apellidos_per, ''))) ILIKE ?", [$patron]);
                        });
                });
            })
            ->orderByDesc('id_caa')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (CargaAcademica $item) {
                $persona = $item->docentePerfil?->persona;
                $dedicacion = $persona?->dedicacionDocente;
                $docenteNombre = $persona
                    ? trim("{$persona->nombres_per} {$persona->apellidos_per}")
                    : 'Docente sin persona asociada';
                $unidad = $item->unidadCurricular;
                $seccion = $item->seccion;

                return array_merge($item->toArray(), [
                    'id'                   => $item->id_caa,
                    'docente_nombre'       => $docenteNombre,
                    'docente_cedula'       => $persona?->cedula_per,
                    'dedicacion_nombre'    => $dedicacion?->nombre_ded,
                    'horas_maximas_ded'    => $dedicacion?->horas_academicas_ded,
                    'etiqueta_registro'    => trim(($seccion?->nombre_sec ?? 'Sección') . ' — ' . ($unidad?->nombre_unc ?? 'Unidad')),
                ]);
            });

        $seccionesFiltro = Seccion::query()
            ->with(['lapsoAcademico:id_laa,codigo_laa,nombre_laa', 'programa:id_prg,nombre_prg,siglas_prg'])
            ->where('status_id', Seccion::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderByDesc('id_sec')
            ->get(['id_sec', 'codigo_sec', 'nombre_sec', 'semestre_sec', 'trayecto_sec', 'laa_id', 'prg_id']);

        $lapsosFiltro = LapsoAcademico::query()
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->orderByDesc('id_laa')
            ->get(['id_laa', 'codigo_laa', 'nombre_laa']);

        return Inertia::render('ProgramacionAcademica/SeccionUnidadDocente/index', [
            'asignaciones' => $asignaciones,
            'secciones'    => $seccionesFiltro,
            'lapsos'       => $lapsosFiltro,
            'filters'      => $filtros,
        ]);
    }

    /**
     * Vista de creación de nueva asignación Sección - Unidad - Docente.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', CargaAcademica::class);

        $secIdInicial = $request->integer('sec_id') ?: null;

        return Inertia::render('ProgramacionAcademica/SeccionUnidadDocente/create', array_merge(
            $this->cargarCatalogosFormulario(),
            ['secIdInicial' => $secIdInicial]
        ));
    }

    /**
     * Persiste una nueva asignación Sección - Unidad - Docente tras pasar validaciones de negocio.
     */
    public function store(StoreCargaAcademicaRequest $request): RedirectResponse
    {
        $this->authorize('create', CargaAcademica::class);

        try {
            $data = $request->validated();

            DB::transaction(function () use ($data) {
                CargaAcademica::create([
                    'sec_id'                => (int) $data['sec_id'],
                    'unc_id'                => (int) $data['unc_id'],
                    'pep_id'                => (int) $data['pep_id'],
                    'tipo_contratacion_caa' => $data['tipo_contratacion_caa'],
                    'horas_semanales_caa'   => (int) $data['horas_semanales_caa'],
                    'status_id'             => CargaAcademica::STATUS_ACTIVO,
                    'created_id'            => auth()->id(),
                    'updated_id'            => auth()->id(),
                ]);
            });

            return redirect()
                ->route('seccion_unidad_docente.index')
                ->with('success', 'Asignación Sección - Unidad - Docente registrada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al registrar la asignación: ' . $e->getMessage()]);
        }
    }

    /**
     * Vista de edición de una asignación Sección - Unidad - Docente existente.
     */
    public function edit(int $seccion_unidad_docente): Response
    {
        $registro = CargaAcademica::query()
            ->with([
                'seccion:id_sec,codigo_sec,nombre_sec,semestre_sec,trayecto_sec,laa_id,prg_id,mac_id',
                'seccion.lapsoAcademico:id_laa,codigo_laa,nombre_laa',
                'unidadCurricular:id_unc,codigo_unc,nombre_unc',
                'docentePerfil:id_pep,per_id,prf_id',
                'docentePerfil.persona:id_per,cedula_per,nombres_per,apellidos_per,doc_ded_id',
                'docentePerfil.persona.dedicacionDocente:id_ded,nombre_ded,horas_academicas_ded',
            ])
            ->where('id_caa', $seccion_unidad_docente)
            ->firstOrFail();

        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/SeccionUnidadDocente/edit', array_merge(
            ['asignacion' => $registro],
            $this->cargarCatalogosFormulario()
        ));
    }

    /**
     * Actualiza una asignación Sección - Unidad - Docente existente.
     */
    public function update(UpdateCargaAcademicaRequest $request, int $seccion_unidad_docente): RedirectResponse
    {
        $registro = CargaAcademica::where('id_caa', $seccion_unidad_docente)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $data = $request->validated();

            DB::transaction(function () use ($registro, $data) {
                $registro->update([
                    'sec_id'                => (int) $data['sec_id'],
                    'unc_id'                => (int) $data['unc_id'],
                    'pep_id'                => (int) $data['pep_id'],
                    'tipo_contratacion_caa' => $data['tipo_contratacion_caa'],
                    'horas_semanales_caa'   => (int) $data['horas_semanales_caa'],
                    'updated_id'            => auth()->id(),
                ]);
            });

            return redirect()
                ->route('seccion_unidad_docente.index')
                ->with('success', 'Asignación Sección - Unidad - Docente actualizada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al actualizar la asignación: ' . $e->getMessage()]);
        }
    }

    /**
     * Cambia el estatus lógico (1 <-> 2) sin eliminar de la base de datos.
     */
    public function alternarEstatus(int $seccion_unidad_docente): RedirectResponse
    {
        $registro = CargaAcademica::where('id_caa', $seccion_unidad_docente)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            return $this->ejecutarCambioEstatus($registro);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al cambiar el estatus de la asignación.']);
        }
    }

    /**
     * Si recibe `eliminar=1`, ejecuta baja lógica en interfaz (`deleted_at` + `status_id = 2`);
     * de lo contrario invierte únicamente el estatus lógico (`status_id` 1 <-> 2).
     */
    public function destroy(Request $request, int $seccion_unidad_docente): RedirectResponse
    {
        $registro = CargaAcademica::with(['seccion', 'unidadCurricular'])
            ->where('id_caa', $seccion_unidad_docente)
            ->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            if ($request->boolean('eliminar')) {
                $tieneHorarios = $registro->horarios()->whereNull('deleted_at')->exists();
                if ($tieneHorarios) {
                    return redirect()->back()->withErrors([
                        'error' => 'No puede eliminar esta asignación mientras tenga bloques horarios asociados. Elimine primero sus horarios.',
                    ]);
                }

                CargaAcademica::ejecutarBajaLogicaModelo($registro, auth()->id(), CargaAcademica::STATUS_INACTIVO);

                $nombreSeccion = $registro->seccion?->nombre_sec ?? 'Sección';
                $nombreUnidad = $registro->unidadCurricular?->nombre_unc ?? 'Unidad';

                return redirect()
                    ->back()
                    ->with('success', "La asignación '{$nombreUnidad}' en '{$nombreSeccion}' ha sido eliminada correctamente.");
            }

            return $this->ejecutarCambioEstatus($registro);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al procesar la solicitud sobre la asignación.']);
        }
    }
}
