<?php

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

/**
 * Controlador: Maestro de Aula (Programación Académica).
 *
 * Propósito: CRUD SPA con Inertia; crear/editar/ver son modales. Envía a la vista las sedes y los
 * tipos de aula activos para los <select>. La baja es lógica vía alternarEstatus(). Autoriza con AulaPolicy.
 */
class AulaController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Aula::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'sed_id', 'tia_id']);

        return Inertia::render('ProgramacionAcademica/Maestros/Aula/index', [
            'aulas'     => Aula::obtenerPaginados($filtros, $perPage),
            'sedes'     => DB::table('sede')
                ->select('id_sed', 'nombre_sed')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_sed')
                ->get(),
            'tiposAula' => DB::table('tipo_aula')
                ->select('id_tia', 'nombre_tia')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_tia')
                ->get(),
            'filters'   => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Aula::class);

        return Inertia::render('ProgramacionAcademica/Maestros/Aula/create', [
            'sedes' => DB::table('sede')
                ->select('id_sed', 'nombre_sed')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_sed')
                ->get(),
            'tiposAula' => DB::table('tipo_aula')
                ->select('id_tia', 'nombre_tia')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_tia')
                ->get(),
        ]);
    }

    public function store(StoreAulaRequest $request): RedirectResponse
    {
        $this->authorize('create', Aula::class);

        try {
            Aula::create($request->validated() + [
                'status'     => true,
                'status_id'  => 1,
                'created_id' => auth()->id(),
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

        return Inertia::render('ProgramacionAcademica/Maestros/Aula/edit', [
            'aula'      => $registro,
            'sedes'     => DB::table('sede')
                ->select('id_sed', 'nombre_sed')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_sed')
                ->get(),
            'tiposAula' => DB::table('tipo_aula')
                ->select('id_tia', 'nombre_tia')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_tia')
                ->get(),
        ]);
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
     * No elimina: alterna el estatus (activo ↔ inactivo) con alternarEstatus().
     */
    public function destroy(int $aula): RedirectResponse
    {
        $registro = Aula::where('id_aul', $aula)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Aula::alternarEstatus($aula, $nuevoEstado);

            return redirect()->back()->with('success', $nuevoEstado === 1
                ? 'Aula activada correctamente.'
                : 'Aula desactivada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo cambiar el estatus del aula.']);
        }
    }
}

