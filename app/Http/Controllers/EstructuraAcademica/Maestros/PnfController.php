<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\EscalaMedicion;
use App\Models\EstructuraAcademica\Maestros\ModalidadEstudio;
use App\Models\EstructuraAcademica\Maestros\Programa;
use App\Models\EstructuraAcademica\Maestros\RegimenEstudio;
use App\Models\EstructuraAcademica\Maestros\TipoPrograma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Programa Académico / PNF (tabla `programa`).
 *
 * Propósito: Gestiona el ciclo de vida CRUD de los programas de formación (`id_prg`)
 * sobre el esquema oficial `sogac_mio.sql`, en reemplazo de la tabla legacy `pnf`.
 *
 * Historial:
 *  - 2026-11-14: Migrado del modelo `Pnf` (`pnf`/`id_pnf`/`status`) a `Programa`
 *    (`programa`/`id_prg`/`status_id`), añadiendo autorización mediante `ProgramaPolicy`
 *    y catálogos (`tipo_programa`, `regimen_estudio`, `modalidad_estudio`, `escala_medicion`).
 */
class PnfController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Programa::class);

        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'status_id']);
        $programas = Programa::obtenerPaginados($filtros, $perPage);

        return Inertia::render('EstructuraAcademica/Maestros/Pnf/index', [
            'pnfs'      => $programas,
            'programas' => $programas,
            'perPage'   => $perPage,
            'filters'   => array_merge(
                $request->only(['search', 'status', 'status_id', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Programa::class);

        return Inertia::render('EstructuraAcademica/Maestros/Pnf/create', [
            'tiposPrograma'      => TipoPrograma::obtenerActivosParaSelect(),
            'regimenesEstudio'   => RegimenEstudio::obtenerActivosParaSelect(),
            'modalidadesEstudio' => ModalidadEstudio::obtenerActivosParaSelect(),
            'escalasMedicion'    => EscalaMedicion::obtenerActivosParaSelect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Programa::class);

        $validated = $this->validarPrograma($request);

        try {
            $programa = Programa::create($validated + [
                'pro_estatus' => 'A',
                'status_id'   => Programa::STATUS_ACTIVO,
                'created_id'  => auth()->id(),
            ]);

            return redirect()
                ->route('academico.pnf.index')
                ->with('success', "El programa '{$programa->nombre_prg}' ha sido registrado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al registrar el programa: ' . $e->getMessage()]);
        }
    }

    public function show(int $id): Response
    {
        $programa = Programa::with([
            'mallas',
            'tipoPrograma',
            'regimenEstudio',
            'modalidadEstudio',
            'escalaMedicion',
        ])->where('id_prg', $id)->firstOrFail();

        $this->authorize('view', $programa);

        return Inertia::render('EstructuraAcademica/Maestros/Pnf/show', [
            'pnf'      => $programa,
            'programa' => $programa,
        ]);
    }

    public function edit(int $id): Response
    {
        $programa = Programa::where('id_prg', $id)->firstOrFail();
        $this->authorize('update', $programa);

        return Inertia::render('EstructuraAcademica/Maestros/Pnf/edit', [
            'pnf'                => $programa,
            'programa'           => $programa,
            'tiposPrograma'      => TipoPrograma::obtenerActivosParaSelect(),
            'regimenesEstudio'   => RegimenEstudio::obtenerActivosParaSelect(),
            'modalidadesEstudio' => ModalidadEstudio::obtenerActivosParaSelect(),
            'escalasMedicion'    => EscalaMedicion::obtenerActivosParaSelect(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $programa = Programa::where('id_prg', $id)->firstOrFail();
        $this->authorize('update', $programa);

        $validated = $this->validarPrograma($request);

        try {
            $programa->update($validated + ['updated_id' => auth()->id()]);

            return redirect()
                ->route('academico.pnf.index')
                ->with('success', "El programa '{$programa->nombre_prg}' ha sido actualizado exitosamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado al actualizar el programa: ' . $e->getMessage()]);
        }
    }

    public function alternarEstatus(Request $request, int $id): RedirectResponse
    {
        $programa = Programa::where('id_prg', $id)->firstOrFail();
        $this->authorize('update', $programa);

        try {
            $nuevoEstatus = Programa::esStatusActivo($programa)
                ? Programa::STATUS_INACTIVO
                : Programa::STATUS_ACTIVO;

            Programa::alternarEstatus($id, $nuevoEstatus, auth()->id());

            $mensaje = $nuevoEstatus === Programa::STATUS_ACTIVO
                ? "El programa '{$programa->nombre_prg}' ha sido activado exitosamente."
                : "El programa '{$programa->nombre_prg}' y sus mallas curriculares asociadas han sido desactivados exitosamente.";

            return redirect()->back()->with('success', $mensaje);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo cambiar el estatus del programa: ' . $e->getMessage()]);
        }
    }

    /**
     * Ejecuta la eliminación lógica en interfaz (`deleted_at` + `status_id = 2`) del programa
     * y sus mallas curriculares asociadas, conservando el registro en PostgreSQL.
     *
     * Historial:
     * - 2026-11-16: Reemplazada la mera inactivación (`alternarEstatus`) por `eliminarConSoftDelete`.
     */
    public function destroy(int $id): RedirectResponse
    {
        $programa = Programa::where('id_prg', $id)->firstOrFail();
        $this->authorize('delete', $programa);

        try {
            $resultado = Programa::eliminarConSoftDelete($id, auth()->id());

            return redirect()
                ->back()
                ->with('success', "El programa '{$resultado['nombre']}' y sus mallas curriculares asociadas han sido eliminados correctamente.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo eliminar el programa: ' . $e->getMessage()]);
        }
    }

    /**
     * Normaliza alias `*_pnf` hacia `*_prg` y valida los campos de `programa`.
     *
     * @return array<string, mixed>
     */
    private function validarPrograma(Request $request): array
    {
        $request->merge([
            'codigo_prg' => $request->input('codigo_prg') ?? $request->input('codigo_pnf'),
            'nombre_prg' => $request->input('nombre_prg') ?? $request->input('nombre_pnf'),
        ]);

        $validated = $request->validate([
            'codigo_prg'            => ['nullable', 'string', 'max:30'],
            'codigo_ministerio_prg' => ['nullable', 'string', 'max:10'],
            'nombre_prg'            => ['required', 'string', 'max:255'],
            'siglas_prg'            => ['nullable', 'string', 'max:10'],
            'titulo_prg'            => ['nullable', 'string', 'max:255'],
            'intermedia_prg'        => ['nullable', 'string', 'max:1'],
            'tpg_id'                => ['nullable', 'integer', 'exists:tipo_programa,id_tpg'],
            'ree_id'                => ['nullable', 'integer', 'exists:regimen_estudio,id_ree'],
            'moe_id'                => ['nullable', 'integer', 'exists:modalidad_estudio,id_moe'],
            'esm_id'                => ['nullable', 'integer', 'exists:escala_medicion,id_esm'],
        ]);

        $validated['nombre_prg'] = trim((string) $validated['nombre_prg']);
        $validated['codigo_prg'] = ! empty($validated['codigo_prg']) ? trim((string) $validated['codigo_prg']) : null;
        $validated['codigo_ministerio_prg'] = ! empty($validated['codigo_ministerio_prg'])
            ? trim((string) $validated['codigo_ministerio_prg'])
            : null;
        $validated['siglas_prg'] = ! empty($validated['siglas_prg'])
            ? mb_strtoupper(trim((string) $validated['siglas_prg']), 'UTF-8')
            : null;
        $validated['titulo_prg'] = ! empty($validated['titulo_prg']) ? trim((string) $validated['titulo_prg']) : null;

        return $validated;
    }
}
