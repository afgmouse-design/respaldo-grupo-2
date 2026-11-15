<?php

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\SedePnf\StoreSedePnfRequest;
use App\Http\Requests\ProgramacionAcademica\SedePnf\UpdateSedePnfRequest;
use App\Models\ProgramacionAcademica\Maestros\SedePnf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro Sede PNF (oferta académica por sede, Programación Académica).
 *
 * Propósito: CRUD SPA con Inertia (crear/editar/ver en modales). Vincula sedes con los PNF
 * que imparten; estas ofertas alimentan el select de Cohorte Sede. Baja lógica vía `status`.
 */
class SedePnfController extends Controller
{
    /**
     * Listado paginado con filtros (search, status, sed_id, pnf_id) y catálogos para los selects.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SedePnf::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = $request->only(['search', 'status', 'sed_id', 'pnf_id']);

        return Inertia::render('ProgramacionAcademica/Maestros/SedePnf/index', [
            'sedesPnf' => SedePnf::obtenerPaginados($filtros, $perPage),
            'sedes'    => DB::table('sede')
                ->select('id_sed', 'nombre_sed')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_sed')
                ->get(),
            'pnfs'     => DB::table('pnf')
                ->select('id_pnf', 'nombre_pnf', 'codigo_pnf')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_pnf')
                ->get(),
            'filters'  => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SedePnf::class);

        return Inertia::render('ProgramacionAcademica/Maestros/SedePnf/create', [
            'sedes' => DB::table('sede')
                ->select('id_sed', 'nombre_sed')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_sed')
                ->get(),
            'pnfs'  => DB::table('pnf')
                ->select('id_pnf', 'nombre_pnf', 'codigo_pnf')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_pnf')
                ->get(),
        ]);
    }

    public function show(int $sede_pnf): Response
    {
        $registro = SedePnf::with(['sede', 'pnf'])->where('id_sep', $sede_pnf)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/SedePnf/show', [
            'sedePnf' => $registro,
        ]);
    }

    public function edit(int $sede_pnf): Response
    {
        $registro = SedePnf::where('id_sep', $sede_pnf)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/SedePnf/edit', [
            'sedePnf' => $registro,
            'sedes'   => DB::table('sede')
                ->select('id_sed', 'nombre_sed')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_sed')
                ->get(),
            'pnfs'    => DB::table('pnf')
                ->select('id_pnf', 'nombre_pnf', 'codigo_pnf')
                ->where('status', true)
                ->whereNull('deleted_at')
                ->orderBy('nombre_pnf')
                ->get(),
        ]);
    }

    /**
     * Registra una nueva oferta Sede PNF.
     */
    public function store(StoreSedePnfRequest $request): RedirectResponse
    {
        $this->authorize('create', SedePnf::class);

        try {
            SedePnf::create($request->validated() + [
                'status'     => true,
                'created_id' => auth()->id(),
            ]);

            return redirect()->route('sede_pnf.index')->with('success', 'Oferta Sede PNF registrada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al registrar la oferta Sede PNF.']);
        }
    }

    /**
     * Actualiza la sede y/o el PNF de una oferta existente.
     */
    public function update(UpdateSedePnfRequest $request, int $sede_pnf): RedirectResponse
    {
        $registro = SedePnf::where('id_sep', $sede_pnf)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $registro->update($request->validated() + ['updated_id' => auth()->id()]);

            return redirect()->route('sede_pnf.index')->with('success', 'Oferta Sede PNF actualizada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar la oferta Sede PNF.']);
        }
    }

    /**
     * No elimina: invierte el campo booleano `status` (activar/desactivar).
     */
    public function destroy(int $sede_pnf): RedirectResponse
    {
        $registro = SedePnf::where('id_sep', $sede_pnf)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            $nuevoEstado = !$registro->status;
            SedePnf::alternarEstatus($sede_pnf, $nuevoEstado);

            return redirect()->back()->with('success', $nuevoEstado
                ? 'Oferta Sede PNF activada correctamente.'
                : 'Oferta Sede PNF desactivada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'No se pudo alternar el estatus de la oferta Sede PNF.']);
        }
    }
}

