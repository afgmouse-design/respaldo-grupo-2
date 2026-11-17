<?php

/**
 * Propósito: Controlador CRUD del Maestro Tipo de Aula (`tipo_aula`).
 * Historial: Actualizado para persistir `status_id` sin el campo booleano `status`.
 */

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\TipoAula\StoreTipoAulaRequest;
use App\Http\Requests\ProgramacionAcademica\TipoAula\UpdateTipoAulaRequest;
use App\Models\ProgramacionAcademica\Maestros\TipoAula;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoAulaController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', TipoAula::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);

        return Inertia::render('ProgramacionAcademica/Maestros/TipoAula/index', [
            'tiposAula' => TipoAula::obtenerPaginados($request->only(['search', 'status', 'status_id']), $perPage),
            'filters'   => $request->only(['search', 'status', 'status_id']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', TipoAula::class);

        return Inertia::render('ProgramacionAcademica/Maestros/TipoAula/create');
    }

    public function store(StoreTipoAulaRequest $request): RedirectResponse
    {
        $this->authorize('create', TipoAula::class);

        try {
            TipoAula::create($request->validated() + [
                'status_id'  => 1,
                'created_id' => auth()->id(),
                'updated_id' => auth()->id(),
            ]);

            return redirect()->route('tipo_aula.index')->with('success', 'Tipo de aula creado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al crear el tipo de aula.']);
        }
    }

    public function show(int $tipo_aula): Response
    {
        $tipoAula = TipoAula::where('id_tia', $tipo_aula)->firstOrFail();
        $this->authorize('view', $tipoAula);

        return Inertia::render('ProgramacionAcademica/Maestros/TipoAula/show', [
            'tipoAula' => $tipoAula,
        ]);
    }

    public function edit(int $tipo_aula): Response
    {
        $tipoAula = TipoAula::where('id_tia', $tipo_aula)->firstOrFail();
        $this->authorize('update', $tipoAula);

        return Inertia::render('ProgramacionAcademica/Maestros/TipoAula/edit', [
            'tipoAula' => $tipoAula,
        ]);
    }

    public function update(UpdateTipoAulaRequest $request, int $tipo_aula): RedirectResponse
    {
        $tipoAula = TipoAula::where('id_tia', $tipo_aula)->firstOrFail();
        $this->authorize('update', $tipoAula);

        try {
            $tipoAula->update($request->validated() + ['updated_id' => auth()->id()]);

            return redirect()->route('tipo_aula.index')->with('success', 'Tipo de aula actualizado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar el tipo de aula.']);
        }
    }

    /**
     * Si recibe `eliminar=1`, ejecuta baja lógica en interfaz (`deleted_at` + `status_id = 2`);
     * de lo contrario invierte únicamente el estatus lógico (`status_id` 1 <-> 2).
     *
     * Historial:
     * - 2026-11-16: Separada la eliminación lógica (`eliminar=1`) del toggle de estado.
     */
    public function destroy(Request $request, int $tipo_aula): RedirectResponse
    {
        $tipoAula = TipoAula::where('id_tia', $tipo_aula)->firstOrFail();
        $this->authorize('delete', $tipoAula);

        try {
            if ($request->boolean('eliminar')) {
                TipoAula::ejecutarBajaLogicaModelo($tipoAula, auth()->id(), TipoAula::STATUS_INACTIVO);

                return redirect()->back()->with('success', "El tipo de aula '{$tipoAula->nombre_tia}' ha sido eliminado correctamente.");
            }

            $nuevoEstado = $tipoAula->status_id === 1 ? 2 : 1;
            TipoAula::alternarEstatus($tipo_aula, $nuevoEstado);

            return redirect()->back()->with(
                'success',
                $nuevoEstado === 1
                    ? 'Tipo de aula activado correctamente.'
                    : 'Tipo de aula desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la acción sobre el tipo de aula.']);
        }
    }
}
