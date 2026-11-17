<?php

/**
 * Propósito: Controlador adaptador para el catálogo de Semestres (`semestre`) y el alias heredado `academico/tipo-periodo` sobre `tipo_lapso`.
 * Historial: Reescrito para eliminar las referencias obsoletas a `Semestres` y `TipoPeriodo` legacy, operando sobre `Semestre` (`id_sem`, `status_id`) y `Academico\TipoLapso` (`id_tla`, `status_id`).
 */

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\Academico\TipoLapso;
use App\Models\EstructuraAcademica\Maestros\Semestre;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SemestresController extends Controller
{
    /**
     * Determina si la petición proviene del prefijo heredado `academico.tipo-periodo.*`.
     */
    private function esRutaTipoPeriodo(Request $request): bool
    {
        return $request->routeIs('academico.tipo-periodo.*');
    }

    /**
     * Resuelve el status_id numérico (1 = Activo, 2 = Inactivo) desde `status_id` o `status`.
     */
    private function resolverStatusId(Request $request, ?int $default = 1): ?int
    {
        if ($request->filled('status_id')) {
            return (int) $request->input('status_id');
        }
        if ($request->has('status') && $request->input('status') !== null && $request->input('status') !== '') {
            return filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN) ? 1 : 2;
        }

        return $default;
    }

    /**
     * Formatea un registro `TipoLapso` añadiendo alias de compatibilidad para las vistas `Academico/TipoPeriodo`.
     */
    private function mapearTipoLapsoCompatible(object $tl): array
    {
        $base = $tl instanceof TipoLapso ? $tl->toArray() : (array) $tl;
        $statusId = (int) ($tl->status_id ?? 1);

        return array_merge($base, [
            'id_tpd' => $tl->id_tla,
            'codigo_tpd' => $tl->codigo_tla,
            'nombre_tpd' => $tl->nombre_tla,
            'duracion_semanas_tpd' => $tl->duracion_semanas_tla,
            'nomenclatura' => $tl->nomenclatura_tla ?? null,
            'descripcion_tpd' => $tl->descripcion_tla ?? null,
            'est_tpd' => $statusId === 1,
            'status' => $statusId === 1,
        ]);
    }

    /**
     * Normaliza y valida la entrada para `tipo_lapso` en las rutas `academico.tipo-periodo.*`.
     */
    private function validarTipoLapso(Request $request, ?int $ignoreId = null): array
    {
        $payload = [
            'codigo_tla' => $request->input('codigo_tla', $request->input('codigo_tpd')),
            'nombre_tla' => $request->input('nombre_tla', $request->input('nombre_tpd')),
            'duracion_semanas_tla' => $request->input('duracion_semanas_tla', $request->input('duracion_semanas_tpd', 12)),
            'nomenclatura_tla' => $request->input('nomenclatura_tla', $request->input('nomenclatura')),
            'descripcion_tla' => $request->input('descripcion_tla', $request->input('descripcion_tpd')),
            'status_id' => $this->resolverStatusId($request, $ignoreId ? null : 1),
        ];

        $uniqueCodigo = Rule::unique('tipo_lapso', 'codigo_tla')->whereNull('deleted_at');
        $uniqueNombre = Rule::unique('tipo_lapso', 'nombre_tla')->whereNull('deleted_at');
        if ($ignoreId !== null) {
            $uniqueCodigo->ignore($ignoreId, 'id_tla');
            $uniqueNombre->ignore($ignoreId, 'id_tla');
        }

        return Validator::make($payload, [
            'codigo_tla' => ['required', 'string', 'max:20', $uniqueCodigo],
            'nombre_tla' => ['required', 'string', 'min:2', 'max:100', $uniqueNombre],
            'duracion_semanas_tla' => ['required', 'integer', 'min:1', 'max:104'],
            'nomenclatura_tla' => ['nullable', 'string', 'max:50'],
            'descripcion_tla' => ['nullable', 'string', 'max:500'],
            'status_id' => [$ignoreId ? 'sometimes' : 'nullable', 'integer', 'exists:status,id_sta'],
        ])->validate();
    }

    /**
     * Normaliza y valida la entrada para `semestre` en las rutas `semestres.*`.
     */
    private function validarSemestre(Request $request, ?int $ignoreId = null): array
    {
        $payload = [
            'tra_id' => $request->input('tra_id'),
            'codigo_sem' => $request->input('codigo_sem', $request->input('codigo')),
            'nombre_sem' => $request->input('nombre_sem', $request->input('nombre')),
            'numero_sem' => $request->input('numero_sem', $request->input('numero')),
            'status_id' => $this->resolverStatusId($request, $ignoreId ? null : 1),
        ];

        $uniqueCodigo = Rule::unique('semestre', 'codigo_sem')->whereNull('deleted_at');
        $uniqueNombre = Rule::unique('semestre', 'nombre_sem')->whereNull('deleted_at');
        if ($ignoreId !== null) {
            $uniqueCodigo->ignore($ignoreId, 'id_sem');
            $uniqueNombre->ignore($ignoreId, 'id_sem');
        }

        return Validator::make($payload, [
            'tra_id' => ['required', 'integer', Rule::exists('trayecto', 'id_tra')->whereNull('deleted_at')],
            'codigo_sem' => ['required', 'string', 'max:20', $uniqueCodigo],
            'nombre_sem' => ['required', 'string', 'min:2', 'max:50', $uniqueNombre],
            'numero_sem' => ['required', 'integer', 'min:1', 'max:99'],
            'status_id' => [$ignoreId ? 'sometimes' : 'nullable', 'integer', 'exists:status,id_sta'],
        ])->validate();
    }

    /**
     * Lista semestres (`semestres.index`) o tipos de lapso (`academico.tipo-periodo.index`).
     */
    public function index(Request $request): Response
    {
        $filtros = $request->only(['search', 'status', 'status_id', 'fecha_inicio', 'fecha_fin', 'tra_id', 'per_page']);
        $perPage = (int) ($filtros['per_page'] ?? 10);

        if ($this->esRutaTipoPeriodo($request)) {
            $tipos = TipoLapso::obtenerPaginados($filtros, $perPage);
            $tipos->getCollection()->transform(fn (object $tl) => $this->mapearTipoLapsoCompatible($tl));

            return Inertia::render('Academico/TipoPeriodo/index', [
                'tiposPeriodo' => $tipos,
                'records' => $tipos,
                'filters' => $filtros,
            ]);
        }

        $semestres = Semestre::query()
            ->with('trayecto:id_tra,codigo_tra,nombre_tra')
            ->when($filtros['search'] ?? null, function ($q, string $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('codigo_sem', 'ilike', "%{$search}%")
                        ->orWhere('nombre_sem', 'ilike', "%{$search}%");
                });
            })
            ->when($filtros['tra_id'] ?? null, fn ($q, $traId) => $q->where('tra_id', $traId))
            ->when(isset($filtros['status_id']) && $filtros['status_id'] !== '', fn ($q) => $q->where('status_id', (int) $filtros['status_id']))
            ->when(!isset($filtros['status_id']) && isset($filtros['status']) && $filtros['status'] !== '' && $filtros['status'] !== 'all', function ($q) use ($filtros) {
                $statusId = in_array($filtros['status'], ['1', 'true', 'activo', true, 1], true) ? 1 : 2;
                $q->where('status_id', $statusId);
            })
            ->when($filtros['fecha_inicio'] ?? null, fn ($q, string $inicio) => $q->whereDate('created_at', '>=', $inicio))
            ->when($filtros['fecha_fin'] ?? null, fn ($q, string $fin) => $q->whereDate('created_at', '<=', $fin))
            ->orderBy('numero_sem')
            ->orderBy('id_sem', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('EstructuraAcademica/Maestros/Semestres/index', [
            'semestres' => $semestres,
            'records' => $semestres,
            'trayectos' => Trayecto::obtenerActivosParaSelect(),
            'filters' => $filtros,
        ]);
    }

    /**
     * Muestra el formulario de creación correspondiente al prefijo de ruta.
     */
    public function create(Request $request): Response
    {
        if ($this->esRutaTipoPeriodo($request)) {
            return Inertia::render('Academico/TipoPeriodo/create');
        }

        return Inertia::render('EstructuraAcademica/Maestros/Semestres/create', [
            'trayectos' => Trayecto::obtenerActivosParaSelect(),
        ]);
    }

    /**
     * Registra un nuevo tipo de lapso o semestre según el grupo de rutas invocado.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($this->esRutaTipoPeriodo($request)) {
            $datos = $this->validarTipoLapso($request);
            try {
                DB::transaction(function () use ($datos) {
                    TipoLapso::liberarCodigoSiEliminado($datos['codigo_tla'] ?? null);
                    TipoLapso::create(array_merge($datos, [
                        'status_id' => $datos['status_id'] ?? 1,
                        'created_id' => Auth::id(),
                        'updated_id' => Auth::id(),
                    ]));
                });

                return redirect()->route('academico.tipo-periodo.index')
                    ->with('success', 'Tipo de período creado exitosamente.');
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()
                    ->withErrors(['error' => 'Error al crear el tipo de período: ' . $e->getMessage()]);
            }
        }

        $datos = $this->validarSemestre($request);
        try {
            Semestre::create(array_merge($datos, [
                'status_id' => $datos['status_id'] ?? 1,
                'created_id' => Auth::id(),
                'updated_id' => Auth::id(),
            ]));

            return redirect()->route('semestres.index')
                ->with('success', 'Semestre registrado exitosamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Error al crear el semestre: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el detalle del registro según la ruta activa.
     */
    public function show(Request $request, int $id): Response
    {
        if ($this->esRutaTipoPeriodo($request)) {
            $tipo = TipoLapso::findOrFail($id);

            return Inertia::render('Academico/TipoPeriodo/show', [
                'tipoPeriodo' => $this->mapearTipoLapsoCompatible($tipo),
            ]);
        }

        $semestre = Semestre::with('trayecto')->findOrFail($id);

        return Inertia::render('EstructuraAcademica/Maestros/Semestres/show', [
            'semestre' => $semestre,
        ]);
    }

    /**
     * Muestra el formulario de edición según la ruta activa.
     */
    public function edit(Request $request, int $id): Response
    {
        if ($this->esRutaTipoPeriodo($request)) {
            $tipo = TipoLapso::findOrFail($id);

            return Inertia::render('Academico/TipoPeriodo/edit', [
                'tipoPeriodo' => $this->mapearTipoLapsoCompatible($tipo),
            ]);
        }

        $semestre = Semestre::findOrFail($id);

        return Inertia::render('EstructuraAcademica/Maestros/Semestres/edit', [
            'semestre' => $semestre,
            'trayectos' => Trayecto::obtenerActivosParaSelect(),
        ]);
    }

    /**
     * Actualiza un tipo de lapso o semestre existente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        if ($this->esRutaTipoPeriodo($request)) {
            $tipo = TipoLapso::findOrFail($id);
            $datos = $this->validarTipoLapso($request, $tipo->id_tla);

            try {
                if (!isset($datos['status_id']) || $datos['status_id'] === null) {
                    unset($datos['status_id']);
                }
                DB::transaction(function () use ($tipo, $datos) {
                    TipoLapso::liberarCodigoSiEliminado($datos['codigo_tla'] ?? null, $tipo->id_tla);
                    $tipo->update(array_merge($datos, ['updated_id' => Auth::id()]));
                });

                return redirect()->route('academico.tipo-periodo.index')
                    ->with('success', 'Tipo de período actualizado exitosamente.');
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()
                    ->withErrors(['error' => 'Error al actualizar el tipo de período: ' . $e->getMessage()]);
            }
        }

        $semestre = Semestre::findOrFail($id);
        $datos = $this->validarSemestre($request, $semestre->id_sem);

        try {
            if (!isset($datos['status_id']) || $datos['status_id'] === null) {
                unset($datos['status_id']);
            }
            $semestre->update(array_merge($datos, ['updated_id' => Auth::id()]));

            return redirect()->route('semestres.index')
                ->with('success', 'Semestre actualizado exitosamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Error al actualizar el semestre: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna únicamente el estatus lógico (`status_id` 1 <-> 2) para `StatusSwitch`.
     */
    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        try {
            if ($this->esRutaTipoPeriodo($request)) {
                $tipo = TipoLapso::findOrFail($id);
                $nuevoStatusId = $tipo->status_id === 1 ? 2 : 1;
                $tipo->update([
                    'status_id' => $nuevoStatusId,
                    'updated_id' => Auth::id(),
                ]);

                return redirect()->back()->with(
                    'success',
                    $nuevoStatusId === 1 ? 'Tipo de período activado.' : 'Tipo de período desactivado.'
                );
            }

            $semestre = Semestre::findOrFail($id);
            $nuevoStatusId = $semestre->status_id === 1 ? 2 : 1;
            $semestre->update([
                'status_id' => $nuevoStatusId,
                'updated_id' => Auth::id(),
            ]);

            return redirect()->back()->with(
                'success',
                $nuevoStatusId === 1 ? 'Semestre activado exitosamente.' : 'Semestre desactivado exitosamente.'
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Error al cambiar el estado: ' . $e->getMessage()]);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * conservando el registro histórico en PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Desacoplado de `toggleStatus()` para realizar baja lógica real en interfaz.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        try {
            if ($this->esRutaTipoPeriodo($request)) {
                $tipo = TipoLapso::findOrFail($id);
                TipoLapso::ejecutarBajaLogicaModelo($tipo, Auth::id(), TipoLapso::STATUS_INACTIVO);

                return redirect()->back()->with('success', "El tipo de período '{$tipo->nombre_tla}' ha sido eliminado exitosamente.");
            }

            $semestre = Semestre::findOrFail($id);
            Semestre::ejecutarBajaLogicaModelo($semestre, Auth::id(), Semestre::STATUS_INACTIVO);

            return redirect()->back()->with('success', "El semestre '{$semestre->nombre_sem}' ha sido eliminado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Error al eliminar el registro: ' . $e->getMessage()]);
        }
    }
}
