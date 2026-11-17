<?php

/**
 * Propósito: Controlador CRUD del Maestro Sede-Programa (oferta académica por sede sobre `sede_programa`).
 * Historial: Actualizado para operar sobre `SedePrograma` (`id_spr`, `prg_id`, `status_id`), `programa`, `StoreSedeProgramaRequest`, `UpdateSedeProgramaRequest` y `SedeProgramaPolicy`.
 */

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\SedePrograma\StoreSedeProgramaRequest;
use App\Http\Requests\ProgramacionAcademica\SedePrograma\UpdateSedeProgramaRequest;
use App\Models\ProgramacionAcademica\Maestros\SedePrograma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SedePnfController extends Controller
{
    /**
     * Carga los catálogos activos de Sedes y Programas requeridos por el listado y modales.
     */
    private function cargarCatalogos(): array
    {
        $sedes = DB::table('sede')
            ->select('id_sed', 'nombre_sed')
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->orderBy('nombre_sed')
            ->get();

        $programas = DB::table('programa')
            ->select('id_prg', 'nombre_prg', 'codigo_prg', 'codigo_ministerio_prg')
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->orderBy('nombre_prg')
            ->get();

        return [
            'sedes'     => $sedes,
            'programas' => $programas,
            'pnfs'      => $programas,
        ];
    }

    /**
     * Listado paginado con filtros (`search`, `status`, `sed_id`, `prg_id`).
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SedePrograma::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'status_id', 'sed_id', 'prg_id', 'pnf_id']);
        if (empty($filtros['prg_id']) && !empty($filtros['pnf_id'])) {
            $filtros['prg_id'] = $filtros['pnf_id'];
        }

        return Inertia::render('ProgramacionAcademica/Maestros/SedePnf/index', array_merge([
            'sedesPnf'       => SedePrograma::obtenerPaginados($filtros, $perPage),
            'sedesProgramas' => SedePrograma::obtenerPaginados($filtros, $perPage),
            'filters'        => $filtros,
        ], $this->cargarCatalogos()));
    }

    public function create(): Response
    {
        $this->authorize('create', SedePrograma::class);

        return Inertia::render('ProgramacionAcademica/Maestros/SedePnf/create', $this->cargarCatalogos());
    }

    public function show(int $sede_pnf): Response
    {
        $registro = SedePrograma::with(['sede', 'programa'])->where('id_spr', $sede_pnf)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/SedePnf/show', [
            'sedePnf'      => $registro,
            'sedePrograma' => $registro,
        ]);
    }

    public function edit(int $sede_pnf): Response
    {
        $registro = SedePrograma::where('id_spr', $sede_pnf)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/SedePnf/edit', array_merge([
            'sedePnf'      => $registro,
            'sedePrograma' => $registro,
        ], $this->cargarCatalogos()));
    }

    /**
     * Registra una nueva oferta Sede-Programa.
     */
    public function store(StoreSedeProgramaRequest $request): RedirectResponse
    {
        $this->authorize('create', SedePrograma::class);

        try {
            $validated = $request->validated();

            DB::transaction(function () use ($validated, $request) {
                $existente = SedePrograma::withTrashed()
                    ->where('sed_id', $validated['sed_id'])
                    ->where('prg_id', $validated['prg_id'])
                    ->first();

                $cantidad = $request->input('cantidad_asignados_spr', $validated['cantidad_asignados_spr'] ?? 0);

                if ($existente && $existente->trashed()) {
                    $existente->restore();
                    $existente->update(array_merge($validated, [
                        'cantidad_asignados_spr' => $cantidad,
                        'status_id'              => SedePrograma::STATUS_ACTIVO,
                        'updated_id'             => auth()->id(),
                    ]));

                    return;
                }

                SedePrograma::create($validated + [
                    'cantidad_asignados_spr' => $cantidad,
                    'status_id'              => SedePrograma::STATUS_ACTIVO,
                    'created_id'             => auth()->id(),
                    'updated_id'             => auth()->id(),
                ]);
            });

            return redirect()->route('sede_pnf.index')->with('success', 'Oferta Sede-Programa registrada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al registrar la oferta Sede-Programa.']);
        }
    }

    /**
     * Actualiza la sede, programa y/o metadata ministerial de una oferta existente.
     */
    public function update(UpdateSedeProgramaRequest $request, int $sede_pnf): RedirectResponse
    {
        $registro = SedePrograma::where('id_spr', $sede_pnf)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $registro->update($request->validated() + ['updated_id' => auth()->id()]);

            return redirect()->route('sede_pnf.index')->with('success', 'Oferta Sede-Programa actualizada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar la oferta Sede-Programa.']);
        }
    }

    /**
     * Si recibe `eliminar=1`, ejecuta baja lógica en interfaz (`deleted_at` + `status_id = 2`);
     * de lo contrario invierte únicamente el estatus lógico (`status_id` 1 <-> 2).
     *
     * Historial:
     * - 2026-11-16: Separada la eliminación lógica (`eliminar=1`) del toggle de estado.
     */
    public function destroy(Request $request, int $sede_pnf): RedirectResponse
    {
        $registro = SedePrograma::where('id_spr', $sede_pnf)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            if ($request->boolean('eliminar')) {
                SedePrograma::ejecutarBajaLogicaModelo($registro, auth()->id(), SedePrograma::STATUS_INACTIVO);

                return redirect()->back()->with('success', 'Oferta Sede-Programa eliminada correctamente.');
            }

            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            SedePrograma::alternarEstatus($sede_pnf, $nuevoEstado);

            return redirect()->back()->with(
                'success',
                $nuevoEstado === 1
                    ? 'Oferta Sede-Programa activada correctamente.'
                    : 'Oferta Sede-Programa desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la solicitud sobre la oferta Sede-Programa.']);
        }
    }
}
