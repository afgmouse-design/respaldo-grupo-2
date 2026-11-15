<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\EstructuraAcademica\Maestros\StoreSemestreRequest;
use App\Http\Requests\EstructuraAcademica\Maestros\UpdateSemestreRequest;
use App\Models\EstructuraAcademica\Maestros\Semestres;
use App\Models\EstructuraAcademica\Maestros\TipoPeriodo;
use Illuminate\Http\Request;
use Inertia\Inertia;


class SemestresController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->integer('registros_per_page', $request->integer('per_page', 10));

        $tiposPeriodo = TipoPeriodo::obtenerPaginados($request->only(['search']), $perPage);

        return Inertia::render('Academico/TipoPeriodo/index', [
            'tiposPeriodo' => $tiposPeriodo,
            'records' => $tiposPeriodo,
            'semestres' => $tiposPeriodo,
            'filters' => $request->only(['search', 'status', 'fecha_inicio', 'fecha_fin']),
            'registros_per_page' => $perPage,
        ]);
    }

    public function create()
    {
        return Inertia::render('Academico/TipoPeriodo/create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre_tpd' => 'required|string|max:100|unique:tipo_periodo,nombre_tpd',
                'codigo_tpd' => 'nullable|string|max:30',
                'nomenclatura' => 'nullable|string|max:50',
                'duracion_semanas_tpd' => 'nullable|integer|min:1|max:100',
                'descripcion_tpd' => 'nullable|string',
                'status' => 'nullable|boolean',
            ]);

            if (!isset($validated['status'])) {
                $validated['status'] = true;
            }
            $validated['status_id'] = $validated['status'] ? 1 : 2;

            TipoPeriodo::create($validated);

            return redirect()
                ->route('academico.tipo-periodo.index')
                ->with('success', 'Tipo de período creado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Ocurrió un error al crear el tipo de período. Inténtalo de nuevo.']);
        }
    }

    public function show($id)
    {
        $tipoPeriodo = TipoPeriodo::find($id);
        if ($tipoPeriodo) {
            return Inertia::render('Academico/TipoPeriodo/show', [
                'tipoPeriodo' => $tipoPeriodo,
            ]);
        }

        $semestre = Semestres::findOrFail($id);
        return Inertia::render('semestres/show', [
            'semestre' => $semestre,
        ]);
    }

    public function edit($id)
    {
        $tipoPeriodo = TipoPeriodo::find($id);
        if ($tipoPeriodo) {
            return Inertia::render('Academico/TipoPeriodo/edit', [
                'tipoPeriodo' => $tipoPeriodo,
            ]);
        }

        $semestre = Semestres::findOrFail($id);
        return Inertia::render('semestres/edit', [
            'semestre' => $semestre,
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $tipoPeriodo = TipoPeriodo::find($id);
            if ($tipoPeriodo) {
                $validated = $request->validate([
                    'nombre_tpd' => 'required|string|max:100|unique:tipo_periodo,nombre_tpd,' . $id . ',id_tpd',
                    'codigo_tpd' => 'nullable|string|max:30',
                    'nomenclatura' => 'nullable|string|max:50',
                    'duracion_semanas_tpd' => 'nullable|integer|min:1|max:100',
                    'descripcion_tpd' => 'nullable|string',
                    'status' => 'nullable|boolean',
                ]);

                if (isset($validated['status'])) {
                    $validated['status_id'] = $validated['status'] ? 1 : 2;
                }

                $tipoPeriodo->update($validated);

                return redirect()
                    ->route('academico.tipo-periodo.index')
                    ->with('success', 'Tipo de período actualizado correctamente.');
            }

            $semestre = Semestres::findOrFail($id);
            $semestre->update($request->all());

            return redirect()
                ->back()
                ->with('success', 'Semestre actualizado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Ocurrió un error al actualizar el registro. Inténtalo de nuevo.']);
        }
    }

    public function destroy($id)
    {
        $tipo = TipoPeriodo::find($id);
        if ($tipo) {
            $tipo->update([
                'status' => !$tipo->status,
                'status_id' => !$tipo->status ? 1 : 2,
            ]);
            $message = $tipo->status ? 'Tipo de período activado correctamente.' : 'Tipo de período desactivado correctamente.';
            return redirect()->back()->with('success', $message);
        }

        $semestre = Semestres::find($id);
        if ($semestre) {
            $semestre->update([
                'status' => !$semestre->status
            ]);
            $message = $semestre->status ? 'Tipo de período activado correctamente.' : 'Tipo de período desactivado correctamente.';
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->withErrors(['error' => 'Registro no encontrado.']);
    }
}