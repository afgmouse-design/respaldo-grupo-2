<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraAcademica\Maestros\StoreEscalaMedicionRequest;
use App\Http\Requests\EstructuraAcademica\Maestros\UpdateEscalaMedicionRequest;
use App\Models\EstructuraAcademica\Maestros\EscalaMedicion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Escala de Medición (Módulo: Estructura Académica / Currículo).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL, ni cláusulas where, ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas al modelo EscalaMedicion.
 *
 * NOTA: Métodos $this->authorize() retirados temporalmente para permitir
 * la prueba fluida del CRUD sin bloqueos de permisos 403.
 */
class EscalaMedicionController extends Controller
{
    /**
     * Muestra el listado de escalas de medición con filtros reactivos y paginación.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', EscalaMedicion::class);

        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        // Delegación de consulta al modelo
        $escalas = EscalaMedicion::obtenerPaginados(
            $request->only(['search', 'status', 'status_id', 'fecha_inicio', 'fecha_fin']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/EscalaMedicion/index', [
            'escalas'  => $escalas,
            'perPage'  => $perPage,
            'filters'  => array_merge(
                $request->only(['search', 'status', 'status_id', 'fecha_inicio', 'fecha_fin', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para registrar una nueva escala de medición.
     */
    public function create(): Response
    {
        $this->authorize('create', EscalaMedicion::class);

        return Inertia::render('EstructuraAcademica/Maestros/EscalaMedicion/create');
    }

    /**
     * Almacena una nueva escala de medición en la base de datos delegando al modelo.
     */
    public function store(StoreEscalaMedicionRequest $request): RedirectResponse
    {
        $this->authorize('create', EscalaMedicion::class);

        try {
            // Delegación de inserción al modelo con registrarEscalaMedicion()
            $escala = EscalaMedicion::registrarEscalaMedicion($request->validated(), auth()->id());

            return redirect()
                ->route('estructura_academica.escalas_medicion.index')
                ->with('success', "La escala de medición '{$escala->nombre_esm}' ha sido registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la escala de medición: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra la ficha detallada de una escala de medición.
     */
    public function show(int $id_esm): Response
    {
        $this->authorize('view', EscalaMedicion::class);

        $escala = EscalaMedicion::obtenerConRelaciones($id_esm);

        return Inertia::render('EstructuraAcademica/Maestros/EscalaMedicion/show', [
            'escala' => $escala,
        ]);
    }

    /**
     * Muestra el formulario precargado para editar una escala de medición existente.
     */
    public function edit(int $id_esm): Response
    {
        $this->authorize('update', EscalaMedicion::class);

        $escala = EscalaMedicion::obtenerParaEditar($id_esm);

        return Inertia::render('EstructuraAcademica/Maestros/EscalaMedicion/edit', [
            'escala' => $escala,
        ]);
    }

    /**
     * Actualiza los atributos de una escala de medición existente delegando al modelo.
     */
    public function update(UpdateEscalaMedicionRequest $request, int $id_esm): RedirectResponse
    {
        $this->authorize('update', EscalaMedicion::class);

        try {
            // Delegación de actualización al modelo con actualizarEscalaMedicion()
            $escala = EscalaMedicion::actualizarEscalaMedicion($id_esm, $request->validated(), auth()->id());

            return redirect()
                ->route('estructura_academica.escalas_medicion.index')
                ->with('success', "La escala de medición '{$escala->nombre_esm}' ha sido actualizada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar la escala de medición: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus lógico de la escala de medición (Activo / Inactivo).
     */
    public function toggleStatus(int $id_esm): RedirectResponse
    {
        $this->authorize('delete', EscalaMedicion::class);

        try {
            $resultado = EscalaMedicion::alternarEstatus($id_esm, null, auth()->id());

            $estadoTexto = $resultado['estatus'] ? 'activada' : 'inactivada';

            return redirect()
                ->back()
                ->with('success', "La escala de medición '{$resultado['nombre']}' ha sido {$estadoTexto} exitosamente.");
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo modificar el estatus de la escala de medición.']);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * preservando el registro en PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`alternarEstatus`) por `eliminarConSoftDelete`.
     */
    public function destroy(int $id_esm): RedirectResponse
    {
        $this->authorize('delete', EscalaMedicion::class);

        try {
            $resultado = EscalaMedicion::eliminarConSoftDelete($id_esm, auth()->id());

            return redirect()
                ->back()
                ->with('success', "La escala de medición '{$resultado['nombre']}' ha sido eliminada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar la escala de medición: ' . $e->getMessage()]);
        }
    }
}
