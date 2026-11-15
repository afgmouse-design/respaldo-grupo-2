<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Maestros\StoreLapsoAcademicoRequest;
use App\Http\Requests\ProgramacionAcademica\Maestros\UpdateLapsoAcademicoRequest;
use App\Models\Academico\AnoAcademico;
use App\Models\Academico\LapsoAcademico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Lapso Académico (Módulo: Estructura Académica).
 *
 * Entidad Hija (pertenece a Año Académico).
 * Arquitectura: Skinny Controller delegando operaciones al modelo LapsoAcademico y AnoAcademico.
 *
 * Actualización 2026-10-21 (Regla de Negocio: Único Lapso Activo y Persistencia Eloquent):
 * Se implementó la validación estricta en los métodos `store` y `update` para garantizar
 * que solo exista un (1) lapso académico activo simultáneamente en todo el sistema.
 *
 * Actualización 2026-10-22 (Validación con Alerta de Activación en Modo Planificación):
 * Se agregó soporte para validación exigiendo cerrar el período anterior o permitir registrarlo
 * como desactivado, recibiendo 'cerrar_anterior' para cerrar el activo previo en cascada.
 *
 * Actualización 2026-10-21 (Corrección de Eliminación Efectiva en destroy):
 * Se corrigió el método `destroy()` para ejecutar la eliminación del registro (`delete()`)
 * cuando no tenga dependencias académicas (cohortes o secciones). Anteriormente ejecutaba
 * únicamente `alternarEstatus($id, 2)`, lo que causaba que registros ya inactivos no se
 * modificaran ni desaparecieran de la interfaz tras confirmar el diálogo de eliminación.
 *
 * Actualización 2026-10-21 (Protección de Padre Inmutable en update):
 * Se forzó que en `update()` el campo `ano_id` conserve estrictamente su valor original,
 * impidiendo reasignar el año académico padre a un lapso lectivo existente.
 *
 * Actualización 2026-10-21 (Lógica de Jerarquía: Eliminación en Cascada y Desactivación):
 * Se reescribió `destroy()` para implementar eliminación en cascada: Lapso -> Cohortes.
 * Después de cohorte, las asignaciones subordinadas en `cohorte_sede` se desactivan preventivamente.
 * Se incorporó `whereNull('deleted_at')` en las validaciones de colisión de `store()` y `update()`.
 */
class LapsoAcademicoController extends Controller
{
    /**
     * Muestra el listado de lapsos académicos con búsqueda, filtro por año, filtro por estatus y paginación.
     */
    public function index(Request $request): Response
    {
        $busqueda = $request->input('search');
        $anoId = $request->input('ano_id');
        $estadoLa = $request->input('estado_la');
        $porPagina = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        $lapsos = LapsoAcademico::obtenerPaginados($busqueda, $porPagina, $anoId, $estadoLa);
        $anosSelect = AnoAcademico::obtenerActivosParaSelect();

        return Inertia::render('Academico/LapsoAcademico/index', [
            'lapsos'     => $lapsos,
            'anosSelect' => $anosSelect,
            'filters'    => [
                'search'    => $busqueda,
                'ano_id'    => $anoId,
                'estado_la' => $estadoLa,
                'per_page'  => $porPagina,
            ],
        ]);
    }

    /**
     * Muestra la vista para crear un nuevo lapso académico con el catálogo de años activos.
     */
    public function create(): Response
    {
        $anosSelect = AnoAcademico::obtenerActivosParaSelect();
        $lapsoActivo = DB::table('lapso_academico')
            ->select(['id_la', 'nombre_la'])
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Academico/LapsoAcademico/create', [
            'anosSelect'  => $anosSelect,
            'lapsoActivo' => $lapsoActivo,
        ]);
    }

    /**
     * Registra un nuevo lapso académico.
     * Aplica la regla de negocio "Único Lapso Activo": solo puede existir un (1) lapso activo a la vez.
     */
    public function store(StoreLapsoAcademicoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Determinar status_id (1 = Activo, 2 = Inactivo) con soporte estricto para valores booleanos y strings
        if (isset($data['status_id'])) {
            $statusId = (int) $data['status_id'];
        } elseif (array_key_exists('estado_la', $data) && $data['estado_la'] !== null) {
            $statusId = filter_var($data['estado_la'], FILTER_VALIDATE_BOOLEAN) ? 1 : 2;
        } else {
            $statusId = 1;
        }

        $data['status_id'] = $statusId;
        $data['estado_la'] = ($statusId === 1);
        $data['status']    = ($statusId === 1);

        $cerrarAnterior = $request->boolean('cerrar_anterior');
        $otroActivo = LapsoAcademico::where('status_id', 1)->whereNull('deleted_at')->first();

        // Si el usuario lo manda como Activo y ya existe otro Activo
        if ($data['status_id'] == 1 && $otroActivo) {
            if ($cerrarAnterior) {
                // Cerrar lapso previo y desactivar hijos en cascada
                LapsoAcademico::alternarEstatus($otroActivo->id_la, 2);
                $this->desactivarHijosEnCascada($otroActivo->id_la);
                $data['status_id'] = 1;
                $data['estado_la'] = true;
                $data['status']    = true;
            } elseif ($request->boolean('guardar_desactivado')) {
                $data['status_id'] = 2;
                $data['estado_la'] = false;
                $data['status']    = false;
            } else {
                return redirect()->back()
                    ->withErrors([
                        'status_id' => "Atención: Ya existe el lapso académico '{$otroActivo->nombre_la}' activo. Debe cerrar el periodo anterior o registrarlo como desactivado."
                    ])
                    ->withInput();
            }
        }

        // Flujo normal de persistencia
        LapsoAcademico::create($data);

        return redirect()->route('lapso.index')
            ->with('success', 'Lapso creado exitosamente.');
    }

    /**
     * Muestra los detalles de un lapso académico.
     */
    public function show($id): Response
    {
        $lapso = LapsoAcademico::obtenerPorId($id);

        return Inertia::render('Academico/LapsoAcademico/show', [
            'lapso' => $lapso,
        ]);
    }

    /**
     * Muestra la vista de edición con los datos del lapso y catálogo de años activos.
     */
    public function edit($id): Response
    {
        $idActual = is_object($id) ? ($id->id_la ?? $id->id) : $id;
        $lapso = LapsoAcademico::obtenerPorId($idActual);
        $anosSelect = AnoAcademico::obtenerActivosParaSelect();

        $lapsoActivo = DB::table('lapso_academico')
            ->select(['id_la', 'nombre_la'])
            ->where('id_la', '!=', $idActual)
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Academico/LapsoAcademico/edit', [
            'lapso'       => $lapso,
            'anosSelect'  => $anosSelect,
            'lapsoActivo' => $lapsoActivo,
        ]);
    }

    /**
     * Actualiza los datos de un lapso académico.
     * Aplica la regla de negocio "Único Lapso Activo": solo puede haber un (1) lapso activo a la vez.
     * Excluye el ID del lapso que se está editando actual.
     */
    public function update(UpdateLapsoAcademicoRequest $request, $id): RedirectResponse
    {
        $data = $request->validated();
        $idActual = is_object($id) ? ($id->id_la ?? $id->id) : $id;
        $lapso = ($id instanceof LapsoAcademico) ? $id : LapsoAcademico::where('id_la', $idActual)->firstOrFail();

        // Regla de Negocio: No se permite modificar el año académico padre durante la edición
        $data['ano_id'] = $lapso->ano_id;

        // Determinar status_id (1 = Activo, 2 = Inactivo) con soporte estricto para valores booleanos y strings
        if (isset($data['status_id'])) {
            $statusId = (int) $data['status_id'];
        } elseif (array_key_exists('estado_la', $data) && $data['estado_la'] !== null) {
            $statusId = filter_var($data['estado_la'], FILTER_VALIDATE_BOOLEAN) ? 1 : 2;
        } else {
            $statusId = 1;
        }

        $data['status_id'] = $statusId;
        $data['estado_la'] = ($statusId === 1);
        $data['status']    = ($statusId === 1);

        $cerrarAnterior = $request->boolean('cerrar_anterior');
        $otroActivo = LapsoAcademico::where('id_la', '!=', $idActual)->where('status_id', 1)->whereNull('deleted_at')->first();

        // Si el usuario lo actualiza a Activo y ya existe otro Activo (excluyendo el actual)
        if ($data['status_id'] == 1 && $otroActivo) {
            if ($cerrarAnterior) {
                // Cerrar lapso previo y propagar en cascada
                LapsoAcademico::alternarEstatus($otroActivo->id_la, 2);
                $this->desactivarHijosEnCascada($otroActivo->id_la);
                $data['status_id'] = 1;
                $data['estado_la'] = true;
                $data['status']    = true;
            } elseif ($request->boolean('guardar_desactivado')) {
                $data['status_id'] = 2;
                $data['estado_la'] = false;
                $data['status']    = false;
            } else {
                return redirect()->back()
                    ->withErrors([
                        'status_id' => "Atención: Ya existe el lapso académico '{$otroActivo->nombre_la}' activo. Debe cerrar el periodo anterior o registrarlo como desactivado."
                    ])
                    ->withInput();
            }
        }

        // Persistencia
        $lapso->update($data);

        // Si se actualizó a inactivo, propagar en cascada
        if ($data['status_id'] === 2) {
            $this->desactivarHijosEnCascada($idActual);
        }

        return redirect()->route('lapso.index')
            ->with('success', 'Lapso actualizado exitosamente.');
    }

    /**
     * Alterna el estatus (activo / inactivo) delegando al modelo y capturando regla de protección.
     */
    public function alternarEstatus(Request $request, $id): RedirectResponse
    {
        try {
            $nuevoEstatus = $request->has('estado_la') ? $request->boolean('estado_la') : null;

            $activo = LapsoAcademico::alternarEstatus($id, $nuevoEstatus);

            $mensaje = $activo
                ? 'Lapso académico activado exitosamente.'
                : 'Lapso académico desactivado exitosamente.';

            return redirect()->back()->with('success', $mensaje);
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Elimina en cascada el lapso académico y sus cohortes dependientes.
     * Las asignaciones subordinadas de cohorte por sede (después de cohorte) y secciones se desactivan.
     */
    public function destroy($id): RedirectResponse
    {
        $idActual = is_object($id) ? ($id->id_la ?? $id->id) : $id;

        try {
            return DB::transaction(function () use ($idActual) {
                $lapso = DB::table('lapso_academico')
                    ->where('id_la', $idActual)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$lapso) {
                    return redirect()->back()->withErrors(['error' => 'El lapso académico que intenta eliminar no existe o ya fue eliminado.']);
                }

                $ahora = now();
                $userId = auth()->id();

                // 1. Obtener cohortes asociadas al lapso
                $cohorteIds = DB::table('cohorte')
                    ->where('laa_id', $idActual)
                    ->whereNull('deleted_at')
                    ->pluck('id_coh');

                if ($cohorteIds->isNotEmpty()) {
                    // 2. Regla Jerarquía: Después de cohorte solo se desactivan las asignaciones por sede
                    DB::table('cohorte_sede')
                        ->whereIn('coh_id', $cohorteIds)
                        ->update([
                            'status'     => false,
                            'status_id'  => 2,
                            'updated_id' => $userId,
                            'updated_at' => $ahora,
                        ]);

                    // 3. Eliminación de las cohortes (soft delete)
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

                // 4. Desactivar secciones asociadas al lapso
                DB::table('seccion')
                    ->where('laa_id', $idActual)
                    ->update([
                        'status'     => false,
                        'status_id'  => 2,
                        'updated_id' => $userId,
                        'updated_at' => $ahora,
                    ]);

                // 5. Eliminación del lapso académico
                DB::table('lapso_academico')
                    ->where('id_la', $idActual)
                    ->update([
                        'deleted_at' => $ahora,
                        'status'     => false,
                        'status_id'  => 2,
                        'estado_la'  => false,
                    ]);

                return redirect()->back()->with('success', 'Lapso académico "' . $lapso->nombre_la . '" y sus cohortes dependientes fueron eliminados correctamente.');
            });
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar el lapso académico: ' . $e->getMessage()]);
        }
    }

    /**
     * Desactiva en cascada todas las cohortes asociadas y sus correspondientes asignaciones por sede.
     */
    protected function desactivarHijosEnCascada($lapsoId): void
    {
        $cohorteIds = DB::table('cohorte')
            ->where('laa_id', $lapsoId)
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
