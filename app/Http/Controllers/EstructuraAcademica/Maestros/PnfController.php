<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\Pnf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de PNF (Programa Nacional de Formación).
 *
 * Módulo: Estructura Académica / Oferta Académica.
 *
 * Propósito: Gestiona el ciclo de vida CRUD de los PNF mediante arquitectura SPA con Inertia.js.
 * Renderiza estrictamente las vistas bajo EstructuraAcademica/Maestros/Pnf/.
 *
 * Actualización:
 *  - Corrección estricta de rutas de vistas en Inertia::render() hacia 'EstructuraAcademica/Maestros/Pnf/...'.
 *  - Uso de asignación masiva Eloquent Pnf::create() y $pnf->update() con la propiedad $fillable configurada.
 */
class PnfController extends Controller
{
    /**
     * Muestra el listado de PNF activos (status_id = 1) con filtros reactivos y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        // Delegación de consulta al modelo con soporte para estado y búsqueda
        $pnfs = Pnf::obtenerPaginados(
            $request->only(['search', 'status']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/Pnf/index', [
            'pnfs'    => $pnfs,
            'perPage' => $perPage,
            'filters' => array_merge(
                $request->only(['search', 'status', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo PNF.
     */
    public function create(): Response
    {
        return Inertia::render('EstructuraAcademica/Maestros/Pnf/create');
    }

    /**
     * Almacena un nuevo PNF en la base de datos utilizando Eloquent y $fillable.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_pnf' => ['nullable', 'string', 'max:30'],
            'nombre_pnf' => ['required', 'string', 'max:255'],
        ]);

        try {
            Pnf::create([
                'codigo_pnf' => !empty($validated['codigo_pnf']) ? trim($validated['codigo_pnf']) : null,
                'nombre_pnf' => trim($validated['nombre_pnf']),
                'status_id'  => 1,
                'status'     => true,
            ]);

            return redirect()
                ->route('academico.pnf.index')
                ->with('success', "El PNF '{$validated['nombre_pnf']}' ha sido registrado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar el PNF: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el detalle de un PNF específico.
     */
    public function show(int $id): Response
    {
        $pnf = Pnf::with('mallas')->where('id_pnf', $id)->firstOrFail();

        return Inertia::render('EstructuraAcademica/Maestros/Pnf/show', [
            'pnf' => $pnf,
        ]);
    }

    /**
     * Muestra el formulario para editar un PNF.
     */
    public function edit(int $id): Response
    {
        $pnf = Pnf::where('id_pnf', $id)->firstOrFail();

        return Inertia::render('EstructuraAcademica/Maestros/Pnf/edit', [
            'pnf' => $pnf,
        ]);
    }

    /**
     * Actualiza los datos de un PNF existente utilizando Eloquent y $fillable.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_pnf' => ['nullable', 'string', 'max:30'],
            'nombre_pnf' => ['required', 'string', 'max:255'],
        ]);

        try {
            $pnf = Pnf::where('id_pnf', $id)->firstOrFail();

            $pnf->update([
                'codigo_pnf' => !empty($validated['codigo_pnf']) ? trim($validated['codigo_pnf']) : null,
                'nombre_pnf' => trim($validated['nombre_pnf']),
            ]);

            return redirect()
                ->route('academico.pnf.index')
                ->with('success', "El PNF '{$validated['nombre_pnf']}' ha sido actualizado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar el PNF: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus activo/inactivo del PNF. Si se inactiva, desactiva en cascada sus mallas curriculares.
     */
    public function alternarEstatus(Request $request, int $id): RedirectResponse
    {
        try {
            $pnf = Pnf::where('id_pnf', $id)->firstOrFail();
            $nuevoEstatus = ($pnf->status_id === 1 || $pnf->status) ? 2 : 1;
            $nombre = $pnf->nombre_pnf;

            Pnf::alternarEstatus($id, $nuevoEstatus);

            $mensaje = ($nuevoEstatus === 1)
                ? "El programa '{$nombre}' ha sido activado exitosamente."
                : "El programa '{$nombre}' y sus mallas curriculares asociadas han sido desactivados exitosamente.";

            return redirect()
                ->back()
                ->with('success', $mensaje);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo cambiar el estatus del programa: ' . $e->getMessage()]);
        }
    }

    /**
     * Realiza la baja lógica del PNF llamando a alternarEstatus($id, 2).
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $pnf = Pnf::where('id_pnf', $id)->first();
            $nombre = $pnf ? $pnf->nombre_pnf : 'PNF';

            Pnf::alternarEstatus($id, 2);

            return redirect()
                ->back()
                ->with('success', "El programa '{$nombre}' y sus mallas curriculares asociadas han sido desactivados exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo dar de baja el PNF: ' . $e->getMessage()]);
        }
    }
}
