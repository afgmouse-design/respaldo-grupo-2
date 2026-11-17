<?php

namespace App\Http\Controllers\EstructuraGeografica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraGeografica\Maestros\StoreLocalidadRequest;
use App\Http\Requests\EstructuraGeografica\Maestros\UpdateLocalidadRequest;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\EstructuraGeografica\Maestros\Municipio;
use App\Models\EstructuraGeografica\Maestros\Parroquia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Localidad (Módulo 1 - Estructura Geográfica).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL, ni cláusulas where, ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas a los modelos Localidad, Parroquia y Municipio.
 */
class LocalidadController extends Controller
{
    /**
     * Muestra el listado general de localidades con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 5);

        // Delegación de consultas a los modelos
        $localidades = Localidad::obtenerPaginados(
            $request->only(['search', 'mun_id', 'par_id', 'status']),
            $perPage
        );

        $municipios = Municipio::obtenerActivosParaSelect();
        $parroquias = Parroquia::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Localidad/index', [
            'localidades' => $localidades,
            'municipios'  => $municipios,
            'parroquias'  => $parroquias,
            'perPage'     => $perPage,
            'filters'     => array_merge(
                $request->only(['search', 'mun_id', 'par_id', 'status']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra la página con el formulario para registrar una nueva localidad.
     */
    public function create(): Response
    {
        $municipios = Municipio::obtenerActivosParaSelect();
        $parroquias = Parroquia::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Localidad/create', [
            'municipios' => $municipios,
            'parroquias' => $parroquias,
        ]);
    }

    /**
     * Almacena una nueva localidad en la base de datos delegando al modelo.
     */
    public function store(StoreLocalidadRequest $request): RedirectResponse
    {
        try {
            // Delegación del insert al modelo con registrarLocalidad()
            $localidad = Localidad::registrarLocalidad($request->validated(), auth()->id());

            return redirect()
                ->route('geografia.localidad.index')
                ->with('success', "La localidad '{$localidad->nombre_loc}' ha sido registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la localidad.']);
        }
    }

    /**
     * Muestra la ficha detallada de una localidad y su jerarquía ascendente completa.
     */
    public function show(int $id_loc): Response
    {
        $localidad = Localidad::obtenerConRelaciones($id_loc);

        return Inertia::render('EstructuraGeografica/Localidad/show', [
            'localidad' => $localidad,
        ]);
    }

    /**
     * Muestra la página con el formulario precargado para editar una localidad.
     */
    public function edit(int $id_loc): Response
    {
        $localidad = Localidad::obtenerParaEditar($id_loc);
        $municipios = Municipio::obtenerActivosParaSelect();
        $parroquias = Parroquia::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Localidad/edit', [
            'localidad'  => $localidad,
            'municipios' => $municipios,
            'parroquias' => $parroquias,
        ]);
    }

    /**
     * Actualiza los datos de una localidad delegando al modelo.
     */
    public function update(UpdateLocalidadRequest $request, int $id_loc): RedirectResponse
    {
        try {
            // Delegación del update al modelo con actualizarLocalidad()
            $localidad = Localidad::actualizarLocalidad($id_loc, $request->validated(), auth()->id());

            return redirect()
                ->route('geografia.localidad.index')
                ->with('success', "La localidad '{$localidad->nombre_loc}' ha sido actualizada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar la localidad.']);
        }
    }

    /**
     * Alterna el estatus lógico de la localidad (Activo / Inactivo).
     */
    public function toggleStatus(int $id_loc): RedirectResponse
    {
        try {
            $resultado = Localidad::alternarEstatus($id_loc, auth()->id());

            $mensaje = $resultado['estatus']
                ? "La localidad '{$resultado['nombre']}' ha sido activada."
                : "La localidad '{$resultado['nombre']}' ha sido desactivada.";

            return redirect()
                ->back()
                ->with('success', $mensaje);
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo modificar el estatus de la localidad.']);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * sin borrar registros físicos de PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`desactivarConCascada`) por `eliminarConCascada`.
     */
    public function destroy(int $id_loc): RedirectResponse
    {
        try {
            $resultado = Localidad::eliminarConCascada($id_loc, auth()->id());

            return redirect()
                ->back()
                ->with('success', "La localidad '{$resultado['nombre']}' ha sido eliminada correctamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar la localidad.']);
        }
    }
}
