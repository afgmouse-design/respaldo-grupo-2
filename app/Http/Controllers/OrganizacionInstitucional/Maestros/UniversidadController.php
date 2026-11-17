<?php

namespace App\Http\Controllers\OrganizacionInstitucional\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizacionInstitucional\Maestros\StoreUniversidadRequest;
use App\Http\Requests\OrganizacionInstitucional\Maestros\UpdateUniversidadRequest;
use App\Models\EstructuraGeografica\Maestros\Estado;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\OrganizacionInstitucional\Maestros\CondicionSede;
use App\Models\OrganizacionInstitucional\Maestros\DependenciaInstitucion;
use App\Models\OrganizacionInstitucional\Maestros\Universidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Universidad (Módulo 2 - Organización Institucional).
 *
 * Arquitectura: Controlador Flaco (Skinny Controller).
 * No contiene consultas SQL, ni cláusulas where, ni llamadas directas a base de datos.
 * Delega toda la lógica de persistencia y consultas a los modelos Universidad y Localidad.
 *
 * Historial de Modificaciones:
 * - [Ajuste de Flujo de Navegación]: Se adaptó index() para redirigir directamente al formulario
 *   correspondiente (create si no existe la institución matriz, o edit si ya existe), eliminando
 *   la pantalla intermedia de listado vacío.
 * - [Corrección de Autorización 403]: Se cambió la autorización en show, edit, update y toggleStatus
 *   a Universidad::class en lugar de pasar la instancia stdClass, permitiendo a Gate resolver la Policy correctamente.
 */
class UniversidadController extends Controller
{
    /**
     * Muestra el formulario de la institución matriz o el listado si se solicita expresamente.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $this->authorize('viewAny', Universidad::class);

        // Si se solicita expresamente visualizar el formato de tabla/listado (?tabla=1)
        if ($request->boolean('tabla')) {
            $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 5);

            // Delegación de consulta al modelo
            $universidades = Universidad::obtenerPaginados(
                $request->only(['search', 'status']),
                $perPage
            );

            return Inertia::render('OrganizacionInstitucional/Universidad/index', [
                'universidades' => $universidades,
                'perPage'       => $perPage,
                'filters'       => array_merge(
                    $request->only(['search', 'status', 'per_page', 'registros_per_page']),
                    ['per_page' => $perPage, 'registros_per_page' => $perPage]
                ),
            ]);
        }

        $universidad = Universidad::obtenerRegistroMatriz();

        // Si aún no existe la institución universitaria matriz, enviar directamente al formulario de creación
        if (! $universidad) {
            return redirect()->route('organizacion.universidad.create');
        }

        // Si ya existe la universidad, enviar directamente al formulario de edición
        return redirect()->route('organizacion.universidad.edit', $universidad->id_uni);
    }

    /**
     * Muestra la página con el formulario para registrar una nueva universidad.
     */
    public function create(): Response
    {
        $this->authorize('create', Universidad::class);

        return Inertia::render('OrganizacionInstitucional/Universidad/create', [
            'localidades'  => Localidad::obtenerActivosConJerarquia(),
            'condiciones'  => CondicionSede::obtenerActivosParaSelect(),
            'dependencias' => DependenciaInstitucion::obtenerActivosParaSelect(),
            'estados'      => Estado::obtenerActivosParaSelect(),
        ]);
    }

    /**
     * Almacena una nueva universidad en la base de datos delegando al modelo.
     */
    public function store(StoreUniversidadRequest $request): RedirectResponse
    {
        $this->authorize('create', Universidad::class);

        try {
            // Delegación del insert al modelo con registrarUniversidad()
            $universidad = Universidad::registrarUniversidad($request->validated(), auth()->id());

            return redirect()
                ->route('organizacion.universidad.edit', $universidad->id_uni)
                ->with('success', "La universidad '{$universidad->nombre_uni}' ha sido registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar la universidad: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra la ficha detallada de una universidad.
     */
    public function show(int $id_uni): Response
    {
        $this->authorize('view', Universidad::class);

        $universidad = Universidad::obtenerConRelaciones($id_uni);

        return Inertia::render('OrganizacionInstitucional/Universidad/show', [
            'universidad' => $universidad,
        ]);
    }

    /**
     * Muestra la página con el formulario precargado para editar una universidad.
     */
    public function edit(int $id_uni): Response
    {
        $this->authorize('update', Universidad::class);

        $universidad = Universidad::obtenerParaEditar($id_uni);

        return Inertia::render('OrganizacionInstitucional/Universidad/edit', [
            'universidad'  => $universidad,
            'localidades'  => Localidad::obtenerActivosConJerarquia(),
            'condiciones'  => CondicionSede::obtenerActivosParaSelect(),
            'dependencias' => DependenciaInstitucion::obtenerActivosParaSelect(),
            'estados'      => Estado::obtenerActivosParaSelect(),
        ]);
    }

    /**
     * Actualiza los datos de una universidad en la base de datos delegando al modelo.
     */
    public function update(UpdateUniversidadRequest $request, int $id_uni): RedirectResponse
    {
        $this->authorize('update', Universidad::class);

        $universidadPrev = Universidad::obtenerPorId($id_uni);

        try {
            // Delegación del update al modelo con actualizarUniversidad()
            $universidad = Universidad::actualizarUniversidad($id_uni, $request->validated(), auth()->id());

            return redirect()
                ->route('organizacion.universidad.edit', $id_uni)
                ->with('success', "La universidad '{$universidad->nombre_uni}' ha sido actualizada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar la universidad: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus lógico de la universidad (Activo / Inactivo).
     */
    public function toggleStatus(int $id_uni): RedirectResponse
    {
        $this->authorize('delete', Universidad::class);

        $universidad = Universidad::obtenerPorId($id_uni);

        try {
            $resultado = Universidad::alternarEstatusConCascada($id_uni, auth()->id());

            $estadoTexto = $resultado['estatus'] ? 'activada' : 'inactivada';

            return redirect()
                ->back()
                ->with('success', "La universidad '{$resultado['nombre']}' ha sido {$estadoTexto} exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo modificar el estatus de la universidad.']);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`)
     * propagando la baja a sus sedes asociadas sin borrar registros de PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Desacoplado de `toggleStatus()` para realizar baja lógica real en interfaz.
     */
    public function destroy(int $id_uni): RedirectResponse
    {
        $this->authorize('delete', Universidad::class);

        try {
            $resultado = Universidad::eliminarConCascada($id_uni, auth()->id());

            return redirect()
                ->back()
                ->with('success', "La universidad '{$resultado['nombre']}' y sus sedes asociadas han sido eliminadas exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar la universidad y sus sedes asociadas.']);
        }
    }
}
