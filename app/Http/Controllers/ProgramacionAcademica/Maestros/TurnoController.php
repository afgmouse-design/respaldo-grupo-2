<?php

/**
 * Propósito: Controlador CRUD del Maestro Turno académico (`turno`).
 * Historial: Actualizado para operar exclusivamente sobre columnas oficiales (`codigo_tur`, `nombre_tur`, `hora_inicio_tur`, `hora_fin_tur`, `descripcion_tur`, `status_id`), sin usar las columnas legacy duplicadas.
 */

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\ProgramacionAcademica\Maestros\Turno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TurnoController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);

        return Inertia::render('ProgramacionAcademica/Maestros/Turno/index', [
            'turnos'  => Turno::obtenerPaginados($request->only(['search', 'status', 'status_id']), $perPage),
            'filters' => $request->only(['search', 'status', 'status_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ProgramacionAcademica/Maestros/Turno/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_tur'      => ['required', 'string', 'max:20', Rule::unique('turno', 'codigo_tur')->whereNull('deleted_at')],
            'nombre_tur'      => ['required', 'string', 'max:100', Rule::unique('turno', 'nombre_tur')->whereNull('deleted_at')],
            'hora_inicio_tur' => ['nullable', 'string'],
            'hora_fin_tur'    => ['nullable', 'string'],
            'descripcion_tur' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Turno::liberarValoresUnicosSiEliminados($validated['codigo_tur'], $validated['nombre_tur']);
                Turno::create($validated + [
                    'status_id'  => 1,
                    'created_id' => auth()->id(),
                    'updated_id' => auth()->id(),
                ]);
            });

            return redirect()->route('turno.index')->with('success', 'Turno registrado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al registrar el turno.']);
        }
    }

    public function show(int $turno): Response
    {
        $registro = Turno::where('id_tur', $turno)->firstOrFail();

        return Inertia::render('ProgramacionAcademica/Maestros/Turno/show', [
            'turno' => $registro,
        ]);
    }

    public function edit(int $turno): Response
    {
        $registro = Turno::where('id_tur', $turno)->firstOrFail();

        return Inertia::render('ProgramacionAcademica/Maestros/Turno/edit', [
            'turno' => $registro,
        ]);
    }

    public function update(Request $request, int $turno): RedirectResponse
    {
        $registro = Turno::where('id_tur', $turno)->firstOrFail();

        $validated = $request->validate([
            'codigo_tur'      => ['required', 'string', 'max:20', Rule::unique('turno', 'codigo_tur')->ignore($turno, 'id_tur')->whereNull('deleted_at')],
            'nombre_tur'      => ['required', 'string', 'max:100', Rule::unique('turno', 'nombre_tur')->ignore($turno, 'id_tur')->whereNull('deleted_at')],
            'hora_inicio_tur' => ['nullable', 'string'],
            'hora_fin_tur'    => ['nullable', 'string'],
            'descripcion_tur' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($registro, $validated, $turno) {
                Turno::liberarValoresUnicosSiEliminados($validated['codigo_tur'], $validated['nombre_tur'], $turno);
                $registro->update($validated + ['updated_id' => auth()->id()]);
            });

            return redirect()->route('turno.index')->with('success', 'Turno actualizado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar el turno.']);
        }
    }

    public function destroy(Request $request, int $turno): RedirectResponse
    {
        $registro = Turno::where('id_tur', $turno)->firstOrFail();

        try {
            if ($request->boolean('eliminar')) {
                $registro->update([
                    'status_id'  => 2,
                    'updated_id' => auth()->id(),
                ]);
                $registro->delete();

                return redirect()->back()->with('success', 'Turno eliminado correctamente.');
            }

            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Turno::alternarEstatus($turno, $nuevoEstado);

            return redirect()->back()->with(
                'success',
                $nuevoEstado === 1
                    ? 'Turno activado correctamente.'
                    : 'Turno desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la solicitud sobre el turno.']);
        }
    }
}
