<?php

namespace App\Http\Controllers\EstructuraGeografica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraGeografica\Maestros\StorePaisRequest;
use App\Http\Requests\EstructuraGeografica\Maestros\UpdatePaisRequest;
use App\Models\EstructuraGeografica\Maestros\Pais;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de País (Módulo 1 - Estructura Geográfica).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas al modelo Pais.
 */
class PaisController extends Controller
{
    /**
     * Muestra el listado general de países con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 5);

        // Delegación de la consulta al modelo (Regla estricta)
        $paises = Pais::obtenerPaginados(
            $request->only(['search', 'status']),
            $perPage
        );

        return Inertia::render('EstructuraGeografica/Pais/index', [
            'paises'  => $paises,
            'perPage' => $perPage,
            'filters' => array_merge(
                $request->only(['search', 'status', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra la página con el formulario para registrar un nuevo país.
     */
    public function create(): Response
    {
        return Inertia::render('EstructuraGeografica/Pais/create');
    }

    /**
     * Almacena un nuevo país en la base de datos delegando al modelo.
     */
    public function store(StorePaisRequest $request): RedirectResponse
    {
        try {
            // Delegación del insert al modelo con registrarPais() (Regla estricta)
            $pais = Pais::registrarPais($request->validated(), auth()->id());

            return redirect()
                ->route('geografia.pais.index')
                ->with('success', "El país '{$pais->nombre_pai}' ha sido registrado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar el país.']);
        }
    }

    /**
     * Muestra la ficha detallada de un país y sus estados subordinados.
     */
    public function show(int $id_pai): Response
    {
        $pais = Pais::obtenerConEstados($id_pai);

        return Inertia::render('EstructuraGeografica/Pais/show', [
            'pais' => $pais,
        ]);
    }

    /**
     * Muestra la página con el formulario precargado para editar un país.
     */
    public function edit(int $id_pai): Response
    {
        $pais = Pais::obtenerPorId($id_pai);

        return Inertia::render('EstructuraGeografica/Pais/edit', [
            'pais' => $pais,
        ]);
    }

    /**
     * Actualiza los datos de un país delegando al modelo.
     */
    public function update(UpdatePaisRequest $request, int $id_pai): RedirectResponse
    {
        try {
            // Delegación del update al modelo con actualizarPais() (Regla estricta)
            $pais = Pais::actualizarPais($id_pai, $request->validated(), auth()->id());

            return redirect()
                ->route('geografia.pais.index')
                ->with('success', "El país '{$pais->nombre_pai}' ha sido actualizado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar el país.']);
        }
    }

    /**
     * Alterna el estatus lógico del país y su estructura en cascada.
     */
    public function toggleStatus(int $id_pai): RedirectResponse
    {
        try {
            $resultado = Pais::alternarEstatusConCascada($id_pai, auth()->id());

            $mensaje = $resultado['estatus']
                ? "El país '{$resultado['nombre']}' y su estructura territorial asociada han sido activados."
                : "El país '{$resultado['nombre']}' y su estructura territorial asociada han sido desactivados.";

            return redirect()
                ->back()
                ->with('success', $mensaje);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo modificar el estatus del país y su jerarquía territorial.']);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * propagando la baja en cascada a la jerarquía territorial sin borrar registros de BD.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`desactivarConCascada`) por `eliminarConCascada`.
     */
    public function destroy(int $id_pai): RedirectResponse
    {
        try {
            $resultado = Pais::eliminarConCascada($id_pai, auth()->id());

            return redirect()
                ->back()
                ->with('success', "El país '{$resultado['nombre']}' y su estructura territorial asociada han sido eliminados correctamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar el país y su jerarquía territorial.']);
        }
    }
}