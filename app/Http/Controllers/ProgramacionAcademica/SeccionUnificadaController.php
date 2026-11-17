<?php

namespace App\Http\Controllers\ProgramacionAcademica;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Seccion\StoreSeccionUnificadaRequest;
use App\Http\Requests\ProgramacionAcademica\Seccion\UpdateSeccionUnificadaRequest;
use App\Models\Academico\LapsoAcademico;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use App\Models\Personas\PersonaPerfil;
use App\Models\ProgramacionAcademica\CargaAcademica;
use App\Models\ProgramacionAcademica\Maestros\CohorteSede;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\Maestros\TipoSeccion;
use App\Models\ProgramacionAcademica\Maestros\Turno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador Unificado: Gestión transaccional de Secciones y su Carga Académica (`seccion` + `carga_academica`).
 *
 * Propósito: Permite consultar secciones con filtros por lapso, sede y estatus; ejecutar búsqueda
 * rápida por código o nombre (`buscarSeccion`) para modales con lupa; y persistir atómicamente en
 * `DB::transaction()` tanto la cabecera `seccion` como el detalle de unidades curriculares y docentes.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial del controlador unificado para Módulo 7 (Programación Académica).
 */
class SeccionUnificadaController extends Controller
{
    /**
     * Sincroniza atómicamente las filas de `carga_academica` asociadas a una `Seccion`.
     * Valida cada fila en secuencia dentro de la transacción activa para garantizar que la
     * sumatoria de horas del docente en el lapso considere todas las materias del mismo lote.
     *
     * @param  array<int, array<string, mixed>>  $cargas
     *
     * @throws ValidationException
     */
    private function sincronizarDetalleCarga(Seccion $seccion, array $cargas, bool $reemplazarOmitidas): void
    {
        $userId = auth()->id();

        $existentes = CargaAcademica::query()
            ->where('sec_id', (int) $seccion->id_sec)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('unc_id');

        if ($reemplazarOmitidas) {
            $entrantesUncIds = collect($cargas)
                ->pluck('unc_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($existentes as $uncIdExistente => $cargaExistente) {
                if (in_array((int) $uncIdExistente, $entrantesUncIds, true)) {
                    continue;
                }

                $tieneHorarios = $cargaExistente->horarios()->whereNull('deleted_at')->exists();
                if ($tieneHorarios) {
                    throw ValidationException::withMessages([
                        'cargas' => 'No se puede retirar una unidad curricular de la sección mientras posea bloques horarios activos.',
                    ]);
                }

                CargaAcademica::ejecutarBajaLogicaModelo($cargaExistente, $userId, CargaAcademica::STATUS_INACTIVO);
            }
        }

        foreach (array_values($cargas) as $indice => $fila) {
            $uncId = (int) $fila['unc_id'];
            $pepId = (int) $fila['pep_id'];
            $horas = (int) ($fila['horas_semanales_caa'] ?? 4);
            $tipoContratacion = trim((string) ($fila['tipo_contratacion_caa'] ?? 'Titular')) ?: 'Titular';

            $existente = $existentes->get($uncId);
            $ignoreCaaId = $existente ? (int) $existente->id_caa : null;

            $validacion = CargaAcademica::validarReglasAsignacion(
                (int) $seccion->id_sec,
                $uncId,
                $pepId,
                $horas,
                $ignoreCaaId,
                CargaAcademica::STATUS_ACTIVO
            );

            if (! empty($validacion['errors'])) {
                $erroresMapeados = [];
                foreach ($validacion['errors'] as $campo => $mensaje) {
                    $erroresMapeados["cargas.{$indice}.{$campo}"] = $mensaje;
                }
                $erroresMapeados['cargas'] = reset($validacion['errors']);

                throw ValidationException::withMessages($erroresMapeados);
            }

            if ($existente) {
                $existente->update([
                    'pep_id'                => $pepId,
                    'tipo_contratacion_caa' => $tipoContratacion,
                    'horas_semanales_caa'   => $horas,
                    'status_id'             => CargaAcademica::STATUS_ACTIVO,
                    'updated_id'            => $userId,
                ]);
            } else {
                CargaAcademica::create([
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
        }
    }

    /**
     * Listado paginado de secciones con filtros de lapso (`laa_id`), sede (`sed_id`) y estatus (`status`/`status_id`).
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Seccion::class);

        $perPage = min(max((int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10), 1), 100);
        $filtros = $request->only(['search', 'laa_id', 'sed_id', 'prg_id', 'status', 'status_id']);

        $programas = DB::table('programa')
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->orderBy('nombre_prg')
            ->get(['id_prg', 'nombre_prg', 'codigo_prg']);

        return Inertia::render('ProgramacionAcademica/Maestros/Seccion/index', [
            'secciones'            => Seccion::obtenerPaginados($filtros, $perPage),
            'lapsos'               => LapsoAcademico::where('status_id', LapsoAcademico::STATUS_ACTIVO)->orderByDesc('id_laa')->get(['id_laa', 'codigo_laa', 'nombre_laa']),
            'programas'            => $programas,
            'pnfs'                 => $programas,
            'sedes'                => DB::table('sede')->where('status_id', 1)->whereNull('deleted_at')->orderBy('nombre_sed')->get(['id_sed', 'nombre_sed']),
            'cohortes'             => CohorteSede::where('status_id', 1)->orderByDesc('id_cos')->get(['id_cos', 'nombre_cos', 'sep_id']),
            'mallas'               => MallaCurricular::where('status_id', 1)->orderBy('nombre_mac')->get(['id_mac', 'nombre_mac', 'prg_id']),
            'tiposSeccion'         => TipoSeccion::where('status_id', 1)->orderBy('id_tis')->get(['id_tis', 'codigo_tis', 'nombre_tis']),
            'turnos'               => Turno::where('status_id', 1)->orderBy('id_tur')->get(['id_tur', 'codigo_tur', 'nombre_tur']),
            'docentes'             => PersonaPerfil::obtenerDocentesActivosParaSelect(),
            'unidadesCurriculares' => UnidadCurricular::where('status_id', 1)->orderBy('codigo_unc')->get(['id_unc', 'codigo_unc', 'nombre_unc', 'tuc_id']),
            'filters'              => $filtros,
        ]);
    }

    /**
     * Guarda en una sola transacción atómica la cabecera de `seccion` y su detalle en `carga_academica`.
     */
    public function store(StoreSeccionUnificadaRequest $request): RedirectResponse
    {
        $this->authorize('create', Seccion::class);

        $validated = $request->validated();
        $cargas = $validated['cargas'] ?? [];
        unset($validated['cargas']);

        if (! empty($cargas)) {
            $this->authorize('create', CargaAcademica::class);
        }

        try {
            $cabecera = Seccion::normalizarFksCatalogo($validated);
            if (empty($cabecera['codigo_sec'])) {
                $cabecera['codigo_sec'] = $cabecera['nombre_sec'];
            }

            DB::transaction(function () use ($cabecera, $cargas) {
                Seccion::liberarNombreSiEliminado(
                    (string) $cabecera['nombre_sec'],
                    (int) $cabecera['laa_id'],
                    (int) $cabecera['sed_id']
                );

                $seccion = Seccion::create($cabecera + [
                    'status_id'  => Seccion::STATUS_ACTIVO,
                    'created_id' => auth()->id(),
                    'updated_id' => auth()->id(),
                ]);

                if (! empty($cargas)) {
                    $this->sincronizarDetalleCarga($seccion, $cargas, false);
                }
            });

            return redirect()
                ->route('secciones_unificadas.index')
                ->with('success', 'Sección y asignación de carga académica guardadas correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\DomainException $e) {
            return redirect()->back()->withInput()->withErrors(['nombre_sec' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->withErrors([
                'error' => 'Ocurrió un error al registrar la sección unificada: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Actualiza atómicamente en `DB::transaction()` la cabecera de `seccion` y sus filas en `carga_academica`.
     */
    public function update(UpdateSeccionUnificadaRequest $request, int $seccion): RedirectResponse
    {
        $registro = Seccion::where('id_sec', $seccion)->firstOrFail();
        $this->authorize('update', $registro);

        $validated = $request->validated();
        $incluyeDetalle = array_key_exists('cargas', $validated) || $request->has('cargas');
        $cargas = $validated['cargas'] ?? [];
        unset($validated['cargas']);

        if ($incluyeDetalle) {
            $this->authorize('update', CargaAcademica::class);
        }

        try {
            $cabecera = Seccion::normalizarFksCatalogo($validated);
            if (empty($cabecera['codigo_sec'])) {
                $cabecera['codigo_sec'] = $cabecera['nombre_sec'];
            }

            DB::transaction(function () use ($registro, $cabecera, $cargas, $incluyeDetalle) {
                Seccion::liberarNombreSiEliminado(
                    (string) $cabecera['nombre_sec'],
                    (int) $cabecera['laa_id'],
                    (int) $cabecera['sed_id'],
                    (int) $registro->id_sec
                );

                $registro->update($cabecera + ['updated_id' => auth()->id()]);

                if ($incluyeDetalle) {
                    $this->sincronizarDetalleCarga($registro, $cargas, true);
                }
            });

            return redirect()
                ->route('secciones_unificadas.index')
                ->with('success', 'Sección y su carga académica actualizadas correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\DomainException $e) {
            return redirect()->back()->withInput()->withErrors(['nombre_sec' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->withErrors([
                'error' => 'Ocurrió un error al actualizar la sección unificada: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Buscador rápido por código o nombre de sección para el modal de búsqueda con lupa.
     */
    public function buscarSeccion(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Seccion::class);

        $termino = trim((string) ($request->input('search') ?? $request->input('q') ?? ''));
        $laaId = $request->filled('laa_id') ? (int) $request->input('laa_id') : null;
        $sedId = $request->filled('sed_id') ? (int) $request->input('sed_id') : null;
        $limit = min(max((int) $request->input('limit', 15), 1), 30);

        $query = Seccion::query()
            ->with([
                'lapsoAcademico:id_laa,codigo_laa,nombre_laa',
                'programa:id_prg,nombre_prg,codigo_prg,siglas_prg',
                'sede:id_sed,nombre_sed',
                'turno:id_tur,codigo_tur,nombre_tur',
                'tipoSeccion:id_tis,codigo_tis,nombre_tis',
                'mallaCurricular:id_mac,nombre_mac',
                'cargasAcademicas' => fn ($q) => $q->whereNull('deleted_at')
                    ->with([
                        'unidadCurricular:id_unc,codigo_unc,nombre_unc',
                        'docentePerfil:id_pep,per_id,prf_id',
                        'docentePerfil.persona:id_per,cedula_per,nombres_per,apellidos_per,doc_ded_id',
                    ]),
            ])
            ->where('status_id', Seccion::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->when($laaId !== null && $laaId > 0, fn ($q) => $q->where('laa_id', $laaId))
            ->when($sedId !== null && $sedId > 0, fn ($q) => $q->where('sed_id', $sedId))
            ->when($termino !== '', function ($q) use ($termino) {
                $patron = "%{$termino}%";
                $q->where(function ($sub) use ($patron) {
                    $sub->where('codigo_sec', 'ilike', $patron)
                        ->orWhere('nombre_sec', 'ilike', $patron);
                })->orderByRaw(
                    'CASE WHEN LOWER(COALESCE(codigo_sec, \'\')) = LOWER(?) THEN 0 WHEN codigo_sec ILIKE ? THEN 1 ELSE 2 END',
                    [$termino, "{$termino}%"]
                );
            })
            ->orderBy('nombre_sec')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $query,
        ]);
    }
}
