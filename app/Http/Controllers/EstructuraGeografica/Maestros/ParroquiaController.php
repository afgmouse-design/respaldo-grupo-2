<?php

namespace App\Http\Controllers\EstructuraGeografica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraGeografica\Maestros\StoreParroquiaRequest;
use App\Http\Requests\EstructuraGeografica\Maestros\UpdateParroquiaRequest;
use App\Models\EstructuraGeografica\Maestros\Estado;
use App\Models\EstructuraGeografica\Maestros\Municipio;
use App\Models\EstructuraGeografica\Maestros\Parroquia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Parroquia (Módulo 1 - Estructura Geográfica).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL, ni cláusulas where, ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas a los modelos Parroquia, Municipio y Estado.
 */
class ParroquiaController extends Controller
{
    /**
     * Muestra el listado general de parroquias con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 5);

        // Delegación de consultas a los modelos
        $parroquias = Parroquia::obtenerPaginados(
            $request->only(['search', 'esd_id', 'mun_id', 'status']),
            $perPage
        );

        $estados = Estado::obtenerActivosParaSelect();
        $municipios = Municipio::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Parroquia/index', [
            'parroquias' => $parroquias,
            'estados'    => $estados,
            'municipios' => $municipios,
            'perPage'    => $perPage,
            'filters'    => array_merge(
                $request->only(['search', 'esd_id', 'mun_id', 'status']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra la página con el formulario para registrar una nueva parroquia.
     */
    public function create(): Response
    {
        $estados = Estado::obtenerActivosParaSelect();
        $municipios = Municipio::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Parroquia/create', [
            'estados'    => $estados,
            'municipios' => $municipios,
        ]);
    }

    /**
     * Almacena una nueva parroquia en la base de datos delegando al modelo.
     */
    public function store(StoreParroquiaRequest $request): RedirectResponse
    {
        try {
            // Delegación del insert al modelo con registrarParroquia()
            $parroquia = Parroquia::registrarParroquia($request->validated(), auth()->id());

            return redirect()
                ->route('geografia.parroquia.index')
                ->with('success', "La parroquia '{$parroquia->nombre_par}' ha sido registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la parroquia.']);
        }
    }

    /**
     * Muestra la ficha detallada de una parroquia y sus localidades subordinadas.
     */
    public function show(int $id_par): Response
    {
        $parroquia = Parroquia::obtenerConRelaciones($id_par);

        return Inertia::render('EstructuraGeografica/Parroquia/show', [
            'parroquia' => $parroquia,
        ]);
    }

    /**
     * Muestra la página con el formulario precargado para editar una parroquia.
     */
    public function edit(int $id_par): Response
    {
        $parroquia = Parroquia::obtenerParaEditar($id_par);
        $estados = Estado::obtenerActivosParaSelect();
        $municipios = Municipio::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Parroquia/edit', [
            'parroquia'  => $parroquia,
            'estados'    => $estados,
            'municipios' => $municipios,
        ]);
    }

    /**
     * Actualiza los datos de una parroquia delegando al modelo.
     */
    public function update(UpdateParroquiaRequest $request, int $id_par): RedirectResponse
    {
        try {
            // Delegación del update al modelo con actualizarParroquia()
            $parroquia = Parroquia::actualizarParroquia($id_par, $request->validated(), auth()->id());

            return redirect()
                ->route('geografia.parroquia.index')
                ->with('success', "La parroquia '{$parroquia->nombre_par}' ha sido actualizada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar la parroquia.']);
        }
    }

    /**
     * Alterna el estatus lógico de la parroquia (Activo / Inactivo) y propaga en cascada a sus localidades.
     */
    public function toggleStatus(int $id_par): RedirectResponse
    {
        try {
            $resultado = Parroquia::alternarEstatusConCascada($id_par, auth()->id());

            $mensaje = $resultado['estatus']
                ? "La parroquia '{$resultado['nombre']}' y sus localidades asociadas han sido activadas."
                : "La parroquia '{$resultado['nombre']}' y sus localidades asociadas han sido desactivadas.";

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
                ->withErrors(['error' => 'No se pudo modificar el estatus de la parroquia y sus localidades.']);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * propagando la baja a localidades sin borrar registros físicos de BD.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`desactivarConCascada`) por `eliminarConCascada`.
     */
    public function destroy(int $id_par): RedirectResponse
    {
        try {
            $resultado = Parroquia::eliminarConCascada($id_par, auth()->id());

            return redirect()
                ->back()
                ->with('success', "La parroquia '{$resultado['nombre']}' y sus localidades asociadas han sido eliminadas correctamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar la parroquia y sus localidades.']);
        }
    }
}
