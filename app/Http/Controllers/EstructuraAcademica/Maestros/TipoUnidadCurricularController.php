<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\TipoUnidadCurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Tipo de Unidad Curricular.
 *
 * Módulo: Estructura Académica / Currículo.
 *
 * Propósito: Gestiona el ciclo de vida CRUD de los tipos de unidad curricular mediante arquitectura SPA con Inertia.js.
 * Renderiza estrictamente las vistas bajo EstructuraAcademica/Maestros/TipoUnidadCurricular/.
 *
 * Actualización:
 *  - Corrección estricta de rutas de vistas en Inertia::render() hacia 'EstructuraAcademica/Maestros/TipoUnidadCurricular/...'.
 *  - Uso de asignación masiva Eloquent TipoUnidadCurricular::create() y $tipo->update() con la propiedad $fillable configurada.
 */
class TipoUnidadCurricularController extends Controller
{
    /**
     * Muestra el listado de tipos de unidad curricular activos (status_id = 1) con paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $tipos = TipoUnidadCurricular::obtenerPaginados(
            $request->only(['search', 'status']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/TipoUnidadCurricular/index', [
            'tipos'   => $tipos,
            'perPage' => $perPage,
            'filters' => array_merge(
                $request->only(['search', 'status', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de unidad curricular.
     */
    public function create(): Response
    {
        return Inertia::render('EstructuraAcademica/Maestros/TipoUnidadCurricular/create');
    }

    /**
     * Almacena un nuevo tipo de unidad curricular en la base de datos utilizando Eloquent y $fillable.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_tuc' => ['required', 'string', 'max:150'],
        ]);

        try {
            TipoUnidadCurricular::create([
                'nombre_tuc' => trim($validated['nombre_tuc']),
                'status_id'  => TipoUnidadCurricular::STATUS_ACTIVO,
                'created_id' => auth()->id(),
            ]);

            return redirect()
                ->route('academico.tipo-unidad-curricular.index')
                ->with('success', "El tipo de unidad curricular '{$validated['nombre_tuc']}' ha sido registrado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar el tipo de unidad curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el detalle de un tipo de unidad curricular específico.
     */
    public function show(int $id): Response
    {
        $tipo = TipoUnidadCurricular::where('id_tuc', $id)->firstOrFail();

        return Inertia::render('EstructuraAcademica/Maestros/TipoUnidadCurricular/show', [
            'tipo' => $tipo,
        ]);
    }

    /**
     * Muestra el formulario para editar un tipo de unidad curricular.
     */
    public function edit(int $id): Response
    {
        $tipo = TipoUnidadCurricular::where('id_tuc', $id)->firstOrFail();

        return Inertia::render('EstructuraAcademica/Maestros/TipoUnidadCurricular/edit', [
            'tipo' => $tipo,
        ]);
    }

    /**
     * Actualiza los datos de un tipo de unidad curricular existente utilizando Eloquent y $fillable.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_tuc' => ['required', 'string', 'max:150'],
        ]);

        try {
            $tipo = TipoUnidadCurricular::where('id_tuc', $id)->firstOrFail();

            $tipo->update([
                'nombre_tuc' => trim($validated['nombre_tuc']),
                'updated_id' => auth()->id(),
            ]);

            return redirect()
                ->route('academico.tipo-unidad-curricular.index')
                ->with('success', "El tipo de unidad curricular '{$validated['nombre_tuc']}' ha sido actualizado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar el tipo de unidad curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus lógico (`status_id` 1 <-> 2) del tipo de unidad curricular sin eliminarlo.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        try {
            $tipo = TipoUnidadCurricular::where('id_tuc', $id)->firstOrFail();
            $nuevoEstado = TipoUnidadCurricular::esStatusActivo($tipo)
                ? TipoUnidadCurricular::STATUS_INACTIVO
                : TipoUnidadCurricular::STATUS_ACTIVO;

            TipoUnidadCurricular::alternarEstatus($id, $nuevoEstado);

            $estadoTexto = $nuevoEstado === TipoUnidadCurricular::STATUS_ACTIVO ? 'activado' : 'inactivado';

            return redirect()
                ->back()
                ->with('success', "El tipo de unidad curricular '{$tipo->nombre_tuc}' ha sido {$estadoTexto} exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo cambiar el estatus del tipo de unidad curricular: ' . $e->getMessage()]);
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
            $tipo = TipoUnidadCurricular::where('id_tuc', $id)->firstOrFail();
            $nombre = $tipo->nombre_tuc;

            TipoUnidadCurricular::ejecutarBajaLogicaModelo($tipo, auth()->id(), TipoUnidadCurricular::STATUS_INACTIVO);

            return redirect()
                ->back()
                ->with('success', "El tipo de unidad curricular '{$nombre}' ha sido eliminado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar el tipo de unidad curricular: ' . $e->getMessage()]);
        }
    }
}
