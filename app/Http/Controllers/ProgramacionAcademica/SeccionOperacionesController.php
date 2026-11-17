<?php

/**
 * Propósito: Controlador de Operaciones Específicas de Secciones (Carga Académica, Apertura, Cierre, Capacidad y Tipos).
 * Historial: Actualizado para consultar `CargaAcademica` (`pep_id`), `TipoSeccion`, `Aula`, `PersonaPerfil::obtenerDocentesActivosParaSelect()` y utilizar exclusivamente `status_id` (`1`/`2`).
 */

namespace App\Http\Controllers\ProgramacionAcademica;

use App\Http\Controllers\Controller;
use App\Models\Personas\PersonaPerfil;
use App\Models\ProgramacionAcademica\CargaAcademica;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\Maestros\TipoSeccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeccionOperacionesController extends Controller
{
    /**
     * Carga secciones paginadas aplicando búsqueda por nombre o código y filtro opcional de estado.
     */
    private function buscarSeccionesPaginadas(Request $request, ?int $statusId = null, int $perPage = 15)
    {
        $search = $request->input('search');

        return Seccion::query()
            ->with(['cohorteSede.cohorte', 'mallaCurricular', 'tipoSeccion', 'turno'])
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId))
            ->when($search, function ($q, string $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nombre_sec', 'ilike', "%{$search}%")
                        ->orWhere('codigo_sec', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('status_id')
            ->orderBy('nombre_sec')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Gestión de Carga Académica por Sección (Unidad Curricular + Docente por Perfil).
     */
    public function sudIndex(Request $request): Response
    {
        $search = $request->input('search');
        $seccionId = $request->input('seccion_id');

        $secciones = Seccion::query()
            ->with(['cohorteSede.cohorte', 'mallaCurricular'])
            ->where('status_id', 1)
            ->when($search, function ($q, string $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nombre_sec', 'ilike', "%{$search}%")
                        ->orWhere('codigo_sec', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('nombre_sec')
            ->get();

        $objetivoId = $seccionId ?: ($secciones->first()?->id_sec);
        $seccionSeleccionada = $objetivoId
            ? Seccion::with(['mallaCurricular.detalles.unidadCurricular'])->find($objetivoId)
            : null;

        $asignaciones = [];
        $unidadesDisponibles = [];
        if ($seccionSeleccionada) {
            $asignaciones = CargaAcademica::with([
                'unidadCurricular',
                'docentePerfil.persona',
                'horarios' => fn ($q) => $q->where('status_id', 1),
                'horarios.aula',
            ])
                ->where('sec_id', $seccionSeleccionada->id_sec)
                ->where('status_id', 1)
                ->get();

            if ($seccionSeleccionada->mallaCurricular) {
                $unidadesDisponibles = $seccionSeleccionada->mallaCurricular->detalles ?? [];
            }
        }

        $aulas = Aula::query()
            ->where('status_id', 1)
            ->select('id_aul', 'codigo_aula_aul', 'capacidad_maxima_aul', 'tipo_espacio_aul')
            ->orderBy('codigo_aula_aul')
            ->get();

        return Inertia::render('ProgramacionAcademica/SeccionUnidadDocente/index', [
            'secciones'           => $secciones,
            'seccionSeleccionada' => $seccionSeleccionada,
            'asignaciones'        => $asignaciones,
            'unidadesDisponibles' => $unidadesDisponibles,
            'aulas'               => $aulas,
            'docentes'            => PersonaPerfil::obtenerDocentesActivosParaSelect(),
            'filters'             => $request->only(['search', 'seccion_id']),
        ]);
    }

    /**
     * Tipo De Sección: Catálogo oficial desde `tipo_seccion`.
     */
    public function tipoSeccionIndex(): Response
    {
        $registros = TipoSeccion::where('status_id', 1)->orderBy('id_tis')->get();

        $tipos = $registros->map(function ($item) {
            return [
                'id_tis'        => $item->id_tis,
                'codigo'        => $item->codigo_tis,
                'nombre'        => $item->nombre_tis,
                'descripcion'   => $item->descripcion_tis,
                'cupo_sugerido' => $item->codigo_tis === 'PER' ? 20 : 35,
                'permite_per'   => $item->codigo_tis === 'PER',
                'color'         => 'blue',
            ];
        });

        return Inertia::render('ProgramacionAcademica/Seccion/TipoSeccion', [
            'tipos' => $tipos,
        ]);
    }

    /**
     * Abrir Sección: Gestión de habilitación y activación de secciones.
     */
    public function abrirIndex(Request $request): Response
    {
        return Inertia::render('ProgramacionAcademica/Seccion/AbrirSeccion', [
            'secciones' => $this->buscarSeccionesPaginadas($request),
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Cerrar Sección: Gestión de consolidación y cierre administrativo de secciones activas.
     */
    public function cerrarIndex(Request $request): Response
    {
        return Inertia::render('ProgramacionAcademica/Seccion/CerrarSeccion', [
            'secciones' => $this->buscarSeccionesPaginadas($request, 1),
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Alterna apertura/cierre de la sección actualizando `status_id` (`1` Activa / `2` Inactiva).
     */
    public function cambiarEstadoSeccion(Request $request, int $id): RedirectResponse
    {
        $seccion = Seccion::findOrFail($id);

        if ($request->filled('status_id')) {
            $nuevoStatusId = (int) $request->input('status_id') === 1 ? 1 : 2;
        } elseif ($request->has('status')) {
            $nuevoStatusId = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN) ? 1 : 2;
        } else {
            $nuevoStatusId = $seccion->status_id === 1 ? 2 : 1;
        }

        $seccion->update([
            'status_id'  => $nuevoStatusId,
            'updated_id' => auth()->id(),
        ]);

        $accion = $nuevoStatusId === 1 ? 'abierta y activada' : 'cerrada';

        return redirect()->back()->with('success', "La sección '{$seccion->nombre_sec}' ha sido {$accion} exitosamente.");
    }

    /**
     * Fusionar Secciones: Vista informativa y preparatoria.
     */
    public function fusionarIndex(): Response
    {
        return Inertia::render('ProgramacionAcademica/Seccion/FusionarSeccion');
    }

    /**
     * Cambiar Capacidad Por Sección: Ajuste puntual de cupos.
     */
    public function capacidadIndex(Request $request): Response
    {
        return Inertia::render('ProgramacionAcademica/Seccion/CapacidadSeccion', [
            'secciones' => $this->buscarSeccionesPaginadas($request, null, 20),
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Actualizar Cupo / Capacidad de Sección.
     */
    public function actualizarCapacidad(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'cupo_maximo_sec' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        $seccion = Seccion::findOrFail($id);
        $seccion->update([
            'cupo_maximo_sec' => $validated['cupo_maximo_sec'],
            'updated_id'      => auth()->id(),
        ]);

        return redirect()->back()->with('success', "Capacidad de la sección '{$seccion->nombre_sec}' actualizada a {$validated['cupo_maximo_sec']} estudiantes.");
    }
}
