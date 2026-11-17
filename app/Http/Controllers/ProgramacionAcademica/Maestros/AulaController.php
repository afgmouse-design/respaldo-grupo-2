<?php

/**
 * Propósito: Controlador CRUD del Maestro Aula física (`aula`).
 * Historial: Actualizado para filtrar `sede` y `tipo_aula` por `status_id = 1` y persistir únicamente `status_id` (sin `status` booleano).
 */

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Aula\StoreAulaRequest;
use App\Http\Requests\ProgramacionAcademica\Aula\UpdateAulaRequest;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AulaController extends Controller
{
    /**
     * Carga sedes y tipos de aula activos (`status_id = 1`) para formularios y filtros.
     */
    private function cargarCatalogos(): array
    {
        return [
            'sedes' => DB::table('sede')
                ->select('id_sed', 'nombre_sed')
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->orderBy('nombre_sed')
                ->get(),
            'tiposAula' => DB::table('tipo_aula')
                ->select('id_tia', 'nombre_tia')
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->orderBy('nombre_tia')
                ->get(),
        ];
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Aula::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'status_id', 'sed_id', 'tia_id']);

        return Inertia::render('ProgramacionAcademica/Maestros/Aula/index', array_merge([
            'aulas'   => Aula::obtenerPaginados($filtros, $perPage),
            'filters' => $filtros,
        ], $this->cargarCatalogos()));
    }

    public function create(): Response
    {
        $this->authorize('create', Aula::class);

        return Inertia::render('ProgramacionAcademica/Maestros/Aula/create', $this->cargarCatalogos());
    }

    public function store(StoreAulaRequest $request): RedirectResponse
    {
        $this->authorize('create', Aula::class);

        try {
            Aula::create($request->validated() + [
                'status_id'  => 1,
                'created_id' => auth()->id(),
                'updated_id' => auth()->id(),
            ]);

            return redirect()->route('aula.index')->with('success', 'Aula creada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al crear el aula.']);
        }
    }

    public function show(int $aula): Response
    {
        $registro = Aula::with(['sede', 'tipoAula'])->where('id_aul', $aula)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Aula/show', [
            'aula' => $registro,
        ]);
    }

    public function edit(int $aula): Response
    {
        $registro = Aula::where('id_aul', $aula)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Aula/edit', array_merge([
            'aula' => $registro,
        ], $this->cargarCatalogos()));
    }

    public function update(UpdateAulaRequest $request, int $aula): RedirectResponse
    {
        $registro = Aula::where('id_aul', $aula)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $registro->update($request->validated() + ['updated_id' => auth()->id()]);

            return redirect()->route('aula.index')->with('success', 'Aula actualizada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar el aula.']);
        }
    }

    /**
     * Si recibe `eliminar=1`, ejecuta baja lógica en interfaz (`deleted_at` + `status_id = 2`);
     * de lo contrario invierte únicamente el estatus lógico (`status_id` 1 <-> 2).
     *
     * Historial:
     * - 2026-11-16: Separada la eliminación lógica (`eliminar=1`) del toggle de estado.
     */
    public function destroy(Request $request, int $aula): RedirectResponse
    {
        $registro = Aula::where('id_aul', $aula)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            if ($request->boolean('eliminar')) {
                Aula::ejecutarBajaLogicaModelo($registro, auth()->id(), Aula::STATUS_INACTIVO);

                return redirect()->back()->with('success', "El aula '{$registro->codigo_aula_aul}' ha sido eliminada correctamente.");
            }

            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Aula::alternarEstatus($aula, $nuevoEstado);

            return redirect()->back()->with(
                'success',
                $nuevoEstado === 1
                    ? 'Aula activada correctamente.'
                    : 'Aula desactivada correctamente.'
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la acción sobre el aula.']);
        }
    }
}
