<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\Academico\TipoLapso;
use App\Models\EstructuraAcademica\Maestros\EjeCurricular;
use App\Models\EstructuraAcademica\Maestros\EscalaMedicion;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\EstructuraAcademica\Maestros\Programa;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Malla Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Gestiona el ciclo de vida CRUD de las mallas curriculares (`malla_curricular` y
 * `malla_curricular_detalle`) asociadas a un Programa (`prg_id`), mediante SPA con Inertia.js.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` operando sobre `Programa` (`prg_id`), `TipoLapso`
 *    (`tla_id`), columnas `total_credito_*` / `total_horas_*` / `total_unidades_*` y `status_id`
 *    (sin `Pnf`, `TipoPeriodo`, `status` booleano ni atributos movidos de `unidad_curricular`).
 *  - 2026-11-16: Ampliado con carga de catálogos `ejes` y `escalas`, relación `programa.escalaMedicion`,
 *    validación y persistencia de `ecu_id`, `htea_mcd`, `htei_mcd`, `thte_mcd`,
 *    `duracion_semestres_mcd` (1 o 2 semestres) y `nota_minima_aprobatoria_mcd` por unidad curricular.
 */
class MallaCurricularController extends Controller
{
    /**
     * Carga los catálogos compartidos requeridos por las vistas create y edit del constructor de malla.
     */
    private function cargarCatalogosFormulario(): array
    {
        $programas = Programa::with('escalaMedicion')
            ->where('status_id', 1)
            ->orderBy('nombre_prg', 'asc')
            ->get();
        $tiposLapso = TipoLapso::where('status_id', 1)->orderBy('id_tla', 'asc')->get();
        $trayectosCatalogo = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra', 'asc')->get();
        $cohortes = DB::table('cohorte')->where('status_id', 1)->orderBy('id_coh', 'desc')->get();
        $ejes = EjeCurricular::where('status_id', 1)
            ->orderBy('nombre_ecu', 'asc')
            ->get(['id_ecu', 'codigo_ecu', 'nombre_ecu']);
        $escalas = EscalaMedicion::where('status_id', 1)
            ->orderBy('id_esm', 'asc')
            ->get(['id_esm', 'nombre_esm', 'nota_aprobatoria_esm', 'nota_minima_esm', 'nota_maxima_esm']);

        $ultimosDetallesPorUnidad = MallaCurricularDetalle::where('status_id', 1)
            ->orderByDesc('id_mcd')
            ->get()
            ->unique('unc_id')
            ->keyBy('unc_id');

        $unidadesCatalogo = UnidadCurricular::with('tipoUnidadCurricular')
            ->where('status_id', 1)
            ->orderBy('codigo_unc', 'asc')
            ->get()
            ->map(function ($u) use ($ultimosDetallesPorUnidad) {
                $detallePrevio = $ultimosDetallesPorUnidad->get($u->id_unc);
                $htea = $detallePrevio && $detallePrevio->htea_mcd !== null ? (int) $detallePrevio->htea_mcd : 3;
                $htei = $detallePrevio && $detallePrevio->htei_mcd !== null ? (int) $detallePrevio->htei_mcd : 3;
                $thte = $detallePrevio && $detallePrevio->thte_mcd !== null ? (int) $detallePrevio->thte_mcd : ($htea + $htei);

                return [
                    'id_unc'             => $u->id_unc,
                    'codigo_unc'         => $u->codigo_unc ?? 'S/C',
                    'nombre_unc'         => $u->nombre_unc,
                    'tipo'               => $u->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                    'tuc_id'             => $u->tuc_id,
                    'tra_id'             => 1,
                    'semestre_unc'       => $detallePrevio ? (int) ($detallePrevio->semestre_mcd ?? 1) : 1,
                    'ecu_id'             => $detallePrevio?->ecu_id ? (int) $detallePrevio->ecu_id : null,
                    'uc'                 => $detallePrevio && $detallePrevio->unidades_credito_mcd !== null ? (int) $detallePrevio->unidades_credito_mcd : 3,
                    'htea'               => $htea,
                    'htei'               => $htei,
                    'thte'               => $thte,
                    'duracion_semestres' => $detallePrevio && in_array((int) ($detallePrevio->duracion_semestres_mcd ?? 1), [1, 2], true)
                        ? (int) $detallePrevio->duracion_semestres_mcd
                        : 1,
                    'nota_aprobatoria'   => $detallePrevio && $detallePrevio->nota_minima_aprobatoria_mcd !== null
                        ? (float) $detallePrevio->nota_minima_aprobatoria_mcd
                        : null,
                ];
            });

        return [
            'pnfs'              => $programas,
            'programas'         => $programas,
            'tiposPeriodo'      => $tiposLapso,
            'tiposLapso'        => $tiposLapso,
            'trayectosCatalogo' => $trayectosCatalogo,
            'cohortes'          => $cohortes,
            'ejes'              => $ejes,
            'escalas'           => $escalas,
            'unidadesCatalogo'  => $unidadesCatalogo,
        ];
    }

    /**
     * Normaliza alias de entrada (`pnf_id` / `eje_id` / `hea` / `duracion` / `nota_aprobatoria`, etc.)
     * y valida tanto la cabecera de la malla como los parámetros académicos de cada unidad curricular.
     */
    private function validarMalla(Request $request): array
    {
        $unidadesRaw = $request->input('unidades_seleccionadas');
        if (is_array($unidadesRaw)) {
            $unidadesNormalizadas = array_map(function ($u) {
                if (! is_array($u)) {
                    return $u;
                }

                $ecuRaw = $u['ecu_id'] ?? $u['eje_id'] ?? null;
                $hteaRaw = $u['htea'] ?? $u['hea'] ?? null;
                $hteiRaw = $u['htei'] ?? $u['hei'] ?? null;
                $thteRaw = $u['thte'] ?? $u['hte'] ?? null;
                $duracionSemestresRaw = $u['duracion_semestres'] ?? $u['duracion'] ?? 1;
                $notaRaw = $u['nota_minima_aprobatoria_mcd'] ?? $u['nota_aprobatoria'] ?? null;

                return array_merge($u, [
                    'ecu_id'                      => ($ecuRaw === '' || $ecuRaw === null) ? null : $ecuRaw,
                    'esm_id'                      => (isset($u['esm_id']) && $u['esm_id'] !== '') ? $u['esm_id'] : null,
                    'htea'                        => ($hteaRaw === '' || $hteaRaw === null) ? null : $hteaRaw,
                    'htei'                        => ($hteiRaw === '' || $hteiRaw === null) ? null : $hteiRaw,
                    'thte'                        => ($thteRaw === '' || $thteRaw === null) ? null : $thteRaw,
                    'duracion_semestres'          => ($duracionSemestresRaw === '' || $duracionSemestresRaw === null) ? 1 : $duracionSemestresRaw,
                    'nota_minima_aprobatoria_mcd' => ($notaRaw === '' || $notaRaw === null) ? null : $notaRaw,
                ]);
            }, $unidadesRaw);

            $request->merge(['unidades_seleccionadas' => $unidadesNormalizadas]);
        }

        $request->merge([
            'prg_id'                        => $request->input('prg_id', $request->input('pnf_id')),
            'total_credito_tsu_mac'         => $request->input('total_credito_tsu_mac', $request->input('creditos_minimos_tsu_mac')),
            'total_horas_tsu_mac'           => $request->input('total_horas_tsu_mac', $request->input('horas_minimas_tsu_mac')),
            'total_unidades_tsu_mac'        => $request->input('total_unidades_tsu_mac', $request->input('materias_obligatorias_tsu_mac')),
            'total_credito_ing_mac'         => $request->input('total_credito_ing_mac', $request->input('creditos_minimos_ing_mac')),
            'total_horas_ing_mac'           => $request->input('total_horas_ing_mac', $request->input('horas_minimas_ing_mac')),
            'total_unidades_ing_mac'        => $request->input('total_unidades_ing_mac', $request->input('materias_obligatorias_ing_mac')),
            'total_credito_prosecucion_mac' => $request->input('total_credito_prosecucion_mac', $request->input('creditos_minimos_prosecucion_mac')),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre_mac'                                           => ['required', 'string', 'max:255'],
            'codigo_gaceta_mac'                                    => ['nullable', 'string', 'max:50'],
            'version_pensum_mac'                                   => ['nullable', 'string', 'max:50'],
            'fecha_aprobacion_mac'                                 => ['nullable', 'date'],
            'observacion_mac'                                      => ['nullable', 'string'],
            'prg_id'                                               => ['required', 'integer', 'exists:programa,id_prg'],
            'tra_inicio_id'                                        => ['nullable', 'integer', 'exists:trayecto,id_tra'],
            'salida_tsu_sem_id'                                    => ['nullable', 'integer', 'exists:semestre,id_sem'],
            'salida_ing_sem_id'                                    => ['nullable', 'integer', 'exists:semestre,id_sem'],
            'total_credito_tsu_mac'                                => ['nullable', 'integer', 'min:0'],
            'total_horas_tsu_mac'                                  => ['nullable', 'integer', 'min:0'],
            'total_unidades_tsu_mac'                               => ['nullable', 'integer', 'min:0'],
            'total_credito_ing_mac'                                => ['nullable', 'integer', 'min:0'],
            'total_horas_ing_mac'                                  => ['nullable', 'integer', 'min:0'],
            'total_unidades_ing_mac'                               => ['nullable', 'integer', 'min:0'],
            'total_credito_prosecucion_mac'                        => ['nullable', 'integer', 'min:0'],
            'total_horas_prosecucion_mac'                          => ['nullable', 'integer', 'min:0'],
            'total_unidades_prosecucion_mac'                       => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas'                               => ['nullable', 'array'],
            'unidades_seleccionadas.*.incluido'                    => ['nullable', 'boolean'],
            'unidades_seleccionadas.*.unc_id'                      => ['required', 'integer', 'exists:unidad_curricular,id_unc'],
            'unidades_seleccionadas.*.tra_id'                      => ['nullable', 'integer'],
            'unidades_seleccionadas.*.tla_id'                      => ['nullable', 'integer'],
            'unidades_seleccionadas.*.tpd_id'                      => ['nullable', 'integer'],
            'unidades_seleccionadas.*.numero_orden_tra'            => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas.*.semestre'                    => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas.*.etapa'                       => ['nullable', 'string', 'max:50'],
            'unidades_seleccionadas.*.ecu_id'                      => ['nullable', 'integer', 'exists:eje_curricular,id_ecu'],
            'unidades_seleccionadas.*.esm_id'                      => ['nullable', 'integer', 'exists:escala_medicion,id_esm'],
            'unidades_seleccionadas.*.uc'                          => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas.*.htea'                        => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas.*.htei'                        => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas.*.thte'                        => ['nullable', 'integer', 'min:0'],
            'unidades_seleccionadas.*.duracion_semestres'          => ['nullable', 'integer', 'in:1,2'],
            'unidades_seleccionadas.*.duracion_semanas'            => ['nullable', 'integer', 'min:1'],
            'unidades_seleccionadas.*.nota_minima_aprobatoria_mcd' => ['nullable', 'numeric', 'min:0'],
        ], [
            'unidades_seleccionadas.*.ecu_id.exists'             => 'El eje curricular seleccionado no es válido.',
            'unidades_seleccionadas.*.htea.min'                  => 'Las horas HEA deben ser un entero mayor o igual a 0.',
            'unidades_seleccionadas.*.htei.min'                  => 'Las horas HEI deben ser un entero mayor o igual a 0.',
            'unidades_seleccionadas.*.thte.min'                  => 'Las horas HTE deben ser un entero mayor o igual a 0.',
            'unidades_seleccionadas.*.duracion_semestres.in'     => 'La duración debe ser 1 Semestre o 2 Semestres.',
            'unidades_seleccionadas.*.nota_minima_aprobatoria_mcd.min' => 'La nota aprobatoria no puede ser negativa.',
        ]);

        $validator->after(function ($v) use ($request) {
            $unidades = $request->input('unidades_seleccionadas', []);
            if (! is_array($unidades) || empty($unidades)) {
                return;
            }

            $programaEsmId = Programa::where('id_prg', (int) $request->input('prg_id'))->value('esm_id');
            $esmIds = collect($unidades)
                ->pluck('esm_id')
                ->filter()
                ->when($programaEsmId, fn ($c) => $c->push($programaEsmId))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $escalasMap = $esmIds->isNotEmpty()
                ? EscalaMedicion::whereIn('id_esm', $esmIds)->get()->keyBy('id_esm')
                : collect();

            foreach ($unidades as $idx => $u) {
                if (! is_array($u) || (isset($u['incluido']) && empty($u['incluido']))) {
                    continue;
                }

                $nota = $u['nota_minima_aprobatoria_mcd'] ?? null;
                if ($nota === null || $nota === '') {
                    continue;
                }

                $esmId = ! empty($u['esm_id']) ? (int) $u['esm_id'] : ($programaEsmId ? (int) $programaEsmId : null);
                $escala = $esmId ? $escalasMap->get($esmId) : null;
                if (! $escala) {
                    continue;
                }

                $valorNota = (float) $nota;
                $min = $escala->nota_minima_esm !== null ? (float) $escala->nota_minima_esm : 0.0;
                $max = $escala->nota_maxima_esm !== null ? (float) $escala->nota_maxima_esm : 100.0;

                if ($valorNota < $min || $valorNota > $max) {
                    $codigoLabel = ! empty($u['codigo']) ? " [{$u['codigo']}]" : '';
                    $mensaje = "La nota aprobatoria{$codigoLabel} ({$valorNota}) debe estar entre {$min} y {$max} según la escala '{$escala->nombre_esm}'.";
                    $v->errors()->add("unidades_seleccionadas.{$idx}.nota_minima_aprobatoria_mcd", $mensaje);
                    if (! $v->errors()->has('error')) {
                        $v->errors()->add('error', $mensaje);
                    }
                }
            }
        });

        return $validator->validate();
    }

    /**
     * Construye el arreglo de persistencia garantizando enteros no nulos en los totales exigidos por `sogac_mio.sql`.
     */
    private function construirPayloadMalla(array $validated): array
    {
        return [
            'prg_id'                         => (int) $validated['prg_id'],
            'tra_inicio_id'                  => $validated['tra_inicio_id'] ?? null,
            'salida_tsu_sem_id'              => $validated['salida_tsu_sem_id'] ?? null,
            'salida_ing_sem_id'              => $validated['salida_ing_sem_id'] ?? null,
            'nombre_mac'                     => trim($validated['nombre_mac']),
            'codigo_gaceta_mac'              => !empty($validated['codigo_gaceta_mac']) ? trim($validated['codigo_gaceta_mac']) : null,
            'version_pensum_mac'             => !empty($validated['version_pensum_mac']) ? trim($validated['version_pensum_mac']) : null,
            'fecha_aprobacion_mac'           => $validated['fecha_aprobacion_mac'] ?? null,
            'observacion_mac'                => !empty($validated['observacion_mac']) ? trim($validated['observacion_mac']) : null,
            'total_credito_tsu_mac'          => (int) ($validated['total_credito_tsu_mac'] ?? 0),
            'total_horas_tsu_mac'            => (int) ($validated['total_horas_tsu_mac'] ?? 0),
            'total_unidades_tsu_mac'         => (int) ($validated['total_unidades_tsu_mac'] ?? 0),
            'total_credito_ing_mac'          => (int) ($validated['total_credito_ing_mac'] ?? 0),
            'total_horas_ing_mac'            => (int) ($validated['total_horas_ing_mac'] ?? 0),
            'total_unidades_ing_mac'         => (int) ($validated['total_unidades_ing_mac'] ?? 0),
            'total_credito_prosecucion_mac'  => (int) ($validated['total_credito_prosecucion_mac'] ?? 0),
            'total_horas_prosecucion_mac'    => (int) ($validated['total_horas_prosecucion_mac'] ?? 0),
            'total_unidades_prosecucion_mac' => (int) ($validated['total_unidades_prosecucion_mac'] ?? 0),
        ];
    }

    /**
     * Resuelve o registra el Trayecto correspondiente según el orden y el Tipo de Lapso (`tla_id`).
     */
    protected function resolverTrayectoId(array $u): int
    {
        $orden = isset($u['numero_orden_tra']) ? (int) $u['numero_orden_tra'] : null;
        $tlaRaw = $u['tla_id'] ?? $u['tpd_id'] ?? null;
        $tlaId = $tlaRaw
            ? (int) $tlaRaw
            : (int) (TipoLapso::where('status_id', 1)->orderBy('id_tla')->value('id_tla') ?? 1);

        if ($orden !== null) {
            $romanos = [0 => 'Inicial', 1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];
            $etiqueta = $romanos[$orden] ?? (string) $orden;
            $nombreDeseado = $orden === 0 ? 'Trayecto Inicial' : "Trayecto {$etiqueta}";

            $existente = Trayecto::where('numero_orden_tra', $orden)
                ->where('tla_id', $tlaId)
                ->where('status_id', 1)
                ->first();

            if ($existente) {
                return (int) $existente->id_tra;
            }

            $creado = Trayecto::create([
                'codigo_tra'       => "T-{$orden}",
                'nombre_tra'       => $nombreDeseado,
                'numero_orden_tra' => $orden,
                'tla_id'           => $tlaId,
                'status_id'        => 1,
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
     * Sincroniza las unidades curriculares seleccionadas dentro de `malla_curricular_detalle`.
     */
    private function sincronizarDetalles(MallaCurricular $malla, array $unidadesSeleccionadas, bool $desactivarPrevios): void
    {
        $userId = auth()->id();
        $malla->loadMissing('programa.escalaMedicion');
        $programaEsmId = $malla->programa?->esm_id ? (int) $malla->programa->esm_id : null;
        $notaDefaultEscala = $malla->programa?->escalaMedicion?->nota_aprobatoria_esm !== null
            ? (float) $malla->programa->escalaMedicion->nota_aprobatoria_esm
            : 12.00;

        if ($desactivarPrevios) {
            MallaCurricularDetalle::where('mac_id', $malla->id_mac)->update([
                'status_id'  => 2,
                'updated_id' => $userId,
            ]);
        }

        foreach ($unidadesSeleccionadas as $u) {
            if ((isset($u['incluido']) && empty($u['incluido'])) || empty($u['unc_id'])) {
                continue;
            }

            $traId = $this->resolverTrayectoId($u);
            $semestre = (int) ($u['semestre'] ?? 1);
            $ucId = (int) $u['unc_id'];

            $htea = max(0, (int) ($u['htea'] ?? 3));
            $htei = max(0, (int) ($u['htei'] ?? 3));
            $thte = (isset($u['thte']) && $u['thte'] !== '')
                ? max(0, (int) $u['thte'])
                : ($htea + $htei);

            $duracionSemestres = in_array((int) ($u['duracion_semestres'] ?? 1), [1, 2], true)
                ? (int) ($u['duracion_semestres'] ?? 1)
                : 1;
            $duracionSemanas = (isset($u['duracion_semanas']) && (int) $u['duracion_semanas'] > 0)
                ? (int) $u['duracion_semanas']
                : ($duracionSemestres * 18);

            $notaAprobatoria = (isset($u['nota_minima_aprobatoria_mcd']) && $u['nota_minima_aprobatoria_mcd'] !== '')
                ? round((float) $u['nota_minima_aprobatoria_mcd'], 2)
                : $notaDefaultEscala;

            $atributosComunes = [
                'ecu_id'                      => ! empty($u['ecu_id']) ? (int) $u['ecu_id'] : null,
                'esm_id'                      => ! empty($u['esm_id']) ? (int) $u['esm_id'] : $programaEsmId,
                'unidades_credito_mcd'        => max(0, (int) ($u['uc'] ?? 3)),
                'htea_mcd'                    => $htea,
                'htei_mcd'                    => $htei,
                'thte_mcd'                    => $thte,
                'duracion_semanas_mcd'        => $duracionSemanas,
                'duracion_semestres_mcd'      => $duracionSemestres,
                'nota_minima_aprobatoria_mcd' => $notaAprobatoria,
                'semestre_mcd'                => $semestre,
                'etapa_curricular_mcd'        => $u['etapa'] ?? 'TSU',
                'status_id'                   => 1,
                'updated_id'                  => $userId,
            ];

            $detalleExistente = MallaCurricularDetalle::withTrashed()
                ->where('mac_id', $malla->id_mac)
                ->where('unc_id', $ucId)
                ->where('tra_id', $traId)
                ->first();

            if ($detalleExistente) {
                if ($detalleExistente->trashed()) {
                    $detalleExistente->restore();
                }
                $detalleExistente->update($atributosComunes);
            } else {
                MallaCurricularDetalle::create(array_merge($atributosComunes, [
                    'mac_id'     => $malla->id_mac,
                    'unc_id'     => $ucId,
                    'tra_id'     => $traId,
                    'created_id' => $userId,
                ]));
            }
        }
    }

    /**
     * Muestra el listado de mallas curriculares activas (`status_id = 1`) con filtros reactivos y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $mallas = MallaCurricular::obtenerPaginados(
            $request->only(['search']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricular/index', [
            'mallas'  => $mallas,
            'perPage' => $perPage,
            'filters' => array_merge(
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
        return Inertia::render(
            'EstructuraAcademica/Maestros/MallaCurricular/create',
            $this->cargarCatalogosFormulario()
        );
    }

    /**
     * Almacena una nueva malla curricular y sus detalles en una transacción atómica.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validarMalla($request);

        try {
            $malla = DB::transaction(function () use ($validated) {
                $malla = MallaCurricular::create(array_merge(
                    $this->construirPayloadMalla($validated),
                    [
                        'status_id'  => 1,
                        'created_id' => auth()->id(),
                        'updated_id' => auth()->id(),
                    ]
                ));

                if (!empty($validated['unidades_seleccionadas']) && is_array($validated['unidades_seleccionadas'])) {
                    $this->sincronizarDetalles($malla, $validated['unidades_seleccionadas'], false);
                }

                return $malla;
            });

            return redirect()
                ->route('academico.malla-curricular.index')
                ->with('success', "La malla curricular '{$malla->nombre_mac}' y sus unidades curriculares han sido registradas exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la malla curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Retorna las unidades curriculares asociadas al Programa (`prg_id`) para visualización reactiva en el formulario.
     */
    public function unidadesPorPnf(int $pnfId): JsonResponse
    {
        $mallaIds = MallaCurricular::where('prg_id', $pnfId)->pluck('id_mac');

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
                $sem = $item->semestre_mcd ?? 1;
                $tra = $item->tra_id ?? 1;

                return $item->unc_id . '-' . $tra . '-' . $sem;
            });

            foreach ($detallesUnicos as $det) {
                $traId = (int) ($det->tra_id ?? 1);
                $traNombre = $det->trayecto ? $det->trayecto->nombre_tra : "Trayecto {$traId}";

                $semestreNum = (int) ($det->semestre_mcd ?? 1);
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
                $uc = (int) ($det->unidades_credito_mcd ?? 3);
                $thte = (int) ($det->thte_mcd ?? 6);
                $htea = (int) ($det->htea_mcd ?? 3);
                $htei = (int) ($det->htei_mcd ?? 3);

                if ($etapa === 'TSU') {
                    $totalCreditosTsu += $uc;
                    $totalHorasTsu += $thte;
                    $totalMateriasTsu++;
                }
                $totalCreditosIng += $uc;
                $totalHorasIng += $thte;
                $totalMateriasIng++;

                $bloques[$clave]['unidades'][] = [
                    'id_mcd'                      => $det->id_mcd,
                    'unc_id'                      => $det->unc_id,
                    'codigo'                      => $det->unidadCurricular->codigo_unc ?? 'N/A',
                    'nombre'                      => $det->unidadCurricular->nombre_unc ?? 'Sin nombre',
                    'tipo'                        => $det->unidadCurricular->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                    'tra_id'                      => $traId,
                    'ecu_id'                      => $det->ecu_id,
                    'esm_id'                      => $det->esm_id,
                    'semestre'                    => $semestreNum,
                    'etapa'                       => $etapa,
                    'uc'                          => $uc,
                    'htea'                        => $htea,
                    'htei'                        => $htei,
                    'thte'                        => $thte,
                    'duracion_semestres'          => in_array((int) ($det->duracion_semestres_mcd ?? 1), [1, 2], true) ? (int) $det->duracion_semestres_mcd : 1,
                    'duracion_semanas'            => $det->duracion_semanas_mcd ?? 12,
                    'nota_minima_aprobatoria_mcd' => $det->nota_minima_aprobatoria_mcd !== null ? (float) $det->nota_minima_aprobatoria_mcd : null,
                    'incluido'                    => true,
                ];
            }
        } else {
            $programa = Programa::find($pnfId);
            $codigoBase = $programa ? ($programa->codigo_prg ?? $programa->codigo_ministerio_prg ?? $programa->siglas_prg) : null;
            $codigoPrefix = $codigoBase ? strtoupper(substr((string) $codigoBase, 0, 3)) : null;

            $query = UnidadCurricular::with(['tipoUnidadCurricular'])->where('status_id', 1);
            if ($codigoPrefix) {
                $unidades = (clone $query)->where('codigo_unc', 'like', "{$codigoPrefix}%")->orderBy('codigo_unc')->get();
                if ($unidades->isEmpty()) {
                    $unidades = $query->orderBy('codigo_unc')->get();
                }
            } else {
                $unidades = $query->orderBy('codigo_unc')->get();
            }

            $trayectoPorDefecto = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra')->first();
            $traId = $trayectoPorDefecto ? (int) $trayectoPorDefecto->id_tra : 1;
            $traNombre = $trayectoPorDefecto ? $trayectoPorDefecto->nombre_tra : 'Trayecto 1';

            foreach ($unidades as $u) {
                $semestreNum = 1;
                $clave = "{$traNombre} - Semestre {$semestreNum}";

                if (!isset($bloques[$clave])) {
                    $bloques[$clave] = [
                        'trayecto_id'     => $traId,
                        'trayecto_nombre' => $traNombre,
                        'semestre'        => $semestreNum,
                        'unidades'        => [],
                    ];
                }

                $uc = 3;
                $htea = 3;
                $htei = 3;
                $thte = 6;

                $bloques[$clave]['unidades'][] = [
                    'id_mcd'                      => null,
                    'unc_id'                      => $u->id_unc,
                    'codigo'                      => $u->codigo_unc ?? 'N/A',
                    'nombre'                      => $u->nombre_unc ?? 'Sin nombre',
                    'tipo'                        => $u->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                    'tra_id'                      => $traId,
                    'ecu_id'                      => null,
                    'esm_id'                      => $programa?->esm_id,
                    'semestre'                    => $semestreNum,
                    'etapa'                       => 'TSU',
                    'uc'                          => $uc,
                    'htea'                        => $htea,
                    'htei'                        => $htei,
                    'thte'                        => $thte,
                    'duracion_semestres'          => 1,
                    'duracion_semanas'            => 12,
                    'nota_minima_aprobatoria_mcd' => null,
                    'incluido'                    => true,
                ];

                $totalCreditosTsu += $uc;
                $totalHorasTsu += $thte;
                $totalMateriasTsu++;
                $totalCreditosIng += $uc;
                $totalHorasIng += $thte;
                $totalMateriasIng++;
            }
        }

        return response()->json([
            'pnf_id'            => $pnfId,
            'prg_id'            => $pnfId,
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
            'programa',
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
            'programa.escalaMedicion',
            'detalles' => fn ($q) => $q->where('status_id', 1)->orderBy('tra_id')->orderBy('semestre_mcd')->orderBy('id_mcd'),
            'detalles.unidadCurricular.tipoUnidadCurricular',
            'detalles.trayecto',
            'detalles.ejeCurricular',
            'detalles.escalaMedicion',
        ])
            ->where('id_mac', $id)
            ->firstOrFail();

        return Inertia::render(
            'EstructuraAcademica/Maestros/MallaCurricular/edit',
            array_merge(['malla' => $malla], $this->cargarCatalogosFormulario())
        );
    }

    /**
     * Actualiza los datos de una malla curricular existente y sincroniza sus unidades curriculares.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $this->validarMalla($request);

        try {
            $malla = DB::transaction(function () use ($validated, $id) {
                $malla = MallaCurricular::where('id_mac', $id)->firstOrFail();

                $malla->update(array_merge(
                    $this->construirPayloadMalla($validated),
                    ['updated_id' => auth()->id()]
                ));

                if (!empty($validated['unidades_seleccionadas']) && is_array($validated['unidades_seleccionadas'])) {
                    $this->sincronizarDetalles($malla, $validated['unidades_seleccionadas'], true);
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
     * Alterna el estatus activo/inactivo de la malla curricular verificando que el programa padre esté activo.
     */
    public function alternarEstatus(Request $request, int $id): RedirectResponse
    {
        try {
            $malla = MallaCurricular::where('id_mac', $id)->firstOrFail();
            $nuevoEstatus = $malla->status_id === 1 ? 2 : 1;
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
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * preservando la malla curricular y sus detalles en PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`alternarEstatus`) por `eliminarConSoftDelete`.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $resultado = MallaCurricular::eliminarConSoftDelete($id, auth()->id());

            return redirect()
                ->back()
                ->with('success', "La malla curricular '{$resultado['nombre']}' ha sido eliminada correctamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar la malla curricular: ' . $e->getMessage()]);
        }
    }
}
