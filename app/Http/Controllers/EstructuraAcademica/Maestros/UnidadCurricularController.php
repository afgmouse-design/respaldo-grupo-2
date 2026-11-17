<?php

/**
 * Propósito: Controlador CRUD para el catálogo maestro de Unidades Curriculares (`unidad_curricular`).
 * Historial: Actualizado para operar exclusivamente sobre las columnas oficiales del dump (`codigo_unc`, `codigo_anterior_unc`, `nombre_unc`, `tuc_id`, `dep_id`, `status_id`), eliminando campos movidos a `malla_curricular_detalle` y el campo booleano `status`.
 */

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\TipoUnidadCurricular;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Gestiona el ciclo de vida CRUD de las unidades curriculares (materias) mediante arquitectura SPA con Inertia.js.
 * Carga en create y edit los tipos de unidad curricular activos (status_id = 1).
 * Implementa baja lógica asignando status_id = 2 en el método destroy.
 *
 * Actualización 2026-10-21 (Corrección de Redirección y Alertas en store y update):
 * Se corrigió la redirección en `store()` y `update()` para enviar al usuario a
 * `route('academico.unidad-curricular.index')` con el mensaje flash 'success', en lugar de `back()`,
 * resolviendo el bloqueo donde el usuario no salía del formulario tras guardar/editar y
 * no se visualizaba la notificación flash de éxito en el maestro.
 */
class UnidadCurricularController extends Controller
{
    /**
     * Valida los atributos permitidos de la tabla `unidad_curricular`.
     *
     * Historial:
     * - 2026-11-16: Añadida unicidad de `codigo_unc` filtrada por `deleted_at IS NULL` y
     *   validación de existencia activa en `tipo_unidad_curricular`.
     */
    private function validarUnidad(Request $request, ?int $ignoreId = null): array
    {
        $uniqueCodigo = Rule::unique('unidad_curricular', 'codigo_unc')->whereNull('deleted_at');
        if ($ignoreId !== null) {
            $uniqueCodigo->ignore($ignoreId, 'id_unc');
        }

        return $request->validate([
            'codigo_unc'          => ['nullable', 'string', 'max:30', $uniqueCodigo],
            'codigo_anterior_unc' => ['nullable', 'string', 'max:30'],
            'nombre_unc'          => ['required', 'string', 'max:255'],
            'tuc_id'              => ['required', 'integer', Rule::exists('tipo_unidad_curricular', 'id_tuc')->whereNull('deleted_at')],
            'dep_id'              => ['nullable', 'integer'],
        ], [
            'codigo_unc.unique' => 'Ya existe una unidad curricular activa con este código.',
        ]);
    }

    /**
     * Muestra la vista principal del Maestro de Unidades Curriculares con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);
        $filtros = array_merge(
            $request->only(['search', 'status', 'status_id', 'fecha_inicio', 'fecha_fin', 'per_page', 'registros_per_page']),
            ['per_page' => $perPage, 'registros_per_page' => $perPage]
        );
        $unidades = UnidadCurricular::obtenerPaginados($filtros, $perPage);
        $tipos = TipoUnidadCurricular::where('status_id', 1)->orderBy('nombre_tuc', 'asc')->get();

        return Inertia::render('EstructuraAcademica/Maestros/UnidadCurricular/index', [
            'unidades'     => $unidades,
            'perPage'      => $perPage,
            'tipos'        => $tipos,
            'tipos_unidad' => $tipos,
            'filters'      => $filtros,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva unidad curricular.
     */
    public function create(): Response
    {
        $tipos = TipoUnidadCurricular::where('status_id', 1)->orderBy('nombre_tuc', 'asc')->get();
        $trayectos = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra', 'asc')->get();

        return Inertia::render('EstructuraAcademica/Maestros/UnidadCurricular/create', [
            'tipos'        => $tipos,
            'tipos_unidad' => $tipos,
            'trayectos'    => $trayectos,
        ]);
    }

    /**
     * Almacena una nueva unidad curricular en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validarUnidad($request);

        try {
            $unidad = DB::transaction(function () use ($validated) {
                $codigo = ! empty($validated['codigo_unc']) ? trim($validated['codigo_unc']) : null;
                UnidadCurricular::liberarCodigoSiEliminado($codigo);

                return UnidadCurricular::create([
                    'codigo_unc'          => $codigo,
                    'codigo_anterior_unc' => ! empty($validated['codigo_anterior_unc']) ? trim($validated['codigo_anterior_unc']) : null,
                    'nombre_unc'          => trim($validated['nombre_unc']),
                    'tuc_id'              => $validated['tuc_id'],
                    'dep_id'              => $validated['dep_id'] ?? null,
                    'status_id'           => UnidadCurricular::STATUS_ACTIVO,
                    'created_id'          => auth()->id(),
                    'updated_id'          => auth()->id(),
                ]);
            });

            return redirect()
                ->route('academico.unidad-curricular.index')
                ->with('success', "La unidad curricular '{$unidad->nombre_unc}' ha sido registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la unidad curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el detalle de una unidad curricular específica junto a sus asignaciones en mallas y prelaciones.
     */
    public function show(int $id): Response
    {
        $unidad = UnidadCurricular::with([
            'tipoUnidadCurricular',
            'detallesMalla.trayecto',
            'prelacionesExigidas.unidadOrigen',
        ])->where('id_unc', $id)->firstOrFail();

        return Inertia::render('EstructuraAcademica/Maestros/UnidadCurricular/show', [
            'unidad' => $unidad,
        ]);
    }

    /**
     * Muestra el formulario para editar una unidad curricular.
     */
    public function edit(int $id): Response
    {
        $unidad = UnidadCurricular::with(['tipoUnidadCurricular'])
            ->where('id_unc', $id)
            ->firstOrFail();

        $tipos = TipoUnidadCurricular::where('status_id', 1)->orderBy('nombre_tuc', 'asc')->get();
        $trayectos = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra', 'asc')->get();

        return Inertia::render('EstructuraAcademica/Maestros/UnidadCurricular/edit', [
            'unidad'       => $unidad,
            'tipos'        => $tipos,
            'tipos_unidad' => $tipos,
            'trayectos'    => $trayectos,
        ]);
    }

    /**
     * Actualiza los datos de una unidad curricular existente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $this->validarUnidad($request, $id);

        try {
            $unidad = DB::transaction(function () use ($validated, $id) {
                $unidad = UnidadCurricular::where('id_unc', $id)->firstOrFail();
                $codigo = ! empty($validated['codigo_unc']) ? trim($validated['codigo_unc']) : null;
                UnidadCurricular::liberarCodigoSiEliminado($codigo, $unidad->id_unc);

                $unidad->update([
                    'codigo_unc'          => $codigo,
                    'codigo_anterior_unc' => ! empty($validated['codigo_anterior_unc']) ? trim($validated['codigo_anterior_unc']) : null,
                    'nombre_unc'          => trim($validated['nombre_unc']),
                    'tuc_id'              => $validated['tuc_id'],
                    'dep_id'              => $validated['dep_id'] ?? $unidad->dep_id,
                    'updated_id'          => auth()->id(),
                ]);

                return $unidad;
            });

            return redirect()
                ->route('academico.unidad-curricular.index')
                ->with('success', "La unidad curricular '{$unidad->nombre_unc}' ha sido actualizada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar la unidad curricular: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus lógico (`status_id` 1 <-> 2) sin eliminar de la interfaz.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        try {
            $unidad = UnidadCurricular::where('id_unc', $id)->firstOrFail();
            $nuevoEstado = UnidadCurricular::esStatusActivo($unidad)
                ? UnidadCurricular::STATUS_INACTIVO
                : UnidadCurricular::STATUS_ACTIVO;

            UnidadCurricular::alternarEstatus($id, $nuevoEstado);

            $estadoTexto = $nuevoEstado === UnidadCurricular::STATUS_ACTIVO ? 'activada' : 'inactivada';

            return redirect()
                ->back()
                ->with('success', "La unidad curricular '{$unidad->nombre_unc}' ha sido {$estadoTexto} exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo cambiar el estatus de la unidad curricular: ' . $e->getMessage()]);
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
            $unidad = UnidadCurricular::where('id_unc', $id)->firstOrFail();
            $nombre = $unidad->nombre_unc;

            UnidadCurricular::ejecutarBajaLogicaModelo($unidad, auth()->id(), UnidadCurricular::STATUS_INACTIVO);

            return redirect()
                ->back()
                ->with('success', "La unidad curricular '{$nombre}' ha sido eliminada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar la unidad curricular: ' . $e->getMessage()]);
        }
    }
}

