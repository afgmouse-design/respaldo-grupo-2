<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Models\Academico\AnoAcademico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Año Académico (`ano_academico`).
 *
 * Propósito: Gestión del macro-ciclo académico institucional (`id_ana`) aplicando la
 * regla de único Año Académico activo simultáneo y propagación lógica hacia lapsos lectivos.
 *
 * Historial:
 *  - 2026-11-14: Alineado completamente a `id_ana`, `nombre_ana`, `fecha_inicio_ana`,
 *    `fecha_fin_ana` y `status_id` (`1` Activo / `2` Inactivo), eliminando referencias
 *    legacy (`id_ano`, `*_ano`, `id_la`, `estado_la` y `status` booleano).
 */
class AnoAcademicoController extends Controller
{
    public function index(Request $request): Response
    {
        $busqueda = $request->input('search');
        $status = $request->input('status_id') ?? $request->input('status');
        $porPagina = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        return Inertia::render('Academico/AnoAcademico/index', [
            'anos'    => AnoAcademico::obtenerPaginados($busqueda, $porPagina, $status),
            'filters' => [
                'search'    => $busqueda,
                'status'    => $status,
                'status_id' => $status,
                'per_page'  => $porPagina,
            ],
        ]);
    }

    public function create(): Response
    {
        $anoActivo = DB::table('ano_academico')
            ->select(['id_ana', 'nombre_ana'])
            ->where('status_id', AnoAcademico::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Academico/AnoAcademico/create', [
            'anoActivo' => $anoActivo,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarPeticion($request);

        $otroActivo = DB::table('ano_academico')
            ->select(['id_ana', 'nombre_ana'])
            ->where('status_id', AnoAcademico::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->first();

        if ($datos['status_id'] === AnoAcademico::STATUS_ACTIVO && $otroActivo) {
            if ($request->boolean('cerrar_anterior')) {
                AnoAcademico::alternarEstatus($otroActivo->id_ana, false);
            } elseif ($request->boolean('guardar_desactivado')) {
                $datos['status_id'] = AnoAcademico::STATUS_INACTIVO;
            } else {
                return redirect()->back()
                    ->withErrors([
                        'status_id' => "Atención: Ya existe el año académico '{$otroActivo->nombre_ana}' activo. Debe cerrar el año anterior o registrarlo como desactivado.",
                    ])
                    ->withInput();
            }
        }

        AnoAcademico::registrar($datos);

        return redirect()->route('academico.ano-academico.index')
            ->with('success', 'Año académico registrado exitosamente.');
    }

    public function show($id): Response
    {
        $idActual = is_object($id) ? ($id->id_ana ?? $id->id) : $id;

        return Inertia::render('Academico/AnoAcademico/show', [
            'ano' => AnoAcademico::obtenerPorId($idActual),
        ]);
    }

    public function edit($id): Response
    {
        $idActual = is_object($id) ? ($id->id_ana ?? $id->id) : $id;
        $ano = AnoAcademico::obtenerPorId($idActual);

        $anoActivo = DB::table('ano_academico')
            ->select(['id_ana', 'nombre_ana'])
            ->where('id_ana', '!=', (int) $idActual)
            ->where('status_id', AnoAcademico::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Academico/AnoAcademico/edit', [
            'ano'       => $ano,
            'anoActivo' => $anoActivo,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $datos = $this->validarPeticion($request);
        $idActual = (int) (is_object($id) ? ($id->id_ana ?? $id->id) : $id);

        $otroActivo = DB::table('ano_academico')
            ->select(['id_ana', 'nombre_ana'])
            ->where('id_ana', '!=', $idActual)
            ->where('status_id', AnoAcademico::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->first();

        if ($datos['status_id'] === AnoAcademico::STATUS_ACTIVO && $otroActivo) {
            if ($request->boolean('cerrar_anterior')) {
                AnoAcademico::alternarEstatus($otroActivo->id_ana, false);
            } elseif ($request->boolean('guardar_desactivado')) {
                $datos['status_id'] = AnoAcademico::STATUS_INACTIVO;
            } else {
                return redirect()->back()
                    ->withErrors([
                        'status_id' => "Atención: Ya existe el año académico '{$otroActivo->nombre_ana}' activo. Debe cerrar el año anterior o registrarlo como desactivado.",
                    ])
                    ->withInput();
            }
        }

        AnoAcademico::actualizar($idActual, $datos);

        if ($datos['status_id'] === AnoAcademico::STATUS_INACTIVO) {
            $this->desactivarHijosEnCascada($idActual);
        }

        return redirect()->route('academico.ano-academico.index')
            ->with('success', 'Año académico actualizado exitosamente.');
    }

    public function alternarEstatus(Request $request, $id): RedirectResponse
    {
        try {
            $nuevoEstatus = null;
            if ($request->has('status_id')) {
                $nuevoEstatus = ((int) $request->input('status_id')) === AnoAcademico::STATUS_ACTIVO;
            } elseif ($request->has('status')) {
                $nuevoEstatus = $request->boolean('status');
            }

            $activo = AnoAcademico::alternarEstatus($id, $nuevoEstatus);

            return redirect()->back()->with('success', $activo
                ? 'Año académico activado exitosamente.'
                : 'Año académico desactivado exitosamente.');
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id): RedirectResponse
    {
        $idActual = (int) (is_object($id) ? ($id->id_ana ?? $id->id) : $id);

        try {
            return DB::transaction(function () use ($idActual) {
                $ano = DB::table('ano_academico')
                    ->where('id_ana', $idActual)
                    ->whereNull('deleted_at')
                    ->first();

                if (! $ano) {
                    return redirect()->back()->withErrors(['error' => 'El año académico que intenta eliminar no existe o ya fue eliminado.']);
                }

                $ahora = now();
                $userId = auth()->id();

                $lapsoIds = DB::table('lapso_academico')
                    ->where('ana_id', $idActual)
                    ->whereNull('deleted_at')
                    ->pluck('id_laa');

                if ($lapsoIds->isNotEmpty()) {
                    $cohorteIds = DB::table('cohorte')
                        ->whereIn('laa_id', $lapsoIds)
                        ->whereNull('deleted_at')
                        ->pluck('id_coh');

                    if ($cohorteIds->isNotEmpty()) {
                        DB::table('cohorte_sede')
                            ->whereIn('coh_id', $cohorteIds)
                            ->update([
                                'status_id'  => AnoAcademico::STATUS_INACTIVO,
                                'updated_id' => $userId,
                                'updated_at' => $ahora,
                            ]);

                        DB::table('cohorte')
                            ->whereIn('id_coh', $cohorteIds)
                            ->update([
                                'deleted_at' => $ahora,
                                'status_id'  => AnoAcademico::STATUS_INACTIVO,
                                'updated_id' => $userId,
                                'updated_at' => $ahora,
                            ]);
                    }

                    DB::table('seccion')
                        ->whereIn('laa_id', $lapsoIds)
                        ->update([
                            'status_id'  => AnoAcademico::STATUS_INACTIVO,
                            'updated_id' => $userId,
                            'updated_at' => $ahora,
                        ]);

                    DB::table('lapso_academico')
                        ->whereIn('id_laa', $lapsoIds)
                        ->update([
                            'deleted_at' => $ahora,
                            'status_id'  => AnoAcademico::STATUS_INACTIVO,
                            'updated_id' => $userId,
                            'updated_at' => $ahora,
                        ]);
                }

                DB::table('ano_academico')
                    ->where('id_ana', $idActual)
                    ->update([
                        'deleted_at' => $ahora,
                        'status_id'  => AnoAcademico::STATUS_INACTIVO,
                        'updated_id' => $userId,
                        'updated_at' => $ahora,
                    ]);

                return redirect()->back()->with('success', 'Año académico "' . $ano->nombre_ana . '" y sus dependencias (lapsos y cohortes) fueron eliminados correctamente.');
            });
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar el año académico: ' . $e->getMessage()]);
        }
    }

    /**
     * Normaliza alias de entrada y valida contra el esquema oficial `ano_academico`.
     *
     * @return array<string, mixed>
     */
    private function validarPeticion(Request $request): array
    {
        $request->merge([
            'nombre_ana'       => $request->input('nombre_ana') ?? $request->input('nombre_ano'),
            'fecha_inicio_ana' => $request->input('fecha_inicio_ana') ?? $request->input('fecha_inicio_ano'),
            'fecha_fin_ana'    => $request->input('fecha_fin_ana') ?? $request->input('fecha_fin_ano'),
        ]);

        $datos = $request->validate([
            'nombre_ana'       => 'required|string|max:100',
            'fecha_inicio_ana' => 'nullable|date',
            'fecha_fin_ana'    => 'nullable|date|after_or_equal:fecha_inicio_ana',
            'status_id'        => 'nullable|integer|exists:status,id_sta',
            'status'           => 'nullable',
        ]);

        if (isset($datos['status_id'])) {
            $statusId = (int) $datos['status_id'];
        } elseif ($request->has('status')) {
            $statusId = $request->boolean('status') ? AnoAcademico::STATUS_ACTIVO : AnoAcademico::STATUS_INACTIVO;
        } else {
            $statusId = AnoAcademico::STATUS_ACTIVO;
        }

        $datos['status_id'] = $statusId;

        return $datos;
    }

    protected function desactivarHijosEnCascada(int $anoId): void
    {
        $lapsoIds = DB::table('lapso_academico')
            ->where('ana_id', $anoId)
            ->whereNull('deleted_at')
            ->pluck('id_laa');

        if ($lapsoIds->isEmpty()) {
            return;
        }

        $now = now();
        $userId = auth()->id();

        DB::table('lapso_academico')
            ->whereIn('id_laa', $lapsoIds)
            ->update([
                'status_id'  => AnoAcademico::STATUS_INACTIVO,
                'updated_id' => $userId,
                'updated_at' => $now,
            ]);

        $cohorteIds = DB::table('cohorte')
            ->whereIn('laa_id', $lapsoIds)
            ->whereNull('deleted_at')
            ->pluck('id_coh');

        if ($cohorteIds->isNotEmpty()) {
            DB::table('cohorte')
                ->whereIn('id_coh', $cohorteIds)
                ->update([
                    'status_id'  => AnoAcademico::STATUS_INACTIVO,
                    'updated_id' => $userId,
                    'updated_at' => $now,
                ]);

            DB::table('cohorte_sede')
                ->whereIn('coh_id', $cohorteIds)
                ->update([
                    'status_id'  => AnoAcademico::STATUS_INACTIVO,
                    'updated_id' => $userId,
                    'updated_at' => $now,
                ]);
        }
    }
}
