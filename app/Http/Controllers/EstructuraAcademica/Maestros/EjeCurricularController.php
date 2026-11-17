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
            $request->only(['search', 'status', 'status_id']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/EjeCurricular/index', [
            'ejes'    => $ejes,
            'perPage' => $perPage,
            'filters' => array_merge(
                $request->only(['search', 'status', 'status_id', 'per_page', 'registros_per_page']),
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
                'status_id'       => EjeCurricular::STATUS_ACTIVO,
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
        $eje = DB::table('eje_curricular')->where('id_ecu', $id)->whereNull('deleted_at')->first();

        if (!$eje) {
            abort(404, 'Eje curricular no encontrado.');
        }

        return Inertia::render('EstructuraAcademica/Maestros/EjeCurricular/show', [
            'eje' => $eje,
        ]);
    }

    /**
     * Muestra el formulario para editar un eje curricular.
     * Historial: Se agrega filtro whereNull('deleted_at') para respetar el SoftDelete de PostgreSQL.
     */
    public function edit(int $id): Response
    {
        $eje = DB::table('eje_curricular')->where('id_ecu', $id)->whereNull('deleted_at')->first();

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
     * Alterna el estatus lógico (`status_id` 1 <-> 2) del eje curricular sin eliminarlo.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        try {
            $eje = EjeCurricular::where('id_ecu', $id)->firstOrFail();
            $nuevoEstado = EjeCurricular::esStatusActivo($eje)
                ? EjeCurricular::STATUS_INACTIVO
                : EjeCurricular::STATUS_ACTIVO;

            EjeCurricular::alternarEstatus($id, $nuevoEstado);

            $estadoTexto = $nuevoEstado === EjeCurricular::STATUS_ACTIVO ? 'activado' : 'inactivado';

            return redirect()
                ->back()
                ->with('success', "El eje curricular '{$eje->nombre_ecu}' ha sido {$estadoTexto} exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo cambiar el estatus del eje curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * preservando el registro histórico en PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`alternarEstatus`) por `ejecutarBajaLogicaModelo`.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $eje = EjeCurricular::where('id_ecu', $id)->firstOrFail();
            $nombre = $eje->nombre_ecu;

            EjeCurricular::ejecutarBajaLogicaModelo($eje, auth()->id(), EjeCurricular::STATUS_INACTIVO);

            return redirect()
                ->back()
                ->with('success', "El eje curricular '{$nombre}' ha sido eliminado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar el eje curricular: ' . $e->getMessage()]);
        }
    }
}

