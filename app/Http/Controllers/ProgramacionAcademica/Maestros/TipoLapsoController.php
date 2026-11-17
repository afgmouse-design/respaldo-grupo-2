<?php

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Maestros\StoreTipoLapsoRequest;
use App\Http\Requests\ProgramacionAcademica\Maestros\UpdateTipoLapsoRequest;
use App\Models\Academico\TipoLapso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Tipo de Lapso Académico (`tipo_lapso`).
 *
 * Propósito: Gestiona el catálogo maestro oficial de tipos de lapso (`id_tla`: Semestral,
 * Trimestral, Anual/Trayecto), unificando los flujos previamente divididos entre
 * `tipo_lapsos` (`tap_*`) y el eliminado `tipo_periodo`.
 *
 * Historial:
 *  - 2026-11-14: Migrado del esquema legacy `tipo_lapsos`/`tap_*` hacia `tipo_lapso(id_tla)`
 *    con `codigo_tla`, `nombre_tla`, `duracion_semanas_tla`, `nomenclatura_tla`,
 *    `descripcion_tla` y `status_id`.
 */
class TipoLapsoController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 5);
        $filtros = $request->only(['search', 'status', 'status_id', 'fecha_inicio', 'fecha_fin']);

        return Inertia::render('ProgramacionAcademica/Maestros/TipoLapso/index', [
            'tipoLapsos'         => TipoLapso::obtenerPaginados($filtros, $perPage),
            'filters'            => $filtros,
            'registros_per_page' => $perPage,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ProgramacionAcademica/Maestros/TipoLapso/create');
    }

    public function store(StoreTipoLapsoRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            DB::transaction(function () use ($validated) {
                TipoLapso::liberarCodigoSiEliminado($validated['codigo_tla'] ?? null);
                TipoLapso::create($validated + [
                    'status_id'  => TipoLapso::STATUS_ACTIVO,
                    'created_id' => auth()->id(),
                ]);
            });

            return redirect()
                ->route('tipo_lapso.index')
                ->with('success', 'Tipo de lapso creado correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Ocurrió un error al crear el tipo de lapso. Inténtalo de nuevo.']);
        }
    }

    public function show(int|string|TipoLapso $tipo_lapso): Response
    {
        return Inertia::render('ProgramacionAcademica/Maestros/TipoLapso/show', [
            'tipoLapso' => $this->resolverRegistro($tipo_lapso),
        ]);
    }

    public function edit(int|string|TipoLapso $tipo_lapso): Response
    {
        return Inertia::render('ProgramacionAcademica/Maestros/TipoLapso/edit', [
            'tipoLapso' => $this->resolverRegistro($tipo_lapso),
        ]);
    }

    public function update(UpdateTipoLapsoRequest $request, int|string|TipoLapso $tipo_lapso): RedirectResponse
    {
        $registro = $this->resolverRegistro($tipo_lapso);

        try {
            $validated = $request->validated();

            DB::transaction(function () use ($registro, $validated) {
                TipoLapso::liberarCodigoSiEliminado($validated['codigo_tla'] ?? null, $registro->id_tla);
                $registro->update($validated + ['updated_id' => auth()->id()]);
            });

            return redirect()
                ->route('tipo_lapso.index')
                ->with('success', 'Tipo de lapso actualizado correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Ocurrió un error al actualizar el tipo de lapso. Inténtalo de nuevo.']);
        }
    }

    /**
     * Si recibe `eliminar=1`, ejecuta baja lógica en interfaz (`deleted_at` + `status_id = 2`);
     * de lo contrario invierte `status_id` (`1` ↔ `2`).
     *
     * Historial:
     * - 2026-11-16: Separada la eliminación lógica (`eliminar=1`) del toggle de estado.
     */
    public function destroy(Request $request, int|string|TipoLapso $tipo_lapso): RedirectResponse
    {
        $registro = $this->resolverRegistro($tipo_lapso);

        if ($request->boolean('eliminar')) {
            TipoLapso::ejecutarBajaLogicaModelo($registro, auth()->id(), TipoLapso::STATUS_INACTIVO);

            return redirect()->back()->with('success', "El tipo de lapso '{$registro->nombre_tla}' ha sido eliminado correctamente.");
        }

        $activar = ! TipoLapso::esStatusActivo($registro);

        $registro->update([
            'status_id'  => $activar ? TipoLapso::STATUS_ACTIVO : TipoLapso::STATUS_INACTIVO,
            'updated_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', $activar
            ? 'Tipo de lapso activado.'
            : 'Tipo de lapso desactivado.');
    }

    private function resolverRegistro(int|string|TipoLapso $tipoLapso): TipoLapso
    {
        if ($tipoLapso instanceof TipoLapso) {
            return $tipoLapso;
        }

        return TipoLapso::query()->where('id_tla', (int) $tipoLapso)->firstOrFail();
    }
}
