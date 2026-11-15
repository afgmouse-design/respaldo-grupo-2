<?php

namespace App\Http\Controllers\EstructuraGeografica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraGeografica\Maestros\StoreEstadoRequest;
use App\Http\Requests\EstructuraGeografica\Maestros\UpdateEstadoRequest;
use App\Models\EstructuraGeografica\Maestros\Estado;
use App\Models\EstructuraGeografica\Maestros\Pais;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Estado (Módulo 1 - Estructura Geográfica).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL, ni cláusulas where, ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas a los modelos Estado y Pais.
 */
class EstadoController extends Controller
{
    /**
     * Muestra el listado general de estados con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 5);

        // Delegación de consultas a los modelos
        $estados = Estado::obtenerPaginados(
            $request->only(['search', 'pai_id', 'status']),
            $perPage
        );

        $paises = Pais::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Estado/index', [
            'estados' => $estados,
            'paises'  => $paises,
            'perPage' => $perPage,
            'filters' => array_merge(
                $request->only(['search', 'pai_id', 'status']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra la página con el formulario para registrar un nuevo estado.
     */
    public function create(): Response
    {
        $paises = Pais::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Estado/create', [
            'paises' => $paises,
        ]);
    }

    /**
     * Almacena un nuevo estado en la base de datos delegando al modelo.
     */
    public function store(StoreEstadoRequest $request): RedirectResponse
    {
        try {
            // Delegación del insert al modelo con registrarEstado()
            $estado = Estado::registrarEstado($request->validated(), auth()->id());

            return redirect()
                ->route('geografia.estado.index')
                ->with('success', "El estado '{$estado->nombre_esd}' ha sido registrado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar el estado.']);
        }
    }

    /**
     * Muestra la ficha detallada de un estado y sus municipios subordinados.
     */
    public function show(int $id_esd): Response
    {
        $estado = Estado::obtenerConRelaciones($id_esd);

        return Inertia::render('EstructuraGeografica/Estado/show', [
            'estado' => $estado,
        ]);
    }

    /**
     * Muestra la página con el formulario precargado para editar un estado.
     */
    public function edit(int $id_esd): Response
    {
        $estado = Estado::obtenerPorId($id_esd);
        $paises = Pais::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Estado/edit', [
            'estado' => $estado,
            'paises' => $paises,
        ]);
    }

    /**
     * Actualiza los datos de un estado delegando al modelo.
     */
    public function update(UpdateEstadoRequest $request, int $id_esd): RedirectResponse
    {
        try {
            // Delegación del update al modelo con actualizarEstado()
            $estado = Estado::actualizarEstado($id_esd, $request->validated(), auth()->id());

            return redirect()
                ->route('geografia.estado.index')
                ->with('success', "El estado '{$estado->nombre_esd}' ha sido actualizado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar el estado.']);
        }
    }

    /**
     * Alterna el estatus lógico del estado (Activo / Inactivo) y propaga en cascada.
     */
    public function toggleStatus(int $id_esd): RedirectResponse
    {
        try {
            $resultado = Estado::alternarEstatusConCascada($id_esd, auth()->id());

            $mensaje = $resultado['estatus']
                ? "El estado '{$resultado['nombre']}' y su estructura territorial asociada han sido activados."
                : "El estado '{$resultado['nombre']}' y su estructura territorial asociada han sido desactivados.";

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
                ->withErrors(['error' => 'No se pudo modificar el estatus del estado y su jerarquía territorial.']);
        }
    }

    /**
     * Realiza la baja lógica del estado y propaga la desactivación en cascada a sus dependencias subordinadas.
     */
    public function destroy(int $id_esd): RedirectResponse
    {
        try {
            $resultado = Estado::desactivarConCascada($id_esd, auth()->id());

            return redirect()
                ->back()
                ->with('success', "El estado '{$resultado['nombre']}' y su estructura territorial asociada han sido eliminados correctamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar el estado y su jerarquía territorial.']);
        }
    }
}
