<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\TipoUnidadCurricular;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Gestiona el ciclo de vida CRUD de las unidades curriculares (materias) mediante arquitectura SPA con Inertia.js.
 * Carga en create y edit los tipos de unidad curricular activos (status_id = 1).
 * Implementa baja lógica asignando status_id = 2 en el método destroy.
 *
 * Actualización 2026-10-21 (Corrección de Redirección y Alertas en store y update):
 * Se corrigió la redirección en `store()` y `update()` para enviar al usuario a
 * `route('academico.unidad-curricular.index')` con el mensaje flash 'success', en lugar de `back()`,
 * resolviendo el bloqueo donde el usuario no salía del formulario tras guardar/editar y
 * no se visualizaba la notificación flash de éxito en el maestro.
 */
class UnidadCurricularController extends Controller
{
    /**
     * Muestra el listado de unidades curriculares activas (status_id = 1) con filtros reactivos y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $unidades = UnidadCurricular::obtenerPaginados(
            $request->only(['search', 'status']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/UnidadCurricular/index', [
            'unidades' => $unidades,
            'perPage'  => $perPage,
            'filters'  => array_merge(
                $request->only(['search', 'status', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva unidad curricular.
     */
    public function create(): Response
    {
        $tipos = TipoUnidadCurricular::where('status_id', 1)->orderBy('nombre_tuc', 'asc')->get();
        $trayectos = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra', 'asc')->get();

        return Inertia::render('EstructuraAcademica/Maestros/UnidadCurricular/create', [
            'tipos'        => $tipos,
            'tipos_unidad' => $tipos,
            'trayectos'    => $trayectos,
        ]);
    }

    /**
     * Almacena una nueva unidad curricular en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_unc'           => ['nullable', 'string', 'max:30'],
            'nombre_unc'           => ['required', 'string', 'max:255'],
            'tuc_id'               => ['required', 'integer', 'exists:tipo_unidad_curricular,id_tuc'],
            'semestre_unc'         => ['required', 'integer', 'min:1', 'max:12'],
            'tra_id'               => ['nullable', 'integer', 'exists:trayecto,id_tra'],
            'unidades_credito_unc' => ['nullable', 'integer', 'min:0'],
            'htea_unc'             => ['nullable', 'integer', 'min:0'],
            'htei_unc'             => ['nullable', 'integer', 'min:0'],
            'thte_unc'             => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $htea = (int) ($validated['htea_unc'] ?? 3);
            $htei = (int) ($validated['htei_unc'] ?? 3);
            $thte = !empty($validated['thte_unc']) ? (int) $validated['thte_unc'] : ($htea + $htei);

            $unidad = UnidadCurricular::create([
                'codigo_unc'           => !empty($validated['codigo_unc']) ? trim($validated['codigo_unc']) : null,
                'nombre_unc'           => trim($validated['nombre_unc']),
                'tuc_id'               => $validated['tuc_id'],
                'semestre_unc'         => $validated['semestre_unc'],
                'tra_id'               => $validated['tra_id'] ?? null,
                'unidades_credito_unc' => $validated['unidades_credito_unc'] ?? 3,
                'htea_unc'             => $htea,
                'htei_unc'             => $htei,
                'thte_unc'             => $thte,
                'status_id'            => 1,
                'status'               => true,
                'created_id'           => auth()->id(),
            ]);

            return redirect()
                ->route('academico.unidad-curricular.index')
                ->with('success', "La unidad curricular '{$unidad->nombre_unc}' ha sido registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la unidad curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el detalle de una unidad curricular específica.
     */
    public function show(int $id): Response
    {
        $unidad = UnidadCurricular::with(['tipoUnidadCurricular', 'trayecto'])
            ->where('id_unc', $id)
            ->firstOrFail();

        return Inertia::render('EstructuraAcademica/Maestros/UnidadCurricular/show', [
            'unidad' => $unidad,
        ]);
    }

    /**
     * Muestra el formulario para editar una unidad curricular.
     */
    public function edit(int $id): Response
    {
        $unidad = UnidadCurricular::with(['tipoUnidadCurricular', 'trayecto'])
            ->where('id_unc', $id)
            ->firstOrFail();

        $tipos = TipoUnidadCurricular::where('status_id', 1)->orderBy('nombre_tuc', 'asc')->get();
        $trayectos = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra', 'asc')->get();

        return Inertia::render('EstructuraAcademica/Maestros/UnidadCurricular/edit', [
            'unidad'       => $unidad,
            'tipos'        => $tipos,
            'tipos_unidad' => $tipos,
            'trayectos'    => $trayectos,
        ]);
    }

    /**
     * Actualiza los datos de una unidad curricular existente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_unc'           => ['nullable', 'string', 'max:30'],
            'nombre_unc'           => ['required', 'string', 'max:255'],
            'tuc_id'               => ['required', 'integer', 'exists:tipo_unidad_curricular,id_tuc'],
            'semestre_unc'         => ['required', 'integer', 'min:1', 'max:12'],
            'tra_id'               => ['nullable', 'integer', 'exists:trayecto,id_tra'],
            'unidades_credito_unc' => ['nullable', 'integer', 'min:0'],
            'htea_unc'             => ['nullable', 'integer', 'min:0'],
            'htei_unc'             => ['nullable', 'integer', 'min:0'],
            'thte_unc'             => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $unidad = UnidadCurricular::where('id_unc', $id)->firstOrFail();

            $htea = (int) ($validated['htea_unc'] ?? 3);
            $htei = (int) ($validated['htei_unc'] ?? 3);
            $thte = !empty($validated['thte_unc']) ? (int) $validated['thte_unc'] : ($htea + $htei);

            $unidad->update([
                'codigo_unc'           => !empty($validated['codigo_unc']) ? trim($validated['codigo_unc']) : null,
                'nombre_unc'           => trim($validated['nombre_unc']),
                'tuc_id'               => $validated['tuc_id'],
                'semestre_unc'         => $validated['semestre_unc'],
                'tra_id'               => $validated['tra_id'] ?? null,
                'unidades_credito_unc' => $validated['unidades_credito_unc'] ?? 3,
                'htea_unc'             => $htea,
                'htei_unc'             => $htei,
                'thte_unc'             => $thte,
                'updated_id'           => auth()->id(),
            ]);

            return redirect()
                ->route('academico.unidad-curricular.index')
                ->with('success', "La unidad curricular '{$unidad->nombre_unc}' ha sido actualizada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar la unidad curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Realiza la baja lógica de la unidad curricular llamando a alternarEstatus($id, 2).
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $unidad = UnidadCurricular::where('id_unc', $id)->first();
            $nombre = $unidad ? $unidad->nombre_unc : 'Unidad curricular';

            UnidadCurricular::alternarEstatus($id, 2);

            return redirect()
                ->back()
                ->with('success', "La unidad curricular '{$nombre}' ha sido dada de baja (inactivada) exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo dar de baja la unidad curricular: ' . $e->getMessage()]);
        }
    }
}

