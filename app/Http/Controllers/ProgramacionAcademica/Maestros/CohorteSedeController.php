<?php

/**
 * Propósito: Controlador CRUD del Maestro Cohorte por Sede (`cohorte_sede`).
 * Historial: Actualizado para consultar `sede_programa` (`id_spr`, `status_id = 1`) y `programa` (`nombre_prg`), eliminando el uso de `sede_pnf`, `pnf` y el campo booleano `status`.
 */

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\CohorteSede\StoreCohorteSedeRequest;
use App\Http\Requests\ProgramacionAcademica\CohorteSede\UpdateCohorteSedeRequest;
use App\Models\ProgramacionAcademica\Maestros\CohorteSede;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CohorteSedeController extends Controller
{
    /**
     * Carga cohortes y ofertas Sede-Programa activas para los selectores del módulo.
     */
    private function cargarCatalogos(): array
    {
        $cohortes = DB::table('cohorte')
            ->select('id_coh', 'nombre_coh')
            ->whereNull('deleted_at')
            ->orderBy('nombre_coh', 'asc')
            ->get();

        $ofertasSedePrograma = DB::table('sede_programa')
            ->join('sede', 'sede.id_sed', '=', 'sede_programa.sed_id')
            ->join('programa', 'programa.id_prg', '=', 'sede_programa.prg_id')
            ->where('sede_programa.status_id', 1)
            ->whereNull('sede_programa.deleted_at')
            ->select(
                'sede_programa.id_spr',
                'sede_programa.id_spr as id_sep',
                DB::raw("sede.nombre_sed || ' - ' || programa.nombre_prg as descripcion_oferta")
            )
            ->orderBy('sede.nombre_sed', 'asc')
            ->orderBy('programa.nombre_prg', 'asc')
            ->get();

        return [
            'cohortes'       => $cohortes,
            'ofertasSedePnf' => $ofertasSedePrograma,
        ];
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CohorteSede::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = [
            'search' => $request->input('search'),
            'status' => $request->input('status', 'all'),
            'coh_id' => $request->input('coh_id', 'all'),
            'sep_id' => $request->input('sep_id', 'all'),
        ];

        return Inertia::render('ProgramacionAcademica/Maestros/CohorteSede/index', array_merge([
            'cohortesSede' => CohorteSede::obtenerPaginados($filtros, $perPage),
            'filters'      => $filtros,
        ], $this->cargarCatalogos()));
    }

    public function create(): Response
    {
        $this->authorize('create', CohorteSede::class);

        return Inertia::render('ProgramacionAcademica/Maestros/CohorteSede/create', $this->cargarCatalogos());
    }

    public function show(int $cohorte_sede): Response
    {
        $registro = CohorteSede::with(['cohorte', 'sedePrograma.sede', 'sedePrograma.programa'])
            ->where('id_cos', $cohorte_sede)
            ->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/CohorteSede/show', [
            'cohorteSede' => $registro,
        ]);
    }

    public function edit(int $cohorte_sede): Response
    {
        $registro = CohorteSede::where('id_cos', $cohorte_sede)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/CohorteSede/edit', array_merge([
            'cohorteSede' => $registro,
        ], $this->cargarCatalogos()));
    }

    public function store(StoreCohorteSedeRequest $request): RedirectResponse
    {
        $this->authorize('create', CohorteSede::class);

        try {
            $validated = $request->validated();

            DB::transaction(function () use ($validated) {
                $existente = CohorteSede::withTrashed()
                    ->where('coh_id', $validated['coh_id'])
                    ->where('sep_id', $validated['sep_id'])
                    ->first();

                if ($existente && $existente->trashed()) {
                    $existente->restore();
                    $existente->update(array_merge($validated, [
                        'status_id'  => CohorteSede::STATUS_ACTIVO,
                        'updated_id' => auth()->id(),
                    ]));

                    return;
                }

                CohorteSede::create($validated + [
                    'status_id'  => CohorteSede::STATUS_ACTIVO,
                    'created_id' => auth()->id(),
                    'updated_id' => auth()->id(),
                ]);
            });

            return redirect()->route('cohorte_sede.index')->with('success', 'Cohorte por sede registrada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al registrar la cohorte por sede.']);
        }
    }

    public function update(UpdateCohorteSedeRequest $request, int $cohorte_sede): RedirectResponse
    {
        $registro = CohorteSede::where('id_cos', $cohorte_sede)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $data = $request->validated();
            $data['coh_id'] = $registro->coh_id;

            $registro->update($data + ['updated_id' => auth()->id()]);

            return redirect()->route('cohorte_sede.index')->with('success', 'Cohorte por sede actualizada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar la cohorte por sede.']);
        }
    }

    /**
     * Si recibe `eliminar=1`, ejecuta baja lógica en interfaz (`deleted_at` + `status_id = 2`);
     * de lo contrario invierte únicamente el estatus lógico (`status_id` 1 <-> 2).
     *
     * Historial:
     * - 2026-11-16: Separada la eliminación lógica (`eliminar=1`) del toggle de estado.
     */
    public function destroy(Request $request, int $cohorte_sede): RedirectResponse
    {
        $registro = CohorteSede::where('id_cos', $cohorte_sede)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            if ($request->boolean('eliminar')) {
                CohorteSede::ejecutarBajaLogicaModelo($registro, auth()->id(), CohorteSede::STATUS_INACTIVO);

                return redirect()->back()->with('success', "La cohorte por sede '{$registro->nombre_cos}' ha sido eliminada correctamente.");
            }

            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            CohorteSede::alternarEstatus($cohorte_sede, $nuevoEstado);

            return redirect()->back()->with(
                'success',
                $nuevoEstado === 1
                    ? 'Cohorte por sede activada correctamente.'
                    : 'Cohorte por sede desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la solicitud sobre la cohorte por sede.']);
        }
    }
}
