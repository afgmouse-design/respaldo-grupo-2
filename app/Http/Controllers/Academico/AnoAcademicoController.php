<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Models\Academico\AnoAcademico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Año Académico (Módulo: Estructura Académica).
 *
 * Entidad Padre.
 * Arquitectura: Skinny Controller delegando operaciones al modelo AnoAcademico.
 *
 * Actualización 2026-10-21 (Regla de Negocio: Único Año Activo y Eliminación Segura):
 * Se implementó la regla de negocio que garantiza que solo exista un (1) Año Académico
 * activo en todo el sistema.
 *
 * Actualización 2026-10-22 (Validación con Alerta de Activación en Modo Planificación):
 * Se agregó soporte para validación exigiendo cerrar el año anterior o permitir registrarlo
 * como desactivado, recibiendo 'cerrar_anterior' para cerrar el activo previo en cascada.
 *
 * Actualización 2026-10-21 (Lógica de Jerarquía: Eliminación en Cascada y Desactivación):
 * Se reescribió `destroy()` para implementar eliminación en cascada: Año -> Lapsos -> Cohortes.
 * Después de cohorte, las dependencias de `cohorte_sede` se desactivan preventivamente.
 * Se incorporó filtrado `whereNull('deleted_at')` en `store()` y `update()`.
 */
class AnoAcademicoController extends Controller
{
    /**
     * Muestra el listado de años académicos con búsqueda, filtro por estatus y paginación.
     */
    public function index(Request $request): Response
    {
        $busqueda = $request->input('search');
        $status = $request->input('status');
        $porPagina = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $anos = AnoAcademico::obtenerPaginados($busqueda, $porPagina, $status);

        return Inertia::render('Academico/AnoAcademico/index', [
            'anos'    => $anos,
            'filters' => [
                'search'   => $busqueda,
                'status'   => $status,
                'per_page' => $porPagina,
            ],
        ]);
    }

    /**
     * Muestra el formulario para registrar un nuevo año académico.
     */
    public function create(): Response
    {
        $anoActivo = DB::table('ano_academico')
            ->select(['id_ano', 'nombre_ano'])
            ->where('status', true)
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Academico/AnoAcademico/create', [
            'anoActivo' => $anoActivo,
        ]);
    }

    /**
     * Registra un nuevo año académico aplicando la regla "Único Año Académico Activo".
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_ano'       => 'required|string|max:100',
            'fecha_inicio_ano' => 'nullable|date',
            'fecha_fin_ano'    => 'nullable|date|after_or_equal:fecha_inicio_ano',
            'status'           => 'nullable',
        ]);

        $status = isset($datos['status']) ? filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN) : true;
        $cerrarAnterior = $request->boolean('cerrar_anterior');
        $otroActivo = DB::table('ano_academico')->where('status', true)->whereNull('deleted_at')->first();

        // Si se intenta registrar como activo y ya existe otro activo
        if ($status && $otroActivo) {
            if ($cerrarAnterior) {
                // Cerrar año anterior y desactivar sus hijos en cascada
                AnoAcademico::alternarEstatus($otroActivo->id_ano, 2);
                $this->desactivarHijosEnCascada($otroActivo->id_ano);
                $datos['status'] = true;
            } elseif ($request->boolean('guardar_desactivado')) {
                $datos['status'] = false;
            } else {
                return redirect()->back()
                    ->withErrors([
                        'status' => "Atención: Ya existe el año académico '{$otroActivo->nombre_ano}' activo. Debe cerrar el año anterior o registrarlo como desactivado."
                    ])
                    ->withInput();
            }
        } else {
            $datos['status'] = $status;
        }

        AnoAcademico::registrar($datos);

        return redirect()->route('academico.ano-academico.index')
            ->with('success', 'Año académico registrado exitosamente.');
    }

    /**
     * Muestra la ficha detallada de un año académico.
     */
    public function show($id): Response
    {
        $ano = AnoAcademico::obtenerPorId($id);

        return Inertia::render('Academico/AnoAcademico/show', [
            'ano' => $ano,
        ]);
    }

    /**
     * Muestra el formulario para editar un año académico existente.
     */
    public function edit($id): Response
    {
        $idActual = is_object($id) ? ($id->id_ano ?? $id->id) : $id;
        $ano = AnoAcademico::obtenerPorId($idActual);

        $anoActivo = DB::table('ano_academico')
            ->select(['id_ano', 'nombre_ano'])
            ->where('id_ano', '!=', $idActual)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Academico/AnoAcademico/edit', [
            'ano' => $ano,
            'anoActivo' => $anoActivo,
        ]);
    }

    /**
     * Actualiza un año académico existente aplicando la regla "Único Año Académico Activo".
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_ano'       => 'required|string|max:100',
            'fecha_inicio_ano' => 'nullable|date',
            'fecha_fin_ano'    => 'nullable|date|after_or_equal:fecha_inicio_ano',
            'status'           => 'nullable',
        ]);

        $idActual = is_object($id) ? ($id->id_ano ?? $id->id) : $id;
        $status = isset($datos['status']) ? filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN) : true;
        $cerrarAnterior = $request->boolean('cerrar_anterior');
        $otroActivo = DB::table('ano_academico')->where('id_ano', '!=', $idActual)->where('status', true)->whereNull('deleted_at')->first();

        // Si se intenta actualizar como activo y ya existe otro activo (excluyendo el actual)
        if ($status && $otroActivo) {
            if ($cerrarAnterior) {
                // Cerrar año anterior y desactivar sus hijos en cascada
                AnoAcademico::alternarEstatus($otroActivo->id_ano, 2);
                $this->desactivarHijosEnCascada($otroActivo->id_ano);
                $datos['status'] = true;
            } elseif ($request->boolean('guardar_desactivado')) {
                $datos['status'] = false;
            } else {
                return redirect()->back()
                    ->withErrors([
                        'status' => "Atención: Ya existe el año académico '{$otroActivo->nombre_ano}' activo. Debe cerrar el año anterior o registrarlo como desactivado."
                    ])
                    ->withInput();
            }
        } else {
            $datos['status'] = $status;
        }

        AnoAcademico::actualizar($idActual, $datos);

        // Si se actualizó a inactivo, propagar en cascada
        if (!$datos['status']) {
            $this->desactivarHijosEnCascada($idActual);
        }

        return redirect()->route('academico.ano-academico.index')
            ->with('success', 'Año académico actualizado exitosamente.');
    }

    /**
     * Alterna el estatus operativo del año académico delegando al modelo.
     */
    public function alternarEstatus(Request $request, $id): RedirectResponse
    {
        try {
            $nuevoEstatus = $request->has('status') ? $request->boolean('status') : null;

            $activo = AnoAcademico::alternarEstatus($id, $nuevoEstatus);

            $mensaje = $activo
                ? 'Año académico activado exitosamente.'
                : 'Año académico desactivado exitosamente.';

            return redirect()->back()->with('success', $mensaje);
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Elimina en cascada el año académico, sus lapsos dependientes y sus cohortes.
     * Las asignaciones subordinadas de cohorte por sede (después de cohorte) se desactivan.
     */
    public function destroy($id): RedirectResponse
    {
        $idActual = is_object($id) ? ($id->id_ano ?? $id->id) : $id;

        try {
            return DB::transaction(function () use ($idActual) {
                $ano = DB::table('ano_academico')
                    ->where('id_ano', $idActual)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$ano) {
                    return redirect()->back()->withErrors(['error' => 'El año académico que intenta eliminar no existe o ya fue eliminado.']);
                }

                $ahora = now();
                $userId = auth()->id();

                // 1. Obtener lapsos asociados
                $lapsoIds = DB::table('lapso_academico')
                    ->where('ano_id', $idActual)
                    ->whereNull('deleted_at')
                    ->pluck('id_la');

                if ($lapsoIds->isNotEmpty()) {
                    // 2. Obtener cohortes de esos lapsos
                    $cohorteIds = DB::table('cohorte')
                        ->whereIn('laa_id', $lapsoIds)
                        ->whereNull('deleted_at')
                        ->pluck('id_coh');

                    if ($cohorteIds->isNotEmpty()) {
                        // 3. Regla Jerarquía: Después de cohorte solo se desactivan las asignaciones por sede
                        DB::table('cohorte_sede')
                            ->whereIn('coh_id', $cohorteIds)
                            ->update([
                                'status'     => false,
                                'status_id'  => 2,
                                'updated_id' => $userId,
                                'updated_at' => $ahora,
                            ]);

                        // 4. Eliminación de las cohortes (soft delete)
                        DB::table('cohorte')
                            ->whereIn('id_coh', $cohorteIds)
                            ->update([
                                'deleted_at' => $ahora,
                                'status'     => false,
                                'status_id'  => 2,
                                'updated_id' => $userId,
                                'updated_at' => $ahora,
                            ]);
                    }

                    // 5. Desactivar secciones vinculadas a los lapsos
                    DB::table('seccion')
                        ->whereIn('laa_id', $lapsoIds)
                        ->update([
                            'status'     => false,
                            'status_id'  => 2,
                            'updated_id' => $userId,
                            'updated_at' => $ahora,
                        ]);

                    // 6. Eliminación de los lapsos académicos
                    DB::table('lapso_academico')
                        ->whereIn('id_la', $lapsoIds)
                        ->update([
                            'deleted_at' => $ahora,
                            'status'     => false,
                            'status_id'  => 2,
                            'estado_la'  => false,
                        ]);
                }

                // 7. Eliminación del año académico
                DB::table('ano_academico')
                    ->where('id_ano', $idActual)
                    ->update([
                        'deleted_at' => $ahora,
                        'status'     => false,
                    ]);

                return redirect()->back()->with('success', 'Año académico "' . $ano->nombre_ano . '" y sus dependencias (lapsos y cohortes) fueron eliminados correctamente.');
            });
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar el año académico: ' . $e->getMessage()]);
        }
    }

    /**
     * Desactiva en cascada todos los lapsos del año, sus cohortes y las cohortes por sede.
     */
    protected function desactivarHijosEnCascada($anoId): void
    {
        $lapsoIds = DB::table('lapso_academico')
            ->where('ano_id', $anoId)
            ->whereNull('deleted_at')
            ->pluck('id_la');

        if ($lapsoIds->isNotEmpty()) {
            DB::table('lapso_academico')->whereIn('id_la', $lapsoIds)->update([
                'status_id' => 2,
                'status'    => false,
                'estado_la' => false,
            ]);

            $cohorteIds = DB::table('cohorte')
                ->whereIn('laa_id', $lapsoIds)
                ->whereNull('deleted_at')
                ->pluck('id_coh');

            if ($cohorteIds->isNotEmpty()) {
                $userId = auth()->id();
                DB::table('cohorte')->whereIn('id_coh', $cohorteIds)->update([
                    'status_id'  => 2,
                    'status'     => false,
                    'updated_id' => $userId,
                    'updated_at' => now(),
                ]);

                DB::table('cohorte_sede')->whereIn('coh_id', $cohorteIds)->update([
                    'status_id'  => 2,
                    'status'     => false,
                    'updated_id' => $userId,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

