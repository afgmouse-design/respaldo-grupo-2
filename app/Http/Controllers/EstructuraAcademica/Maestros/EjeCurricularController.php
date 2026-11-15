<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\EjeCurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Eje Curricular.
 *
 * Módulo: Estructura Académica / Currículo.
 *
 * Propósito: Gestiona el ciclo de vida CRUD de los ejes curriculares mediante arquitectura SPA con Inertia.js.
 * Implementa baja lógica asignando status_id = 2 en el método destroy.
 *
 * Actualización 2026-10-21 (Corrección de Redirección y Alertas en store y update):
 * Se corrigió la redirección en `store()` y `update()` para enviar al usuario a
 * `route('academico.eje-curricular.index')` con el mensaje flash 'success', en lugar de `back()`,
 * resolviendo el bloqueo donde el usuario no salía del formulario tras guardar/editar y
 * no se visualizaba la notificación flash de éxito en el maestro.
 */
class EjeCurricularController extends Controller
{
    /**
     * Muestra el listado de ejes curriculares activos (status_id = 1) con paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $ejes = EjeCurricular::obtenerPaginados(
            $request->only(['search']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/EjeCurricular/index', [
            'ejes'    => $ejes,
            'perPage' => $perPage,
            'filters' => array_merge(
                $request->only(['search', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo eje curricular.
     */
    public function create(): Response
    {
        return Inertia::render('EstructuraAcademica/Maestros/EjeCurricular/create');
    }

    /**
     * Almacena un nuevo eje curricular en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_ecu'      => ['required', 'string', 'max:100'],
            'codigo_ecu'      => ['nullable', 'string', 'max:30'],
            'descripcion_ecu' => ['nullable', 'string'],
        ]);

        try {
            DB::table('eje_curricular')->insert([
                'nombre_ecu'      => trim($validated['nombre_ecu']),
                'codigo_ecu'      => isset($validated['codigo_ecu']) ? trim($validated['codigo_ecu']) : null,
                'descripcion_ecu' => isset($validated['descripcion_ecu']) ? trim($validated['descripcion_ecu']) : null,
                'status'          => true,
                'status_id'       => 1,
                'created_id'      => auth()->id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return redirect()
                ->route('academico.eje-curricular.index')
                ->with('success', "El eje curricular '{$validated['nombre_ecu']}' ha sido registrado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar el eje curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el detalle de un eje curricular específico.
     */
    public function show(int $id): Response
    {
        $eje = DB::table('eje_curricular')->where('id_ecu', $id)->first();

        if (!$eje) {
            abort(404, 'Eje curricular no encontrado.');
        }

        return Inertia::render('EstructuraAcademica/Maestros/EjeCurricular/show', [
            'eje' => $eje,
        ]);
    }

    /**
     * Muestra el formulario para editar un eje curricular.
     */
    public function edit(int $id): Response
    {
        $eje = DB::table('eje_curricular')->where('id_ecu', $id)->first();

        if (!$eje) {
            abort(404, 'Eje curricular no encontrado.');
        }

        return Inertia::render('EstructuraAcademica/Maestros/EjeCurricular/edit', [
            'eje' => $eje,
        ]);
    }

    /**
     * Actualiza los datos de un eje curricular existente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_ecu'      => ['required', 'string', 'max:100'],
            'codigo_ecu'      => ['nullable', 'string', 'max:30'],
            'descripcion_ecu' => ['nullable', 'string'],
        ]);

        try {
            DB::table('eje_curricular')
                ->where('id_ecu', $id)
                ->update([
                    'nombre_ecu'      => trim($validated['nombre_ecu']),
                    'codigo_ecu'      => isset($validated['codigo_ecu']) ? trim($validated['codigo_ecu']) : null,
                    'descripcion_ecu' => isset($validated['descripcion_ecu']) ? trim($validated['descripcion_ecu']) : null,
                    'updated_id'      => auth()->id(),
                    'updated_at'      => now(),
                ]);

            return redirect()
                ->route('academico.eje-curricular.index')
                ->with('success', "El eje curricular '{$validated['nombre_ecu']}' ha sido actualizado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar el eje curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Realiza la baja lógica del eje curricular llamando a alternarEstatus($id, 2).
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $eje = DB::table('eje_curricular')->where('id_ecu', $id)->first();
            $nombre = $eje ? $eje->nombre_ecu : 'Eje curricular';

            EjeCurricular::alternarEstatus($id, 2);

            return redirect()
                ->back()
                ->with('success', "El eje curricular '{$nombre}' ha sido dado de baja (inactivado) exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo dar de baja el eje curricular: ' . $e->getMessage()]);
        }
    }
}

