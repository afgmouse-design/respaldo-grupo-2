<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\EstructuraAcademica\Maestros\Pnf;
use App\Models\EstructuraAcademica\Maestros\TipoPeriodo;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Malla Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Gestiona el ciclo de vida CRUD de las mallas curriculares (planes de estudio) mediante arquitectura SPA con Inertia.js.
 * Carga en create y edit los PNF activos (status_id = 1).
 * Implementa baja lógica asignando status_id = 2 en el método destroy.
 *
 * Actualización 2026-10-21 (Corrección de Redirección y Alertas en store y update):
 * Se corrigió la redirección en `store()` y `update()` para enviar al usuario a
 * `route('academico.malla-curricular.index')` con el mensaje flash 'success', en lugar de `back()`,
 * resolviendo el bloqueo donde el usuario no salía del formulario tras guardar/editar y
 * no se visualizaba la notificación flash de éxito en el maestro.
 *
 * Actualización 2026-11-08 (Constructor Dinámico de Trayectos, Tipo de Período y Unidades por Semestre):
 * Se inyectaron en `create()` y `edit()` los catálogos de `tiposPeriodo`, `trayectosCatalogo`,
 * `unidadesCatalogo` y `cohortes`, y se actualizaron `store()` y `update()` para persistir
 * atómicamente los trayectos dinámicos, el tipo de período (`tpd_id`) y las unidades curriculares por semestre.
 */
class MallaCurricularController extends Controller
{
    /**
     * Muestra el listado de mallas curriculares activas (status_id = 1) con filtros reactivos y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $mallas = MallaCurricular::obtenerPaginados(
            $request->only(['search']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricular/index', [
            'mallas'   => $mallas,
            'perPage'  => $perPage,
            'filters'  => array_merge(
                $request->only(['search', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva malla curricular.
     */
    public function create(): Response
    {
        $pnfs = Pnf::where('status_id', 1)->orderBy('nombre_pnf', 'asc')->get();
        $tiposPeriodo = TipoPeriodo::where('status_id', 1)->orderBy('id_tpd', 'asc')->get();
        $trayectosCatalogo = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra', 'asc')->get();
        $cohortes = DB::table('cohorte')->where('status_id', 1)->orderBy('id_coh', 'desc')->get();
        $unidadesCatalogo = UnidadCurricular::with('tipoUnidadCurricular')
            ->where('status_id', 1)
            ->orderBy('codigo_unc', 'asc')
            ->get()
            ->map(fn ($u) => [
                'id_unc'           => $u->id_unc,
                'codigo_unc'       => $u->codigo_unc ?? 'S/C',
                'nombre_unc'       => $u->nombre_unc,
                'tipo'             => $u->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                'tuc_id'           => $u->tuc_id,
                'tra_id'           => $u->tra_id ?? 1,
                'semestre_unc'     => $u->semestre_unc ?? 1,
                'uc'               => $u->unidades_credito_unc ?? 3,
                'htea'             => $u->htea_unc ?? 3,
                'htei'             => $u->htei_unc ?? 3,
                'thte'             => $u->thte_unc ?? 6,
            ]);

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricular/create', [
            'pnfs'              => $pnfs,
            'tiposPeriodo'      => $tiposPeriodo,
            'trayectosCatalogo' => $trayectosCatalogo,
            'cohortes'          => $cohortes,
            'unidadesCatalogo'  => $unidadesCatalogo,
        ]);
    }

    /**
     * Resuelve o registra el Trayecto correspondiente según el orden y el Tipo de Período (`tpd_id`).
     */
    protected function resolverTrayectoId(array $u): int
    {
        $orden = isset($u['numero_orden_tra']) ? (int) $u['numero_orden_tra'] : null;
        $tpdId = isset($u['tpd_id']) && $u['tpd_id'] ? (int) $u['tpd_id'] : 4;

        if ($orden !== null) {
            $romanos = [0 => 'Inicial', 1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];
            $etiqueta = $romanos[$orden] ?? (string) $orden;
            $nombreDeseado = $orden === 0 ? 'Trayecto Inicial' : "Trayecto {$etiqueta}";

            $existente = Trayecto::where('numero_orden_tra', $orden)
                ->where('tpd_id', $tpdId)
                ->where('status_id', 1)
                ->first();

            if ($existente) {
                return (int) $existente->id_tra;
            }

            $creado = Trayecto::create([
                'codigo_tra'       => "T-{$orden}",
                'nombre_tra'       => $nombreDeseado,
                'numero_orden_tra' => $orden,
                'tpd_id'           => $tpdId,
                'status_id'        => 1,
                'status'           => true,
                'created_id'       => auth()->id(),
            ]);

            return (int) $creado->id_tra;
        }

        if (!empty($u['tra_id']) && Trayecto::where('id_tra', (int) $u['tra_id'])->exists()) {
            return (int) $u['tra_id'];
        }

        $fallback = Trayecto::where('status_id', 1)->orderBy('id_tra')->first();
        return $fallback ? (int) $fallback->id_tra : 1;
    }

    /**
     * Almacena una nueva malla curricular en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_mac'                       => ['required', 'string', 'max:255'],
            'codigo_gaceta_mac'                => ['nullable', 'string', 'max:100'],
            'version_pensum_mac'               => ['nullable', 'string', 'max:50'],
            'fecha_aprobacion_mac'             => ['nullable', 'date'],
            'observacion_mac'                  => ['nullable', 'string'],
            'pnf_id'                           => ['required', 'integer', 'exists:pnf,id_pnf'],
            'creditos_minimos_tsu_mac'         => ['nullable', 'integer', 'min:0'],
            'horas_minimas_tsu_mac'            => ['nullable', 'integer', 'min:0'],
            'materias_obligatorias_tsu_mac'    => ['nullable', 'integer', 'min:0'],
            'creditos_minimos_ing_mac'         => ['nullable', 'integer', 'min:0'],
            'horas_minimas_ing_mac'            => ['nullable', 'integer', 'min:0'],
            'materias_obligatorias_ing_mac'    => ['nullable', 'integer', 'min:0'],
            'creditos_minimos_prosecucion_mac' => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas'           => ['nullable', 'array'],
        ]);

        try {
            $malla = DB::transaction(function () use ($validated) {
                $malla = MallaCurricular::create([
                    'nombre_mac'                       => trim($validated['nombre_mac']),
                    'codigo_gaceta_mac'                => !empty($validated['codigo_gaceta_mac']) ? trim($validated['codigo_gaceta_mac']) : null,
                    'version_pensum_mac'               => !empty($validated['version_pensum_mac']) ? trim($validated['version_pensum_mac']) : null,
                    'fecha_aprobacion_mac'             => $validated['fecha_aprobacion_mac'] ?? null,
                    'observacion_mac'                  => !empty($validated['observacion_mac']) ? trim($validated['observacion_mac']) : null,
                    'pnf_id'                           => $validated['pnf_id'],
                    'prg_id'                           => $validated['pnf_id'],
                    'creditos_minimos_tsu_mac'         => $validated['creditos_minimos_tsu_mac'] ?? null,
                    'horas_minimas_tsu_mac'            => $validated['horas_minimas_tsu_mac'] ?? null,
                    'materias_obligatorias_tsu_mac'    => $validated['materias_obligatorias_tsu_mac'] ?? null,
                    'creditos_minimos_ing_mac'         => $validated['creditos_minimos_ing_mac'] ?? null,
                    'horas_minimas_ing_mac'            => $validated['horas_minimas_ing_mac'] ?? null,
                    'materias_obligatorias_ing_mac'    => $validated['materias_obligatorias_ing_mac'] ?? null,
                    'creditos_minimos_prosecucion_mac' => $validated['creditos_minimos_prosecucion_mac'] ?? null,
                    'status_id'                        => 1,
                    'status'                           => true,
                    'created_id'                       => auth()->id(),
                ]);

                // Asignar unidades curriculares seleccionadas
                if (!empty($validated['unidades_seleccionadas']) && is_array($validated['unidades_seleccionadas'])) {
                    foreach ($validated['unidades_seleccionadas'] as $u) {
                        if ((!isset($u['incluido']) || !empty($u['incluido'])) && !empty($u['unc_id'])) {
                            $traId = $this->resolverTrayectoId($u);
                            MallaCurricularDetalle::create([
                                'mac_id'               => $malla->id_mac,
                                'unc_id'               => (int) $u['unc_id'],
                                'tra_id'               => $traId,
                                'ecu_id'               => $u['ecu_id'] ?? null,
                                'esm_id'               => $u['esm_id'] ?? null,
                                'tuc_id'               => $u['tuc_id'] ?? null,
                                'unidades_credito_mcd' => $u['uc'] ?? 3,
                                'htea_mcd'             => $u['htea'] ?? 3,
                                'htei_mcd'             => $u['htei'] ?? 3,
                                'thte_mcd'             => $u['thte'] ?? 6,
                                'duracion_semanas_mcd' => $u['duracion_semanas'] ?? 12,
                                'semestre_mcd'         => $u['semestre'] ?? 1,
                                'etapa_curricular_mcd' => $u['etapa'] ?? 'TSU',
                                'status_id'            => 1,
                                'status'               => true,
                                'created_id'           => auth()->id(),
                            ]);
                        }
                    }
                }

                return $malla;
            });

            return redirect()
                ->route('academico.malla-curricular.index')
                ->with('success', "La malla curricular '{$malla->nombre_mac}' ha sido registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la malla curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Retorna las unidades curriculares asociadas al PNF para visualización reactiva en el formulario.
     */
    public function unidadesPorPnf(int $pnfId): JsonResponse
    {
        // 1. Buscar detalles de mallas previas del mismo PNF
        $mallaIds = MallaCurricular::where('pnf_id', $pnfId)->pluck('id_mac');

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'ejeCurricular',
            'escalaMedicion',
        ])
        ->whereIn('mac_id', $mallaIds)
        ->where('status_id', 1)
        ->orderBy('tra_id')
        ->orderBy('semestre_mcd')
        ->get();

        $bloques = [];
        $totalCreditosTsu = 0;
        $totalHorasTsu = 0;
        $totalMateriasTsu = 0;
        $totalCreditosIng = 0;
        $totalHorasIng = 0;
        $totalMateriasIng = 0;

        if ($detalles->isNotEmpty()) {
            $detallesUnicos = $detalles->unique(function ($item) {
                $sem = $item->semestre_mcd ?? ($item->unidadCurricular ? $item->unidadCurricular->semestre_unc : 1);
                $tra = $item->tra_id ?? ($item->unidadCurricular ? $item->unidadCurricular->tra_id : 1);
                return $item->unc_id . '-' . $tra . '-' . $sem;
            });

            foreach ($detallesUnicos as $det) {
                $traId = (int) ($det->tra_id ?? ($det->unidadCurricular ? $det->unidadCurricular->tra_id : 1));
                $traNombre = $det->trayecto ? $det->trayecto->nombre_tra : "Trayecto {$traId}";
                
                $semestreNum = (int) ($det->semestre_mcd ?? ($det->unidadCurricular ? $det->unidadCurricular->semestre_unc : 1));
                if ($det->trayecto && preg_match('/semestre\s*(\d+)/i', $det->trayecto->nombre_tra, $m)) {
                    $semestreNum = (int) $m[1];
                }

                $clave = preg_match('/semestre/i', $traNombre)
                    ? $traNombre
                    : "{$traNombre} - Semestre {$semestreNum}";

                if (!isset($bloques[$clave])) {
                    $bloques[$clave] = [
                        'trayecto_id'     => $traId,
                        'trayecto_nombre' => $traNombre,
                        'semestre'        => $semestreNum,
                        'unidades'        => [],
                    ];
                }

                $esTsu = ($semestreNum <= 4) && !preg_match('/(trayecto\s*[34]|trayecto\s*(iii|iv))\b/i', $traNombre);
                $etapa = $det->etapa_curricular_mcd ?? ($esTsu ? 'TSU' : 'Ingeniería');
                $uc = (int) ($det->unidades_credito_mcd ?? ($det->unidadCurricular ? $det->unidadCurricular->unidades_credito_unc : 3));
                $thte = (int) ($det->thte_mcd ?? ($det->unidadCurricular ? $det->unidadCurricular->thte_unc : 6));
                $htea = (int) ($det->htea_mcd ?? ($det->unidadCurricular ? $det->unidadCurricular->htea_unc : 3));
                $htei = (int) ($det->htei_mcd ?? ($det->unidadCurricular ? $det->unidadCurricular->htei_unc : 3));

                if ($etapa === 'TSU') {
                    $totalCreditosTsu += $uc;
                    $totalHorasTsu += $thte;
                    $totalMateriasTsu++;
                }
                $totalCreditosIng += $uc;
                $totalHorasIng += $thte;
                $totalMateriasIng++;

                $bloques[$clave]['unidades'][] = [
                    'id_mcd'           => $det->id_mcd,
                    'unc_id'           => $det->unc_id,
                    'codigo'           => $det->unidadCurricular->codigo_unc ?? 'N/A',
                    'nombre'           => $det->unidadCurricular->nombre_unc ?? 'Sin nombre',
                    'tipo'             => $det->unidadCurricular->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                    'tra_id'           => $traId,
                    'ecu_id'           => $det->ecu_id,
                    'esm_id'           => $det->esm_id,
                    'semestre'         => $semestreNum,
                    'etapa'            => $etapa,
                    'uc'               => $uc,
                    'htea'             => $htea,
                    'htei'             => $htei,
                    'thte'             => $thte,
                    'duracion_semanas' => $det->duracion_semanas_mcd ?? 12,
                    'incluido'         => true,
                ];
            }
        } else {
            // Si no hay mallas previas, consultar catálogo de unidades
            $pnf = Pnf::find($pnfId);
            $codigoPrefix = $pnf && $pnf->codigo_pnf ? strtoupper(substr($pnf->codigo_pnf, 0, 3)) : null;

            $query = UnidadCurricular::with(['tipoUnidadCurricular', 'trayecto'])->where('status_id', 1);
            if ($codigoPrefix) {
                $unidades = (clone $query)->where('codigo_unc', 'like', "{$codigoPrefix}%")->orderBy('codigo_unc')->get();
                if ($unidades->isEmpty()) {
                    $unidades = $query->orderBy('codigo_unc')->get();
                }
            } else {
                $unidades = $query->orderBy('codigo_unc')->get();
            }

            foreach ($unidades as $u) {
                $semestreNum = (int) ($u->semestre_unc ?? 1);
                $traId = (int) ($u->tra_id ?? 1);
                $traNombre = $u->trayecto ? $u->trayecto->nombre_tra : "Trayecto {$traId}";
                $clave = "{$traNombre} - Semestre {$semestreNum}";

                if (!isset($bloques[$clave])) {
                    $bloques[$clave] = [
                        'trayecto_id'     => $traId,
                        'trayecto_nombre' => $traNombre,
                        'semestre'        => $semestreNum,
                        'unidades'        => [],
                    ];
                }

                $esTsu = ($semestreNum <= 4) && !preg_match('/(trayecto\s*[34]|trayecto\s*(iii|iv))\b/i', $traNombre);
                $etapa = $esTsu ? 'TSU' : 'Ingeniería';
                $uc = (int) ($u->unidades_credito_unc ?? 3);
                $thte = (int) ($u->thte_unc ?? 6);
                $htea = (int) ($u->htea_unc ?? 3);
                $htei = (int) ($u->htei_unc ?? 3);

                $bloques[$clave]['unidades'][] = [
                    'id_mcd'           => null,
                    'unc_id'           => $u->id_unc,
                    'codigo'           => $u->codigo_unc ?? 'N/A',
                    'nombre'           => $u->nombre_unc ?? 'Sin nombre',
                    'tipo'             => $u->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                    'tra_id'           => $traId,
                    'ecu_id'           => null,
                    'esm_id'           => null,
                    'semestre'         => $semestreNum,
                    'etapa'            => $etapa,
                    'uc'               => $uc,
                    'htea'             => $htea,
                    'htei'             => $htei,
                    'thte'             => $thte,
                    'duracion_semanas' => 12,
                    'incluido'         => true,
                ];

                if ($etapa === 'TSU') {
                    $totalCreditosTsu += $uc;
                    $totalHorasTsu += $thte;
                    $totalMateriasTsu++;
                }
                $totalCreditosIng += $uc;
                $totalHorasIng += $thte;
                $totalMateriasIng++;
            }
        }

        return response()->json([
            'pnf_id'            => $pnfId,
            'total_unidades'    => $totalMateriasIng,
            'bloques'           => array_values($bloques),
            'totales_sugeridos' => [
                'creditos_tsu'         => $totalCreditosTsu,
                'horas_tsu'            => $totalHorasTsu,
                'materias_tsu'         => $totalMateriasTsu,
                'creditos_ing'         => $totalCreditosIng,
                'horas_ing'            => $totalHorasIng,
                'materias_ing'         => $totalMateriasIng,
                'creditos_prosecucion' => max(0, $totalCreditosIng - $totalCreditosTsu),
            ],
        ]);
    }

    /**
     * Muestra el detalle de una malla curricular específica.
     */
    public function show(int $id): Response
    {
        $malla = MallaCurricular::with([
            'pnf',
            'detalles.unidadCurricular',
            'detalles.trayecto',
            'detalles.ejeCurricular',
            'detalles.escalaMedicion',
        ])
            ->where('id_mac', $id)
            ->firstOrFail();

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricular/show', [
            'malla' => $malla,
        ]);
    }

    /**
     * Muestra el formulario para editar una malla curricular.
     */
    public function edit(int $id): Response
    {
        $malla = MallaCurricular::with([
            'pnf',
            'detalles' => fn ($q) => $q->where('status_id', 1)->orderBy('tra_id')->orderBy('semestre_mcd')->orderBy('id_mcd'),
            'detalles.unidadCurricular.tipoUnidadCurricular',
            'detalles.trayecto',
            'detalles.ejeCurricular',
        ])
            ->where('id_mac', $id)
            ->firstOrFail();

        $pnfs = Pnf::where('status_id', 1)->orderBy('nombre_pnf', 'asc')->get();
        $tiposPeriodo = TipoPeriodo::where('status_id', 1)->orderBy('id_tpd', 'asc')->get();
        $trayectosCatalogo = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra', 'asc')->get();
        $cohortes = DB::table('cohorte')->where('status_id', 1)->orderBy('id_coh', 'desc')->get();
        $unidadesCatalogo = UnidadCurricular::with('tipoUnidadCurricular')
            ->where('status_id', 1)
            ->orderBy('codigo_unc', 'asc')
            ->get()
            ->map(fn ($u) => [
                'id_unc'           => $u->id_unc,
                'codigo_unc'       => $u->codigo_unc ?? 'S/C',
                'nombre_unc'       => $u->nombre_unc,
                'tipo'             => $u->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                'tuc_id'           => $u->tuc_id,
                'tra_id'           => $u->tra_id ?? 1,
                'semestre_unc'     => $u->semestre_unc ?? 1,
                'uc'               => $u->unidades_credito_unc ?? 3,
                'htea'             => $u->htea_unc ?? 3,
                'htei'             => $u->htei_unc ?? 3,
                'thte'             => $u->thte_unc ?? 6,
            ]);

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricular/edit', [
            'malla'             => $malla,
            'pnfs'              => $pnfs,
            'tiposPeriodo'      => $tiposPeriodo,
            'trayectosCatalogo' => $trayectosCatalogo,
            'cohortes'          => $cohortes,
            'unidadesCatalogo'  => $unidadesCatalogo,
        ]);
    }

    /**
     * Actualiza los datos de una malla curricular existente y sincroniza sus unidades curriculares por trayecto y semestre.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_mac'                       => ['required', 'string', 'max:255'],
            'codigo_gaceta_mac'                => ['nullable', 'string', 'max:100'],
            'version_pensum_mac'               => ['nullable', 'string', 'max:50'],
            'fecha_aprobacion_mac'             => ['nullable', 'date'],
            'observacion_mac'                  => ['nullable', 'string'],
            'pnf_id'                           => ['required', 'integer', 'exists:pnf,id_pnf'],
            'creditos_minimos_tsu_mac'         => ['nullable', 'integer', 'min:0'],
            'horas_minimas_tsu_mac'            => ['nullable', 'integer', 'min:0'],
            'materias_obligatorias_tsu_mac'    => ['nullable', 'integer', 'min:0'],
            'creditos_minimos_ing_mac'         => ['nullable', 'integer', 'min:0'],
            'horas_minimas_ing_mac'            => ['nullable', 'integer', 'min:0'],
            'materias_obligatorias_ing_mac'    => ['nullable', 'integer', 'min:0'],
            'creditos_minimos_prosecucion_mac' => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas'           => ['nullable', 'array'],
        ]);

        try {
            $malla = DB::transaction(function () use ($validated, $id) {
                $malla = MallaCurricular::where('id_mac', $id)->firstOrFail();

                $malla->update([
                    'nombre_mac'                       => trim($validated['nombre_mac']),
                    'codigo_gaceta_mac'                => !empty($validated['codigo_gaceta_mac']) ? trim($validated['codigo_gaceta_mac']) : null,
                    'version_pensum_mac'               => !empty($validated['version_pensum_mac']) ? trim($validated['version_pensum_mac']) : null,
                    'fecha_aprobacion_mac'             => $validated['fecha_aprobacion_mac'] ?? null,
                    'observacion_mac'                  => !empty($validated['observacion_mac']) ? trim($validated['observacion_mac']) : null,
                    'pnf_id'                           => $validated['pnf_id'],
                    'prg_id'                           => $validated['pnf_id'],
                    'creditos_minimos_tsu_mac'         => $validated['creditos_minimos_tsu_mac'] ?? null,
                    'horas_minimas_tsu_mac'            => $validated['horas_minimas_tsu_mac'] ?? null,
                    'materias_obligatorias_tsu_mac'    => $validated['materias_obligatorias_tsu_mac'] ?? null,
                    'creditos_minimos_ing_mac'         => $validated['creditos_minimos_ing_mac'] ?? null,
                    'horas_minimas_ing_mac'            => $validated['horas_minimas_ing_mac'] ?? null,
                    'materias_obligatorias_ing_mac'    => $validated['materias_obligatorias_ing_mac'] ?? null,
                    'creditos_minimos_prosecucion_mac' => $validated['creditos_minimos_prosecucion_mac'] ?? null,
                    'updated_id'                       => auth()->id(),
                ]);

                if (array_key_exists('unidades_seleccionadas', $validated) && is_array($validated['unidades_seleccionadas'])) {
                    // Desactivar detalles previos y registrar/reactivar los actuales
                    MallaCurricularDetalle::where('mac_id', $malla->id_mac)->update([
                        'status_id'  => 2,
                        'status'     => false,
                        'updated_id' => auth()->id(),
                    ]);

                    foreach ($validated['unidades_seleccionadas'] as $u) {
                        if ((!isset($u['incluido']) || !empty($u['incluido'])) && !empty($u['unc_id'])) {
                            $traId = $this->resolverTrayectoId($u);
                            $semestre = (int) ($u['semestre'] ?? 1);

                            $detalleExistente = MallaCurricularDetalle::where('mac_id', $malla->id_mac)
                                ->where('unc_id', (int) $u['unc_id'])
                                ->where('tra_id', $traId)
                                ->where('semestre_mcd', $semestre)
                                ->first();

                            if ($detalleExistente) {
                                $detalleExistente->update([
                                    'unidades_credito_mcd' => $u['uc'] ?? 3,
                                    'htea_mcd'             => $u['htea'] ?? 3,
                                    'htei_mcd'             => $u['htei'] ?? 3,
                                    'thte_mcd'             => $u['thte'] ?? 6,
                                    'duracion_semanas_mcd' => $u['duracion_semanas'] ?? 12,
                                    'etapa_curricular_mcd' => $u['etapa'] ?? 'TSU',
                                    'status_id'            => 1,
                                    'status'               => true,
                                    'updated_id'           => auth()->id(),
                                ]);
                            } else {
                                MallaCurricularDetalle::create([
                                    'mac_id'               => $malla->id_mac,
                                    'unc_id'               => (int) $u['unc_id'],
                                    'tra_id'               => $traId,
                                    'ecu_id'               => $u['ecu_id'] ?? null,
                                    'esm_id'               => $u['esm_id'] ?? null,
                                    'tuc_id'               => $u['tuc_id'] ?? null,
                                    'unidades_credito_mcd' => $u['uc'] ?? 3,
                                    'htea_mcd'             => $u['htea'] ?? 3,
                                    'htei_mcd'             => $u['htei'] ?? 3,
                                    'thte_mcd'             => $u['thte'] ?? 6,
                                    'duracion_semanas_mcd' => $u['duracion_semanas'] ?? 12,
                                    'semestre_mcd'         => $semestre,
                                    'etapa_curricular_mcd' => $u['etapa'] ?? 'TSU',
                                    'status_id'            => 1,
                                    'status'               => true,
                                    'created_id'           => auth()->id(),
                                ]);
                            }
                        }
                    }
                }

                return $malla;
            });

            return redirect()
                ->route('academico.malla-curricular.index')
                ->with('success', "La malla curricular '{$malla->nombre_mac}' ha sido actualizada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar la malla curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus activo/inactivo de la malla curricular verificando que el PNF padre esté activo.
     */
    public function alternarEstatus(Request $request, int $id): RedirectResponse
    {
        try {
            $malla = MallaCurricular::where('id_mac', $id)->firstOrFail();
            $nuevoEstatus = ($malla->status_id === 1 || $malla->status) ? 2 : 1;
            $nombre = $malla->nombre_mac;

            MallaCurricular::alternarEstatus($id, $nuevoEstatus);

            $mensaje = ($nuevoEstatus === 1)
                ? "La malla curricular '{$nombre}' ha sido activada exitosamente."
                : "La malla curricular '{$nombre}' ha sido desactivada exitosamente.";

            return redirect()
                ->back()
                ->with('success', $mensaje);
        } catch (\DomainException $de) {
            return redirect()
                ->back()
                ->withErrors(['error' => $de->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo cambiar el estatus de la malla curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Realiza la baja lógica de la malla curricular llamando a alternarEstatus($id, 2).
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $malla = MallaCurricular::where('id_mac', $id)->first();
            $nombre = $malla ? $malla->nombre_mac : 'Malla curricular';

            MallaCurricular::alternarEstatus($id, 2);

            return redirect()
                ->back()
                ->with('success', "La malla curricular '{$nombre}' ha sido dada de baja (inactivada) exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo dar de baja la malla curricular: ' . $e->getMessage()]);
        }
    }
}

