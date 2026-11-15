<?php

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Maestros\StoreTipoLapsoRequest;
use App\Http\Requests\ProgramacionAcademica\Maestros\UpdateTipoLapsoRequest;
use App\Models\ProgramacionAcademica\Maestros\TipoLapso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TipoLapsoController extends Controller
{
    /**
     * Display a listing of tipos de lapso with search, filter and pagination.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('registros_per_page', 5);

        $tipoLapsos = TipoLapso::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('tap_nombre', 'like', "%{$search}%")
                        ->orWhere('tap_codigo', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status !== 'all') {
                    $query->where('tap_estatus', $request->boolean('status'));
                }
            })
            ->when($request->filled('fecha_inicio'), function ($query) use ($request) {
                $end = $request->filled('fecha_fin') ? $request->fecha_fin : now();
                $query->whereBetween('created_at', [$request->fecha_inicio, $end]);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('ProgramacionAcademica/Maestros/TipoLapso/index', [
            'tipoLapsos' => $tipoLapsos,
            'filters' => $request->only(['search', 'status', 'fecha_inicio', 'fecha_fin']),
            'registros_per_page' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new tipo de lapso.
     */
    public function create()
    {
        return Inertia::render('ProgramacionAcademica/Maestros/TipoLapso/create');
    }

    /**
     * Store a newly created tipo de lapso in storage.
     */
    public function store(StoreTipoLapsoRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['tap_estatus'] = true;
            TipoLapso::create($validated);

            return redirect()
                ->route('tipo_lapso.index')
                ->with('success', 'Tipo de lapso creado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Ocurrió un error al crear el tipo de lapso. Inténtalo de nuevo.']);
        }
    }

    /**
     * Display the specified tipo de lapso.
     */
    public function show(TipoLapso $tipoLapso)
    {
        return Inertia::render('ProgramacionAcademica/Maestros/TipoLapso/show', [
            'tipoLapso' => $tipoLapso
        ]);
    }

    /**
     * Show the form for editing the specified tipo de lapso.
     */
    public function edit(TipoLapso $tipoLapso)
    {
        return Inertia::render('ProgramacionAcademica/Maestros/TipoLapso/edit', [
            'tipoLapso' => $tipoLapso,
        ]);
    }

    /**
     * Update the specified tipo de lapso in storage.
     */
    public function update(UpdateTipoLapsoRequest $request, TipoLapso $tipoLapso)
    {
        try {
            $validated = $request->validated();
            $tipoLapso->update($validated);

            return redirect()
                ->route('tipo_lapso.index')
                ->with('success', 'Tipo de lapso actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Ocurrió un error al actualizar el tipo de lapso. Inténtalo de nuevo.']);
        }
    }

    /**
     * Toggle the status (true/false).
     */
    public function destroy(TipoLapso $tipoLapso)
    {
        $tipoLapso->update([
            'tap_estatus' => !$tipoLapso->tap_estatus
        ]);

        $message = $tipoLapso->tap_estatus ? 'Tipo de lapso activado' : 'Tipo de lapso desactivado';
        return redirect()->back()->with('success', $message);
    }
}