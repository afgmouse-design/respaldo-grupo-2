<?php

namespace App\Http\Controllers\EstructuraGeografica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraGeografica\Maestros\StoreMunicipioRequest;
use App\Http\Requests\EstructuraGeografica\Maestros\UpdateMunicipioRequest;
use App\Models\EstructuraGeografica\Maestros\Estado;
use App\Models\EstructuraGeografica\Maestros\Municipio;
use App\Models\EstructuraGeografica\Maestros\Pais;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Municipio (Módulo 1 - Estructura Geográfica).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL, ni cláusulas where, ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas a los modelos Municipio, Estado y Pais.
 */
class MunicipioController extends Controller
{
    /**
     * Muestra el listado general de municipios con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 5);

        // Delegación de consultas a los modelos
        $municipios = Municipio::obtenerPaginados(
            $request->only(['search', 'pai_id', 'esd_id', 'status']),
            $perPage
        );

        $paises = Pais::obtenerActivosParaSelect();
        $estados = Estado::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Municipio/index', [
            'municipios' => $municipios,
            'paises'     => $paises,
            'estados'    => $estados,
            'perPage'    => $perPage,
            'filters'    => array_merge(
                $request->only(['search', 'pai_id', 'esd_id', 'status']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra la página con el formulario para registrar un nuevo municipio.
     */
    public function create(): Response
    {
        $paises = Pais::obtenerActivosParaSelect();
        $estados = Estado::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Municipio/create', [
            'paises'  => $paises,
            'estados' => $estados,
        ]);
    }

    /**
     * Almacena un nuevo municipio en la base de datos delegando al modelo.
     */
    public function store(StoreMunicipioRequest $request): RedirectResponse
    {
        try {
            // Delegación del insert al modelo con registrarMunicipio()
            $municipio = Municipio::registrarMunicipio($request->validated(), auth()->id());

            return redirect()
                ->route('geografia.municipio.index')
                ->with('success', "El municipio '{$municipio->nombre_mun}' ha sido registrado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar el municipio.']);
        }
    }

    /**
     * Muestra la ficha detallada de un municipio y sus parroquias subordinadas.
     */
    public function show(int $id_mun): Response
    {
        $municipio = Municipio::obtenerConRelaciones($id_mun);

        return Inertia::render('EstructuraGeografica/Municipio/show', [
            'municipio' => $municipio,
        ]);
    }

    /**
     * Muestra la página con el formulario precargado para editar un municipio.
     */
    public function edit(int $id_mun): Response
    {
        $municipio = Municipio::obtenerParaEditar($id_mun);
        $paises = Pais::obtenerActivosParaSelect();
        $estados = Estado::obtenerActivosParaSelect();

        return Inertia::render('EstructuraGeografica/Municipio/edit', [
            'municipio' => $municipio,
            'paises'    => $paises,
            'estados'   => $estados,
        ]);
    }

    /**
     * Actualiza los datos de un municipio delegando al modelo.
     */
    public function update(UpdateMunicipioRequest $request, int $id_mun): RedirectResponse
    {
        try {
            // Delegación del update al modelo con actualizarMunicipio()
            $municipio = Municipio::actualizarMunicipio($id_mun, $request->validated(), auth()->id());

            return redirect()
                ->route('geografia.municipio.index')
                ->with('success', "El municipio '{$municipio->nombre_mun}' ha sido actualizado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar el municipio.']);
        }
    }

    /**
     * Alterna el estatus lógico del municipio (Activo / Inactivo) y propaga en cascada.
     */
    public function toggleStatus(int $id_mun): RedirectResponse
    {
        try {
            $resultado = Municipio::alternarEstatusConCascada($id_mun, auth()->id());

            $mensaje = $resultado['estatus']
                ? "El municipio '{$resultado['nombre']}' y su estructura territorial asociada han sido activados."
                : "El municipio '{$resultado['nombre']}' y su estructura territorial asociada han sido desactivados.";

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
                ->withErrors(['error' => 'No se pudo modificar el estatus del municipio y su jerarquía territorial.']);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * propagando la baja en cascada sin borrar registros físicos de BD.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`desactivarConCascada`) por `eliminarConCascada`.
     */
    public function destroy(int $id_mun): RedirectResponse
    {
        try {
            $resultado = Municipio::eliminarConCascada($id_mun, auth()->id());

            return redirect()
                ->back()
                ->with('success', "El municipio '{$resultado['nombre']}' y su estructura territorial asociada han sido eliminados correctamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar el municipio y su jerarquía territorial.']);
        }
    }
}
