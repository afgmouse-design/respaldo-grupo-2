<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraAcademica\Maestros\StoreTrayectoRequest;
use App\Http\Requests\EstructuraAcademica\Maestros\UpdateTrayectoRequest;
use App\Models\EstructuraAcademica\Maestros\TipoPeriodo;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Trayecto / Nivel Académico (Módulo: Estructura Académica / Régimen).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL, ni cláusulas where, ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas a los modelos Trayecto y TipoPeriodo.
 */
class TrayectoController extends Controller
{
    /**
     * Muestra el listado de trayectos con filtros reactivos y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        // Delegación de consultas a los modelos
        $trayectos = Trayecto::obtenerPaginados(
            $request->only(['search', 'tpd_id', 'status']),
            $perPage
        );

        $tiposPeriodo = TipoPeriodo::obtenerActivosParaSelect();

        return Inertia::render('Academico/Trayecto/index', [
            'trayectos'    => $trayectos,
            'tiposPeriodo' => $tiposPeriodo,
            'perPage'      => $perPage,
            'filters'      => array_merge(
                $request->only(['search', 'tpd_id', 'status', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para registrar un nuevo trayecto.
     */
    public function create(): Response
    {
        $tiposPeriodo = TipoPeriodo::obtenerActivosParaSelect();

        return Inertia::render('Academico/Trayecto/create', [
            'tiposPeriodo' => $tiposPeriodo,
        ]);
    }

    /**
     * Almacena un nuevo trayecto delegando al modelo.
     */
    public function store(StoreTrayectoRequest $request): RedirectResponse
    {
        try {
            // Delegación de inserción al modelo con registrarTrayecto()
            $trayecto = Trayecto::registrarTrayecto($request->validated(), auth()->id());

            return redirect()
                ->route('academico.trayecto.index')
                ->with('success', "El trayecto '{$trayecto->nombre_tra}' ha sido registrado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar el trayecto: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el detalle de un trayecto.
     */
    public function show(int $id_tra): Response
    {
        $trayecto = Trayecto::obtenerConRelaciones($id_tra);

        return Inertia::render('Academico/Trayecto/show', [
            'trayecto' => $trayecto,
        ]);
    }

    /**
     * Muestra el formulario para editar un trayecto existente.
     */
    public function edit(int $id_tra): Response
    {
        $trayecto = Trayecto::obtenerParaEditar($id_tra);
        $tiposPeriodo = TipoPeriodo::obtenerActivosParaSelect();

        return Inertia::render('Academico/Trayecto/edit', [
            'trayecto'     => $trayecto,
            'tiposPeriodo' => $tiposPeriodo,
        ]);
    }

    /**
     * Actualiza los datos de un trayecto existente delegando al modelo.
     */
    public function update(UpdateTrayectoRequest $request, int $id_tra): RedirectResponse
    {
        try {
            // Delegación de actualización al modelo con actualizarTrayecto()
            $trayecto = Trayecto::actualizarTrayecto($id_tra, $request->validated(), auth()->id());

            return redirect()
                ->route('academico.trayecto.index')
                ->with('success', "El trayecto '{$trayecto->nombre_tra}' ha sido actualizado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar el trayecto: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus lógico del trayecto (Activo / Inactivo).
     */
    public function toggleStatus(int $id_tra): RedirectResponse
    {
        try {
            $resultado = Trayecto::alternarEstatus($id_tra, auth()->id());

            $estadoTexto = $resultado['estatus'] ? 'activado' : 'inactivado';

            return redirect()
                ->back()
                ->with('success', "El trayecto '{$resultado['nombre']}' ha sido {$estadoTexto} exitosamente.");
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo modificar el estatus del trayecto.']);
        }
    }

    /**
     * Alterna el estatus lógico del trayecto (baja lógica).
     */
    public function destroy(int $id_tra): RedirectResponse
    {
        return $this->toggleStatus($id_tra);
    }
}

