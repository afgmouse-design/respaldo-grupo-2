<?php

namespace App\Http\Controllers\OrganizacionInstitucional\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizacionInstitucional\Maestros\StoreSedeRequest;
use App\Http\Requests\OrganizacionInstitucional\Maestros\UpdateSedeRequest;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\OrganizacionInstitucional\Maestros\CondicionSede;
use App\Models\OrganizacionInstitucional\Maestros\Sede;
use App\Models\OrganizacionInstitucional\Maestros\Universidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Sede Universitaria (Módulo 2 - Organización Institucional y Sedes).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL, ni cláusulas where, ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas a los modelos Sede, Universidad, Localidad y Condicion.
 */
class SedeController extends Controller
{
    /**
     * Muestra el listado de sedes con filtros reactivos y paginación.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Sede::class);

        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 5);

        // Delegación de consultas a los modelos
        $sedes = Sede::obtenerPaginados(
            $request->only(['search', 'status', 'uni_id']),
            $perPage
        );

        $universidades = Universidad::obtenerActivosParaSelect();

        return Inertia::render('OrganizacionInstitucional/Sede/index', [
            'sedes'         => $sedes,
            'universidades' => $universidades,
            'perPage'       => $perPage,
            'filters'       => array_merge(
                $request->only(['search', 'status', 'uni_id', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para registrar una nueva sede universitaria.
     */
    public function create(): Response
    {
        $this->authorize('create', Sede::class);

        $universidades = Universidad::obtenerActivosParaSelect();
        $localidades = Localidad::obtenerActivosConJerarquia();
        $condiciones = CondicionSede::obtenerActivosParaSelect();

        return Inertia::render('OrganizacionInstitucional/Sede/create', [
            'universidades' => $universidades,
            'localidades'   => $localidades,
            'condiciones'   => $condiciones,
        ]);
    }

    /**
     * Almacena una nueva sede en la base de datos delegando al modelo.
     */
    public function store(StoreSedeRequest $request): RedirectResponse
    {
        $this->authorize('create', Sede::class);

        try {
            // Delegación del insert al modelo con registrarSede()
            $sede = Sede::registrarSede($request->validated(), auth()->id());

            return redirect()
                ->route('organizacion.sede.index')
                ->with('success', "La sede '{$sede->nombre_sed}' ha sido registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la sede: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra la ficha detallada de una sede universitaria.
     */
    public function show(int $id_sed): Response
    {
        $this->authorize('view', Sede::class);

        $sede = Sede::obtenerConRelaciones($id_sed);

        return Inertia::render('OrganizacionInstitucional/Sede/show', [
            'sede' => $sede,
        ]);
    }

    /**
     * Muestra el formulario precargado para editar una sede existente.
     */
    public function edit(int $id_sed): Response
    {
        $this->authorize('update', Sede::class);

        $sede = Sede::obtenerParaEditar($id_sed);

        $universidades = Universidad::obtenerActivosParaSelect();
        $localidades = Localidad::obtenerActivosConJerarquia();
        $condiciones = CondicionSede::obtenerActivosParaSelect();

        return Inertia::render('OrganizacionInstitucional/Sede/edit', [
            'sede'          => $sede,
            'universidades' => $universidades,
            'localidades'   => $localidades,
            'condiciones'   => $condiciones,
        ]);
    }

    /**
     * Actualiza los atributos de una sede existente delegando al modelo.
     */
    public function update(UpdateSedeRequest $request, int $id_sed): RedirectResponse
    {
        $this->authorize('update', Sede::class);

        $sedePrev = Sede::obtenerPorId($id_sed);

        try {
            // Delegación del update al modelo con actualizarSede()
            $sede = Sede::actualizarSede($id_sed, $request->validated(), auth()->id());

            return redirect()
                ->route('organizacion.sede.index')
                ->with('success', "La sede '{$sede->nombre_sed}' ha sido actualizada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar la sede: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus lógico de la sede (Activo / Inactivo).
     */
    public function toggleStatus(int $id_sed): RedirectResponse
    {
        $this->authorize('delete', Sede::class);

        try {
            $resultado = Sede::alternarEstatus($id_sed, auth()->id());

            $estadoTexto = $resultado['estatus'] ? 'activada' : 'inactivada';

            return redirect()
                ->back()
                ->with('success', "La sede '{$resultado['nombre']}' ha sido {$estadoTexto} exitosamente.");
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo modificar el estatus de la sede.']);
        }
    }

    /**
     * Da de baja lógica real en interfaz a la sede (`deleted_at` + `status_id = 2`),
     * manteniendo el registro intacto en PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`alternarEstatus`) por `eliminarConSoftDelete`.
     */
    public function destroy(int $id_sed): RedirectResponse
    {
        $this->authorize('delete', Sede::class);

        try {
            $resultado = Sede::eliminarConSoftDelete($id_sed, auth()->id());

            return redirect()
                ->back()
                ->with('success', "La sede '{$resultado['nombre']}' ha sido eliminada exitosamente.");
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar la sede.']);
        }
    }
}
