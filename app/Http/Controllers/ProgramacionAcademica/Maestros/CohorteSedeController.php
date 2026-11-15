<?php

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\CohorteSede\StoreCohorteSedeRequest;
use App\Http\Requests\ProgramacionAcademica\CohorteSede\UpdateCohorteSedeRequest;
use App\Models\ProgramacionAcademica\Maestros\CohorteSede;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Cohorte por Sede (Programación Académica).
 *
 * Propósito: CRUD SPA con Inertia; crear/editar/ver se resuelven mediante modales.
 * Provee selectores para la asignación de Cohorte y Oferta Académica (Sede-PNF).
 *
 * Actualización 2026-10-21 (Restauración de relación Oferta Sede-PNF):
 * Se restauró el envío del listado `ofertasSedePnf` al frontend y el filtro `sep_id`, resolviendo
 * la discrepancia con la tabla `cohorte_sede` y evitando el fallo al consultar `sed_id` inexistente.
 *
 * Actualización 2026-10-21 (Visualización Predeterminada de Todas las Ofertas y Filtros):
 * Se ajustó `index()` para pasar filtros por defecto 'all' para estatus, cohorte y oferta,
 * permitiendo listar todas las asignaciones registradas al entrar sin restringir indebidamente a activas.
 */
class CohorteSedeController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CohorteSede::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = [
            'search' => $request->input('search'),
            'status' => $request->input('status', 'all'),
            'coh_id' => $request->input('coh_id', 'all'),
            'sep_id' => $request->input('sep_id', 'all'),
        ];

        $cohortesSede = CohorteSede::obtenerPaginados($filtros, $perPage);

        $cohortes = DB::table('cohorte')
            ->select('id_coh', 'nombre_coh')
            ->whereNull('deleted_at')
            ->orderBy('nombre_coh', 'asc')
            ->get();

        // Obtiene las ofertas activas vinculando Sede y PNF (ej: "Sede Principal - Informática")
        $ofertasSedePnf = DB::table('sede_pnf')
            ->join('sede', 'sede.id_sed', '=', 'sede_pnf.sed_id')
            ->join('pnf', 'pnf.id_pnf', '=', 'sede_pnf.pnf_id')
            ->where('sede_pnf.status', true)
            ->whereNull('sede_pnf.deleted_at')
            ->select('sede_pnf.id_sep', DB::raw("sede.nombre_sed || ' - ' || pnf.nombre_pnf as descripcion_oferta"))
            ->orderBy('sede.nombre_sed', 'asc')
            ->orderBy('pnf.nombre_pnf', 'asc')
            ->get();

        return Inertia::render('ProgramacionAcademica/Maestros/CohorteSede/index', [
            'cohortesSede'   => $cohortesSede,
            'cohortes'       => $cohortes,
            'ofertasSedePnf' => $ofertasSedePnf,
            'filters'        => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', CohorteSede::class);

        $cohortes = DB::table('cohorte')
            ->select('id_coh', 'nombre_coh')
            ->whereNull('deleted_at')
            ->orderBy('nombre_coh', 'asc')
            ->get();

        $ofertasSedePnf = DB::table('sede_pnf')
            ->join('sede', 'sede.id_sed', '=', 'sede_pnf.sed_id')
            ->join('pnf', 'pnf.id_pnf', '=', 'sede_pnf.pnf_id')
            ->where('sede_pnf.status', true)
            ->whereNull('sede_pnf.deleted_at')
            ->select('sede_pnf.id_sep', DB::raw("sede.nombre_sed || ' - ' || pnf.nombre_pnf as descripcion_oferta"))
            ->orderBy('sede.nombre_sed', 'asc')
            ->orderBy('pnf.nombre_pnf', 'asc')
            ->get();

        return Inertia::render('ProgramacionAcademica/Maestros/CohorteSede/create', [
            'cohortes'       => $cohortes,
            'ofertasSedePnf' => $ofertasSedePnf,
        ]);
    }

    public function show(int $cohorte_sede): Response
    {
        $registro = CohorteSede::with(['cohorte', 'sedePnf.sede', 'sedePnf.pnf'])->where('id_cos', $cohorte_sede)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/CohorteSede/show', [
            'cohorteSede' => $registro,
        ]);
    }

    public function edit(int $cohorte_sede): Response
    {
        $registro = CohorteSede::where('id_cos', $cohorte_sede)->firstOrFail();
        $this->authorize('update', $registro);

        $cohortes = DB::table('cohorte')
            ->select('id_coh', 'nombre_coh')
            ->whereNull('deleted_at')
            ->orderBy('nombre_coh', 'asc')
            ->get();

        $ofertasSedePnf = DB::table('sede_pnf')
            ->join('sede', 'sede.id_sed', '=', 'sede_pnf.sed_id')
            ->join('pnf', 'pnf.id_pnf', '=', 'sede_pnf.pnf_id')
            ->where('sede_pnf.status', true)
            ->whereNull('sede_pnf.deleted_at')
            ->select('sede_pnf.id_sep', DB::raw("sede.nombre_sed || ' - ' || pnf.nombre_pnf as descripcion_oferta"))
            ->orderBy('sede.nombre_sed', 'asc')
            ->orderBy('pnf.nombre_pnf', 'asc')
            ->get();

        return Inertia::render('ProgramacionAcademica/Maestros/CohorteSede/edit', [
            'cohorteSede'    => $registro,
            'cohortes'       => $cohortes,
            'ofertasSedePnf' => $ofertasSedePnf,
        ]);
    }

    public function store(StoreCohorteSedeRequest $request): RedirectResponse
    {
        $this->authorize('create', CohorteSede::class);

        try {
            CohorteSede::create($request->validated() + [
                'status'     => true,
                'status_id'  => 1,
                'created_id' => auth()->id(),
            ]);

            return redirect()->route('cohorte_sede.index')->with('success', 'Cohorte por sede registrada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al registrar la cohorte por sede.']);
        }
    }

    public function update(UpdateCohorteSedeRequest $request, int $cohorte_sede): RedirectResponse
    {
        $registro = CohorteSede::where('id_cos', $cohorte_sede)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $data = $request->validated();
            // Regla de Negocio: No se permite modificar la cohorte padre durante la edición
            $data['coh_id'] = $registro->coh_id;

            $registro->update($data + ['updated_id' => auth()->id()]);

            return redirect()->route('cohorte_sede.index')->with('success', 'Cohorte por sede actualizada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar la cohorte por sede.']);
        }
    }

    public function destroy(int $cohorte_sede): RedirectResponse
    {
        $registro = CohorteSede::where('id_cos', $cohorte_sede)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            CohorteSede::alternarEstatus($cohorte_sede, $nuevoEstado);

            return redirect()->back()->with('success', $nuevoEstado === 1
                ? 'Cohorte por sede activada correctamente.'
                : 'Cohorte por sede desactivada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'No se pudo alternar el estatus de la cohorte por sede.']);
        }
    }
}
