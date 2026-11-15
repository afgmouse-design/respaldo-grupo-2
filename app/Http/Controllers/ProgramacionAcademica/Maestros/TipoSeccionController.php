<?php

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\ProgramacionAcademica\Maestros\TipoSeccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Tipo de Sección (Programación Académica).
 *
 * Propósito: CRUD completo sobre la tabla PostgreSQL `tipo_seccion`
 * con vistas standalone (`index`, `create`, `edit`, `show`) y baja lógica.
 */
class TipoSeccionController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);

        return Inertia::render('ProgramacionAcademica/Maestros/TipoSeccion/index', [
            'tiposSeccion' => TipoSeccion::obtenerPaginados($request->only(['search', 'status']), $perPage),
            'filters'      => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ProgramacionAcademica/Maestros/TipoSeccion/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_tis'      => ['required', 'string', 'max:20', 'unique:tipo_seccion,codigo_tis'],
            'nombre_tis'      => ['required', 'string', 'max:100'],
            'descripcion_tis' => ['nullable', 'string'],
        ]);

        try {
            TipoSeccion::create($validated + [
                'status'     => true,
                'status_id'  => 1,
                'created_id' => auth()->id(),
            ]);

            return redirect()->route('tipo_seccion.index')->with('success', 'Tipo de sección creado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al crear el tipo de sección.']);
        }
    }

    public function show(int $tipo_seccion): Response
    {
        $tipoSeccion = TipoSeccion::where('id_tis', $tipo_seccion)->firstOrFail();

        return Inertia::render('ProgramacionAcademica/Maestros/TipoSeccion/show', [
            'tipoSeccion' => $tipoSeccion,
        ]);
    }

    public function edit(int $tipo_seccion): Response
    {
        $tipoSeccion = TipoSeccion::where('id_tis', $tipo_seccion)->firstOrFail();

        return Inertia::render('ProgramacionAcademica/Maestros/TipoSeccion/edit', [
            'tipoSeccion' => $tipoSeccion,
        ]);
    }

    public function update(Request $request, int $tipo_seccion): RedirectResponse
    {
        $tipoSeccion = TipoSeccion::where('id_tis', $tipo_seccion)->firstOrFail();

        $validated = $request->validate([
            'codigo_tis'      => ['required', 'string', 'max:20', Rule::unique('tipo_seccion', 'codigo_tis')->ignore($tipo_seccion, 'id_tis')],
            'nombre_tis'      => ['required', 'string', 'max:100'],
            'descripcion_tis' => ['nullable', 'string'],
        ]);

        try {
            $tipoSeccion->update($validated + ['updated_id' => auth()->id()]);

            return redirect()->route('tipo_seccion.index')->with('success', 'Tipo de sección actualizado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar el tipo de sección.']);
        }
    }

    public function destroy(Request $request, int $tipo_seccion): RedirectResponse
    {
        $tipoSeccion = TipoSeccion::where('id_tis', $tipo_seccion)->firstOrFail();

        try {
            if ($request->boolean('eliminar')) {
                \Illuminate\Support\Facades\DB::table('tipo_seccion')
                    ->where('id_tis', $tipo_seccion)
                    ->update([
                        'status'     => false,
                        'status_id'  => 2,
                        'deleted_at' => now(),
                        'updated_id' => auth()->id(),
                    ]);

                return redirect()->back()->with('success', 'Tipo de sección eliminado correctamente.');
            }

            $nuevoEstado = $tipoSeccion->status_id === 1 ? 2 : 1;
            TipoSeccion::alternarEstatus($tipo_seccion, $nuevoEstado);

            return redirect()->back()->with('success', $nuevoEstado === 1
                ? 'Tipo de sección activado correctamente.'
                : 'Tipo de sección desactivado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la solicitud sobre el tipo de sección.']);
        }
    }
}

