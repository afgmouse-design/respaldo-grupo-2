<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraAcademica\Maestros\StoreTrayectoRequest;
use App\Http\Requests\EstructuraAcademica\Maestros\UpdateTrayectoRequest;
use App\Models\Academico\TipoLapso;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Trayecto / Nivel Académico (`trayecto`).
 *
 * Propósito: Gestiona trayectos asociados al Tipo de Lapso (`tla_id`) mediante
 * arquitectura Skinny Controller delegando al modelo `Trayecto`.
 *
 * Historial:
 *  - 2026-11-14: Reemplazado `TipoPeriodo`/`tpd_id` por `TipoLapso`/`tla_id` y
 *    habilitada la autorización formal vía `TrayectoPolicy`.
 */
class TrayectoController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Trayecto::class);

        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $trayectos = Trayecto::obtenerPaginados(
            $request->only(['search', 'tla_id', 'tpd_id', 'status', 'status_id']),
            $perPage
        );

        $tiposLapso = TipoLapso::obtenerActivosParaSelect();

        return Inertia::render('Academico/Trayecto/index', [
            'trayectos'    => $trayectos,
            'tiposLapso'   => $tiposLapso,
            'tiposPeriodo' => $tiposLapso,
            'perPage'      => $perPage,
            'filters'      => array_merge(
                $request->only(['search', 'tla_id', 'tpd_id', 'status', 'status_id', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Trayecto::class);

        $tiposLapso = TipoLapso::obtenerActivosParaSelect();

        return Inertia::render('Academico/Trayecto/create', [
            'tiposLapso'   => $tiposLapso,
            'tiposPeriodo' => $tiposLapso,
        ]);
    }

    public function store(StoreTrayectoRequest $request): RedirectResponse
    {
        $this->authorize('create', Trayecto::class);

        try {
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

    public function show(int $id_tra): Response
    {
        $this->authorize('view', Trayecto::class);

        return Inertia::render('Academico/Trayecto/show', [
            'trayecto' => Trayecto::obtenerConRelaciones($id_tra),
        ]);
    }

    public function edit(int $id_tra): Response
    {
        $this->authorize('update', Trayecto::class);

        $tiposLapso = TipoLapso::obtenerActivosParaSelect();

        return Inertia::render('Academico/Trayecto/edit', [
            'trayecto'     => Trayecto::obtenerParaEditar($id_tra),
            'tiposLapso'   => $tiposLapso,
            'tiposPeriodo' => $tiposLapso,
        ]);
    }

    public function update(UpdateTrayectoRequest $request, int $id_tra): RedirectResponse
    {
        $this->authorize('update', Trayecto::class);

        try {
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

    public function toggleStatus(int $id_tra): RedirectResponse
    {
        $this->authorize('delete', Trayecto::class);

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
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`) del trayecto
     * sin borrar filas físicas de PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Desacoplado de `toggleStatus()` para realizar baja lógica real en interfaz.
     */
    public function destroy(int $id_tra): RedirectResponse
    {
        $this->authorize('delete', Trayecto::class);

        try {
            $resultado = Trayecto::eliminarConSoftDelete($id_tra, auth()->id());

            return redirect()
                ->back()
                ->with('success', "El trayecto '{$resultado['nombre']}' ha sido eliminado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar el trayecto: ' . $e->getMessage()]);
        }
    }
}
