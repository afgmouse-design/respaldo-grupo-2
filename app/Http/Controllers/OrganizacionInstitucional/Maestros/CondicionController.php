<?php

namespace App\Http\Controllers\OrganizacionInstitucional\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizacionInstitucional\Condicion\StoreCondicionRequest;
use App\Http\Requests\OrganizacionInstitucional\Condicion\UpdateCondicionRequest;
use App\Models\OrganizacionInstitucional\Maestros\Condicion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Condición (Organización Institucional).
 *
 * Propósito: CRUD SPA con Inertia; crear/editar/ver son modales en index.vue. Envía a la vista los
 * tipos de condición activos para el <select>. La baja es lógica (campo `status`) vía alternarEstatus().
 * Toda acción se autoriza con CondicionPolicy.
 */
class CondicionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Condicion::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'tco_id']);

        return Inertia::render('OrganizacionInstitucional/Maestros/Condicion/index', [
            'condiciones'     => Condicion::obtenerPaginados($filtros, $perPage),
            'tiposCondicion'  => DB::table('tipo_condicion')
                ->select('id_tco', 'nombre_tco')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_tco')
                ->get(),
            'filters'         => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Condicion::class);

        return Inertia::render('OrganizacionInstitucional/Maestros/Condicion/create', [
            'tiposCondicion' => DB::table('tipo_condicion')
                ->select('id_tco', 'nombre_tco')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_tco')
                ->get(),
        ]);
    }

    public function store(StoreCondicionRequest $request): RedirectResponse
    {
        $this->authorize('create', Condicion::class);

        try {
            Condicion::create($request->validated() + ['status' => true, 'created_id' => auth()->id()]);

            return redirect()->route('condicion.index')->with('success', 'Condición creada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al crear la condición.']);
        }
    }

    public function show(int $condicion): Response
    {
        $registro = Condicion::with('tipoCondicion')->where('id_cdn', $condicion)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('OrganizacionInstitucional/Maestros/Condicion/show', [
            'condicion' => $registro,
        ]);
    }

    public function edit(int $condicion): Response
    {
        $registro = Condicion::where('id_cdn', $condicion)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('OrganizacionInstitucional/Maestros/Condicion/edit', [
            'condicion'      => $registro,
            'tiposCondicion' => DB::table('tipo_condicion')
                ->select('id_tco', 'nombre_tco')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_tco')
                ->get(),
        ]);
    }

    public function update(UpdateCondicionRequest $request, int $condicion): RedirectResponse
    {
        $registro = Condicion::where('id_cdn', $condicion)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $registro->update($request->validated() + ['updated_id' => auth()->id()]);

            return redirect()->route('condicion.index')->with('success', 'Condición actualizada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar la condición.']);
        }
    }

    /**
     * No elimina: alterna el estatus (activo ↔ inactivo) con alternarEstatus().
     */
    public function destroy(int $condicion): RedirectResponse
    {
        $registro = Condicion::where('id_cdn', $condicion)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            $nuevo = !$registro->status;
            Condicion::alternarEstatus($condicion, $nuevo);

            return redirect()->back()->with('success', $nuevo
                ? 'Condición activada correctamente.'
                : 'Condición desactivada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo cambiar el estatus de la condición.']);
        }
    }
}

