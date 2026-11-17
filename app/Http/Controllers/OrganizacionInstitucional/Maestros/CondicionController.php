<?php

namespace App\Http\Controllers\OrganizacionInstitucional\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizacionInstitucional\Condicion\StoreCondicionSedeRequest;
use App\Http\Requests\OrganizacionInstitucional\Condicion\UpdateCondicionSedeRequest;
use App\Models\OrganizacionInstitucional\Maestros\CondicionSede;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Condición de Sede (`condicion_sede`).
 *
 * Propósito: CRUD SPA con Inertia; crear/editar/ver son modales en index.vue sobre
 * `condicion_sede(id_cse)`. La baja es lógica (`status_id`: 1 = Activo, 2 = Inactivo).
 *
 * Historial:
 *  - 2026-11-14: Migrado del modelo legacy `Condicion` (`condicion`/`id_cdn`/`status`)
 *    a `CondicionSede` (`condicion_sede`/`id_cse`/`status_id`), eliminando la consulta
 *    obsoleta a `tipo_condicion` inexistente en esa tabla.
 */
class CondicionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CondicionSede::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'status_id']);

        return Inertia::render('OrganizacionInstitucional/Maestros/Condicion/index', [
            'condiciones' => CondicionSede::obtenerPaginados($filtros, $perPage),
            'filters'     => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', CondicionSede::class);

        return Inertia::render('OrganizacionInstitucional/Maestros/Condicion/create');
    }

    public function store(StoreCondicionSedeRequest $request): RedirectResponse
    {
        $this->authorize('create', CondicionSede::class);

        try {
            CondicionSede::create($request->validated() + [
                'status_id'  => CondicionSede::STATUS_ACTIVO,
                'created_id' => auth()->id(),
            ]);

            return redirect()->route('condicion.index')->with('success', 'Condición de sede creada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al crear la condición de sede.']);
        }
    }

    public function show(int $condicion): Response
    {
        $registro = CondicionSede::where('id_cse', $condicion)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('OrganizacionInstitucional/Maestros/Condicion/show', [
            'condicion' => $registro,
        ]);
    }

    public function edit(int $condicion): Response
    {
        $registro = CondicionSede::where('id_cse', $condicion)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('OrganizacionInstitucional/Maestros/Condicion/edit', [
            'condicion' => $registro,
        ]);
    }

    public function update(UpdateCondicionSedeRequest $request, int $condicion): RedirectResponse
    {
        $registro = CondicionSede::where('id_cse', $condicion)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $registro->update($request->validated() + ['updated_id' => auth()->id()]);

            return redirect()->route('condicion.index')->with('success', 'Condición de sede actualizada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar la condición de sede.']);
        }
    }

    /**
     * Si recibe `eliminar=1` (desde DataActionsDropdown), ejecuta baja lógica real (`deleted_at` + `status_id = 2`);
     * de lo contrario alterna únicamente el estatus lógico (`status_id`: 1 ↔ 2) para `StatusSwitch`.
     *
     * Historial:
     * - 2026-11-16: Separada la eliminación lógica (`eliminar=1`) del toggle de estado.
     */
    public function destroy(Request $request, int $condicion): RedirectResponse
    {
        $registro = CondicionSede::where('id_cse', $condicion)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            if ($request->boolean('eliminar')) {
                CondicionSede::ejecutarBajaLogicaModelo($registro, auth()->id(), CondicionSede::STATUS_INACTIVO);

                return redirect()->back()->with('success', "La condición de sede '{$registro->nombre_cse}' ha sido eliminada correctamente.");
            }

            $activar = ! CondicionSede::esStatusActivo($registro);
            CondicionSede::alternarEstatus($condicion, $activar, auth()->id());

            return redirect()->back()->with('success', $activar
                ? 'Condición de sede activada correctamente.'
                : 'Condición de sede desactivada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la acción sobre la condición de sede.']);
        }
    }
}

