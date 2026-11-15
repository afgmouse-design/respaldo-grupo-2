<?php

namespace App\Http\Controllers\EstructuraAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\EjeCurricular;
use App\Models\EstructuraAcademica\Maestros\EscalaMedicion;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro Detalle de Malla Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Gestiona la asignación y carga de materias dentro de las mallas curriculares,
 * vinculando Unidad Curricular, Trayecto, Escala de Medición y Eje Curricular.
 * Envía las colecciones activas (status_id = 1) en create y edit mediante compact().
 * Aplica baja lógica cambiando status_id = 2 en el método destroy.
 */
class MallaCurricularDetalleController extends Controller
{
    /**
     * Muestra el listado de materias asignadas a las mallas curriculares (status_id = 1).
     */
    public function index(Request $request): Response
    {
        $perPage = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $detalles = MallaCurricularDetalle::obtenerPaginados(
            $request->only(['search', 'mac_id', 'tra_id']),
            $perPage
        );

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricularDetalle/index', [
            'detalles' => $detalles,
            'perPage'  => $perPage,
            'filters'  => array_merge(
                $request->only(['search', 'mac_id', 'tra_id', 'per_page', 'registros_per_page']),
                ['per_page' => $perPage, 'registros_per_page' => $perPage]
            ),
        ]);
    }

    /**
     * Muestra el formulario para agregar una unidad curricular a una malla.
     * Carga obligatoriamente todos los selectores activos con status_id = 1.
     */
    public function create(): Response
    {
        $mallas = MallaCurricular::where('status_id', 1)->orderBy('nombre_mac', 'asc')->get();
        $unidades = UnidadCurricular::where('status_id', 1)->orderBy('nombre_unc', 'asc')->get();
        $trayectos = Trayecto::where('status_id', 1)->orderBy('id_tra', 'asc')->get();
        $escalas = EscalaMedicion::where('status_id', 1)->orderBy('nombre_esm', 'asc')->get();
        $ejes = EjeCurricular::where('status_id', 1)->orderBy('nombre_ecu', 'asc')->get();

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricularDetalle/create', compact(
            'mallas',
            'unidades',
            'trayectos',
            'escalas',
            'ejes'
        ));
    }

    /**
     * Almacena una nueva materia en la malla curricular.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mac_id'               => 'required|integer|exists:malla_curricular,id_mac',
            'unc_id'               => 'required|integer|exists:unidad_curricular,id_unc',
            'tra_id'               => 'required|integer|exists:trayecto,id_tra',
            'esm_id'               => 'nullable|integer|exists:escala_medicion,id_esm',
            'ecu_id'               => 'nullable|integer|exists:eje_curricular,id_ecu',
            'unidades_credito_mcd' => 'required|integer|min:0',
            'htea_mcd'             => 'nullable|integer|min:0',
            'htei_mcd'             => 'nullable|integer|min:0',
            'thte_mcd'             => 'nullable|integer|min:0',
            'duracion_semanas_mcd' => 'nullable|integer|min:1',
        ], [
            'mac_id.required'               => 'La malla curricular es obligatoria.',
            'unc_id.required'               => 'La unidad curricular es obligatoria.',
            'tra_id.required'               => 'El trayecto formativo es obligatorio.',
            'unidades_credito_mcd.required' => 'Las unidades de crédito son obligatorias.',
        ]);

        // Cálculo automático de thte si no viene especificado
        if (!isset($validated['thte_mcd']) || $validated['thte_mcd'] === null) {
            $htea = (int) ($validated['htea_mcd'] ?? 0);
            $htei = (int) ($validated['htei_mcd'] ?? 0);
            $validated['thte_mcd'] = $htea + $htei;
        }

        // Verificación de duplicado según el índice único compuesto (mac_id, unc_id, tra_id)
        $existe = MallaCurricularDetalle::where('mac_id', $validated['mac_id'])
            ->where('unc_id', $validated['unc_id'])
            ->where('tra_id', $validated['tra_id'])
            ->where('status_id', 1)
            ->exists();

        if ($existe) {
            return redirect()->back()->withErrors([
                'unc_id' => 'Esta unidad curricular ya se encuentra registrada en el trayecto seleccionado de esta malla.',
            ]);
        }

        $validated['status_id'] = 1;
        $validated['status'] = true;
        $validated['created_id'] = auth()->id();

        MallaCurricularDetalle::create($validated);

        return redirect()->route('academico.malla-curricular-detalle.index')
            ->with('success', 'Unidad curricular asignada a la malla exitosamente.');
    }

    /**
     * Muestra el detalle de una asignación curricular.
     */
    public function show(int $id): Response
    {
        $detalle = MallaCurricularDetalle::with([
            'mallaCurricular',
            'unidadCurricular',
            'trayecto',
            'ejeCurricular',
            'escalaMedicion',
        ])->findOrFail($id);

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricularDetalle/show', [
            'detalle' => $detalle,
        ]);
    }

    /**
     * Muestra el formulario para editar la materia en la malla curricular.
     * Carga obligatoriamente todos los selectores activos con status_id = 1.
     */
    public function edit(int $id): Response
    {
        $detalle = MallaCurricularDetalle::findOrFail($id);

        $mallas = MallaCurricular::where('status_id', 1)->orderBy('nombre_mac', 'asc')->get();
        $unidades = UnidadCurricular::where('status_id', 1)->orderBy('nombre_unc', 'asc')->get();
        $trayectos = Trayecto::where('status_id', 1)->orderBy('id_tra', 'asc')->get();
        $escalas = EscalaMedicion::where('status_id', 1)->orderBy('nombre_esm', 'asc')->get();
        $ejes = EjeCurricular::where('status_id', 1)->orderBy('nombre_ecu', 'asc')->get();

        return Inertia::render('EstructuraAcademica/Maestros/MallaCurricularDetalle/edit', compact(
            'detalle',
            'mallas',
            'unidades',
            'trayectos',
            'escalas',
            'ejes'
        ));
    }

    /**
     * Actualiza la configuración de la materia en la malla curricular.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $detalle = MallaCurricularDetalle::findOrFail($id);

        $validated = $request->validate([
            'mac_id'               => 'required|integer|exists:malla_curricular,id_mac',
            'unc_id'               => 'required|integer|exists:unidad_curricular,id_unc',
            'tra_id'               => 'required|integer|exists:trayecto,id_tra',
            'esm_id'               => 'nullable|integer|exists:escala_medicion,id_esm',
            'ecu_id'               => 'nullable|integer|exists:eje_curricular,id_ecu',
            'unidades_credito_mcd' => 'required|integer|min:0',
            'htea_mcd'             => 'nullable|integer|min:0',
            'htei_mcd'             => 'nullable|integer|min:0',
            'thte_mcd'             => 'nullable|integer|min:0',
            'duracion_semanas_mcd' => 'nullable|integer|min:1',
        ]);

        if (!isset($validated['thte_mcd']) || $validated['thte_mcd'] === null) {
            $htea = (int) ($validated['htea_mcd'] ?? 0);
            $htei = (int) ($validated['htei_mcd'] ?? 0);
            $validated['thte_mcd'] = $htea + $htei;
        }

        // Validar unicidad compuesta excluyendo el registro actual
        $existe = MallaCurricularDetalle::where('mac_id', $validated['mac_id'])
            ->where('unc_id', $validated['unc_id'])
            ->where('tra_id', $validated['tra_id'])
            ->where('status_id', 1)
            ->where('id_mcd', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()->back()->withErrors([
                'unc_id' => 'Esta unidad curricular ya se encuentra registrada en el trayecto de esta malla.',
            ]);
        }

        $validated['updated_id'] = auth()->id();

        $detalle->update($validated);

        return redirect()->route('academico.malla-curricular-detalle.index')
            ->with('success', 'Detalle de malla curricular actualizado exitosamente.');
    }

    /**
     * Aplica baja lógica al registro cambiando status_id = 2.
     */
    public function destroy(int $id): RedirectResponse
    {
        $exito = MallaCurricularDetalle::alternarEstatus($id, 2);

        if (!$exito) {
            return redirect()->back()->withErrors([
                'error' => 'No se pudo dar de baja a la unidad curricular de la malla.',
            ]);
        }

        return redirect()->back()->with('success', 'Unidad curricular dada de baja exitosamente de la malla.');
    }
}

