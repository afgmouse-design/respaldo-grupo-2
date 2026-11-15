<?php

namespace App\Http\Controllers\ProgramacionAcademica;

use App\Http\Controllers\Controller;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\SeccionUnidadDocente;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador de Operaciones Específicas de Secciones (SUD, Apertura, Cierre, Capacidad y Tipos).
 *
 * Propósito: Gestionar cada subflujo del ciclo de vida de las secciones de manera modular e independiente,
 * garantizando que cada ítem del menú posea su vista correspondiente.
 */
class SeccionOperacionesController extends Controller
{
    /**
     * Sección (SUD): Gestión de Sección - Unidad Curricular - Docente.
     */
    public function sudIndex(Request $request): Response
    {
        $search = $request->input('search');
        $seccionId = $request->input('seccion_id');

        $seccionesQuery = Seccion::query()
            ->with(['cohorteSede.cohorte', 'mallaCurricular'])
            ->where('status', true);

        if ($search) {
            $seccionesQuery->where(function ($q) use ($search) {
                $q->where('nombre_sec', 'ilike', "%{$search}%")
                  ->orWhere('codigo_sec', 'ilike', "%{$search}%");
            });
        }

        $secciones = $seccionesQuery->orderBy('nombre_sec')->get();

        $seccionSeleccionada = null;
        $asignaciones = [];
        $unidadesDisponibles = [];

        if ($seccionId) {
            $seccionSeleccionada = Seccion::with(['mallaCurricular.detalles.unidadCurricular'])->find($seccionId);
            if ($seccionSeleccionada) {
                $asignaciones = SeccionUnidadDocente::with(['mallaCurricularDetalle.unidadCurricular', 'docente', 'horarios.aula'])
                    ->where('sec_id', $seccionSeleccionada->id_sec)
                    ->get();

                if ($seccionSeleccionada->mallaCurricular) {
                    $unidadesDisponibles = $seccionSeleccionada->mallaCurricular->detalles ?? [];
                }
            }
        } elseif ($secciones->isNotEmpty()) {
            $seccionSeleccionada = Seccion::with(['mallaCurricular.detalles.unidadCurricular'])->find($secciones->first()->id_sec);
            if ($seccionSeleccionada) {
                $asignaciones = SeccionUnidadDocente::with(['mallaCurricularDetalle.unidadCurricular', 'docente', 'horarios.aula'])
                    ->where('sec_id', $seccionSeleccionada->id_sec)
                    ->get();

                if ($seccionSeleccionada->mallaCurricular) {
                    $unidadesDisponibles = $seccionSeleccionada->mallaCurricular->detalles ?? [];
                }
            }
        }

        $aulas = \App\Models\ProgramacionAcademica\Maestros\Aula::query()
            ->where('status', true)
            ->whereNull('deleted_at')
            ->select('id_aul', 'codigo_aula_aul', 'capacidad_maxima_aul', 'tipo_espacio_aul')
            ->orderBy('codigo_aula_aul')
            ->get();

        return Inertia::render('ProgramacionAcademica/SeccionUnidadDocente/index', [
            'secciones'           => $secciones,
            'seccionSeleccionada' => $seccionSeleccionada,
            'asignaciones'        => $asignaciones,
            'unidadesDisponibles' => $unidadesDisponibles,
            'aulas'               => $aulas,
            'filters'             => $request->only(['search', 'seccion_id']),
        ]);
    }

    /**
     * Tipo De Sección: Catálogo oficial desde la tabla PostgreSQL `tipo_seccion`.
     */
    public function tipoSeccionIndex(): Response
    {
        $registros = \App\Models\ProgramacionAcademica\Maestros\TipoSeccion::where('status_id', 1)
            ->orderBy('id_tis')
            ->get();

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
        $search = $request->input('search');

        $secciones = Seccion::query()
            ->with(['cohorteSede.cohorte', 'mallaCurricular'])
            ->when($search, function ($q, $search) {
                $q->where('nombre_sec', 'ilike', "%{$search}%")
                  ->orWhere('codigo_sec', 'ilike', "%{$search}%");
            })
            ->orderBy('status')
            ->orderBy('nombre_sec')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('ProgramacionAcademica/Seccion/AbrirSeccion', [
            'secciones' => $secciones,
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Cerrar Sección: Gestión de consolidación y cierre administrativo de secciones.
     */
    public function cerrarIndex(Request $request): Response
    {
        $search = $request->input('search');

        $secciones = Seccion::query()
            ->with(['cohorteSede.cohorte', 'mallaCurricular'])
            ->where('status', true)
            ->when($search, function ($q, $search) {
                $q->where('nombre_sec', 'ilike', "%{$search}%")
                  ->orWhere('codigo_sec', 'ilike', "%{$search}%");
            })
            ->orderBy('nombre_sec')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('ProgramacionAcademica/Seccion/CerrarSeccion', [
            'secciones' => $secciones,
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Acción para Abrir o Cerrar Sección (cambio de status).
     */
    public function cambiarEstadoSeccion(Request $request, int $id): RedirectResponse
    {
        $seccion = Seccion::findOrFail($id);
        $nuevoEstado = (bool) $request->input('status', !$seccion->status);

        $seccion->update([
            'status'    => $nuevoEstado,
            'status_id' => $nuevoEstado ? 1 : 2,
        ]);

        $accion = $nuevoEstado ? 'abierta y activada' : 'cerrada';
        return redirect()->back()->with('success', "La sección '{$seccion->nombre_sec}' ha sido {$accion} exitosamente.");
    }

    /**
     * Fusionar Secciones (pronto): Vista informativa y preparatoria.
     */
    public function fusionarIndex(): Response
    {
        return Inertia::render('ProgramacionAcademica/Seccion/FusionarSeccion');
    }

    /**
     * Cambiar Capacidad Por Sección: Ajuste masivo o puntual de cupos.
     */
    public function capacidadIndex(Request $request): Response
    {
        $search = $request->input('search');

        $secciones = Seccion::query()
            ->with(['cohorteSede.cohorte', 'mallaCurricular'])
            ->when($search, function ($q, $search) {
                $q->where('nombre_sec', 'ilike', "%{$search}%")
                  ->orWhere('codigo_sec', 'ilike', "%{$search}%");
            })
            ->orderBy('nombre_sec')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('ProgramacionAcademica/Seccion/CapacidadSeccion', [
            'secciones' => $secciones,
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
        ]);

        return redirect()->back()->with('success', "Capacidad de la sección '{$seccion->nombre_sec}' actualizada a {$validated['cupo_maximo_sec']} estudiantes.");
    }
}

