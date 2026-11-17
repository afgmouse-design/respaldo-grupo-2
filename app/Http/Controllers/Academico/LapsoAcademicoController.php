<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Maestros\StoreLapsoAcademicoRequest;
use App\Http\Requests\ProgramacionAcademica\Maestros\UpdateLapsoAcademicoRequest;
use App\Models\Academico\AnoAcademico;
use App\Models\Academico\LapsoAcademico;
use App\Models\Academico\TipoLapso;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Lapso Académico (`lapso_academico`).
 *
 * Propósito: Gestiona períodos lectivos (`id_laa`) asociados a un Año Académico (`ana_id`)
 * y un Tipo de Lapso (`tla_id`), aplicando la regla de único lapso activo simultáneo y
 * cascada lógica hacia cohortes y secciones dependientes.
 *
 * Historial:
 *  - 2026-11-14: Alineado estrictamente al esquema oficial `lapso_academico` (`id_laa`,
 *    `tla_id`, `ana_id`, `codigo_laa`, `nombre_laa`, `fase_operativa_laa`, `status_id`),
 *    corrigiendo además el orden de parámetros hacia `LapsoAcademico::obtenerPaginados()`.
 *  - 2026-11-16: Se envuelven `store()` y `update()` en transacciones y bloques `try/catch`
 *    para capturar violaciones de unicidad o reglas de dominio sin generar HTTP 500.
 */
class LapsoAcademicoController extends Controller
{
    public function index(Request $request): Response
    {
        $busqueda = $request->input('search');
        $anaId = $request->input('ana_id') ?? $request->input('ano_id');
        $statusFiltro = $request->input('status_id') ?? $request->input('status') ?? $request->input('estado_la');
        $porPagina = (int) ($request->input('per_page') ?? $request->input('registros_per_page') ?? 10);

        return Inertia::render('Academico/LapsoAcademico/index', [
            'lapsos'     => LapsoAcademico::obtenerPaginados($busqueda, $statusFiltro, $porPagina, $anaId),
            'anosSelect' => AnoAcademico::obtenerActivosParaSelect(),
            'tiposLapso' => TipoLapso::obtenerActivosParaSelect(),
            'filters'    => [
                'search'    => $busqueda,
                'ana_id'    => $anaId,
                'status_id' => $statusFiltro,
                'per_page'  => $porPagina,
            ],
        ]);
    }

    public function create(): Response
    {
        $lapsoActivo = DB::table('lapso_academico')
            ->select(['id_laa', 'nombre_laa'])
            ->where('status_id', LapsoAcademico::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Academico/LapsoAcademico/create', [
            'anosSelect'  => AnoAcademico::obtenerActivosParaSelect(),
            'tiposLapso'  => TipoLapso::obtenerActivosParaSelect(),
            'lapsoActivo' => $lapsoActivo,
        ]);
    }

    public function store(StoreLapsoAcademicoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status_id'] = isset($data['status_id']) ? (int) $data['status_id'] : LapsoAcademico::STATUS_ACTIVO;

        return $this->persistirLapso(
            $request,
            $data,
            null,
            static fn (array $payload) => LapsoAcademico::registrarLapso($payload),
            'Lapso académico creado exitosamente.',
            'Ocurrió un error al registrar el lapso académico. Inténtalo de nuevo.'
        );
    }

    public function show($id): Response
    {
        $idActual = is_object($id) ? ($id->id_laa ?? $id->id) : $id;

        return Inertia::render('Academico/LapsoAcademico/show', [
            'lapso' => LapsoAcademico::obtenerPorId($idActual),
        ]);
    }

    public function edit($id): Response
    {
        $idActual = (int) (is_object($id) ? ($id->id_laa ?? $id->id) : $id);

        $lapsoActivo = DB::table('lapso_academico')
            ->select(['id_laa', 'nombre_laa'])
            ->where('id_laa', '!=', $idActual)
            ->where('status_id', LapsoAcademico::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Academico/LapsoAcademico/edit', [
            'lapso'       => LapsoAcademico::obtenerPorId($idActual),
            'anosSelect'  => AnoAcademico::obtenerActivosParaSelect(),
            'tiposLapso'  => TipoLapso::obtenerActivosParaSelect(),
            'lapsoActivo' => $lapsoActivo,
        ]);
    }

    public function update(UpdateLapsoAcademicoRequest $request, $id): RedirectResponse
    {
        $data = $request->validated();
        $idActual = (int) (is_object($id) ? ($id->id_laa ?? $id->id) : $id);
        $lapso = LapsoAcademico::query()->where('id_laa', $idActual)->firstOrFail();

        $data['ana_id'] = $lapso->ana_id;
        $data['tla_id'] = $data['tla_id'] ?? $lapso->tla_id;
        $data['status_id'] = isset($data['status_id']) ? (int) $data['status_id'] : (int) $lapso->status_id;

        return $this->persistirLapso(
            $request,
            $data,
            $idActual,
            function (array $payload) use ($idActual): void {
                LapsoAcademico::actualizarLapso($idActual, $payload);

                if ($payload['status_id'] === LapsoAcademico::STATUS_INACTIVO) {
                    $this->desactivarHijosEnCascada($idActual);
                }
            },
            'Lapso académico actualizado exitosamente.',
            'Ocurrió un error al actualizar el lapso académico. Inténtalo de nuevo.'
        );
    }

    /**
     * Ejecuta la persistencia transaccional de un lapso resolviendo conflicto de lapso activo
     * y capturando errores de unicidad o dominio sin devolver respuestas HTTP 500.
     */
    private function persistirLapso(
        Request $request,
        array $data,
        ?int $excludeId,
        callable $operacion,
        string $mensajeExito,
        string $mensajeError
    ): RedirectResponse {
        try {
            $resultado = DB::transaction(function () use ($request, &$data, $excludeId, $operacion): ?RedirectResponse {
                $otroActivo = DB::table('lapso_academico')
                    ->select(['id_laa', 'nombre_laa'])
                    ->when($excludeId !== null, fn ($q) => $q->where('id_laa', '!=', $excludeId))
                    ->where('status_id', LapsoAcademico::STATUS_ACTIVO)
                    ->whereNull('deleted_at')
                    ->first();

                if ($data['status_id'] === LapsoAcademico::STATUS_ACTIVO && $otroActivo) {
                    if ($request->boolean('cerrar_anterior')) {
                        LapsoAcademico::alternarEstatus($otroActivo->id_laa, false);
                    } elseif ($request->boolean('guardar_desactivado')) {
                        $data['status_id'] = LapsoAcademico::STATUS_INACTIVO;
                    } else {
                        return redirect()->back()
                            ->withErrors([
                                'status_id' => "Atención: Ya existe el lapso académico '{$otroActivo->nombre_laa}' activo. Debe cerrar el período anterior o registrarlo como desactivado.",
                            ])
                            ->withInput();
                    }
                }

                $operacion($data);

                return null;
            });

            if ($resultado instanceof RedirectResponse) {
                return $resultado;
            }

            return redirect()->route('lapso.index')->with('success', $mensajeExito);
        } catch (\DomainException $e) {
            return redirect()->back()
                ->withErrors(['codigo_laa' => $e->getMessage(), 'error' => $e->getMessage()])
                ->withInput();
        } catch (UniqueConstraintViolationException $e) {
            return redirect()->back()
                ->withErrors([
                    'codigo_laa' => 'Ya existe un lapso académico registrado con ese código.',
                    'error'      => 'El código de lapso indicado ya se encuentra registrado en el sistema.',
                ])
                ->withInput();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()
                ->withErrors(['error' => $mensajeError])
                ->withInput();
        }
    }

    public function alternarEstatus(Request $request, $id): RedirectResponse
    {
        try {
            $nuevoEstatus = null;
            if ($request->has('status_id')) {
                $nuevoEstatus = ((int) $request->input('status_id')) === LapsoAcademico::STATUS_ACTIVO;
            } elseif ($request->has('estado_la') || $request->has('status')) {
                $nuevoEstatus = filter_var($request->input('estado_la', $request->input('status')), FILTER_VALIDATE_BOOLEAN);
            }

            $activo = LapsoAcademico::alternarEstatus($id, $nuevoEstatus);

            return redirect()->back()->with('success', $activo
                ? 'Lapso académico activado exitosamente.'
                : 'Lapso académico desactivado exitosamente.');
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id): RedirectResponse
    {
        $idActual = (int) (is_object($id) ? ($id->id_laa ?? $id->id) : $id);

        try {
            return DB::transaction(function () use ($idActual) {
                $lapso = DB::table('lapso_academico')
                    ->where('id_laa', $idActual)
                    ->whereNull('deleted_at')
                    ->first();

                if (! $lapso) {
                    return redirect()->back()->withErrors(['error' => 'El lapso académico que intenta eliminar no existe o ya fue eliminado.']);
                }

                $ahora = now();
                $userId = auth()->id();

                $cohorteIds = DB::table('cohorte')
                    ->where('laa_id', $idActual)
                    ->whereNull('deleted_at')
                    ->pluck('id_coh');

                if ($cohorteIds->isNotEmpty()) {
                    DB::table('cohorte_sede')
                        ->whereIn('coh_id', $cohorteIds)
                        ->update([
                            'status_id'  => LapsoAcademico::STATUS_INACTIVO,
                            'updated_id' => $userId,
                            'updated_at' => $ahora,
                        ]);

                    DB::table('cohorte')
                        ->whereIn('id_coh', $cohorteIds)
                        ->update([
                            'deleted_at' => $ahora,
                            'status_id'  => LapsoAcademico::STATUS_INACTIVO,
                            'updated_id' => $userId,
                            'updated_at' => $ahora,
                        ]);
                }

                DB::table('seccion')
                    ->where('laa_id', $idActual)
                    ->update([
                        'status_id'  => LapsoAcademico::STATUS_INACTIVO,
                        'updated_id' => $userId,
                        'updated_at' => $ahora,
                    ]);

                DB::table('lapso_academico')
                    ->where('id_laa', $idActual)
                    ->update([
                        'deleted_at' => $ahora,
                        'status_id'  => LapsoAcademico::STATUS_INACTIVO,
                        'updated_id' => $userId,
                        'updated_at' => $ahora,
                    ]);

                return redirect()->back()->with('success', 'Lapso académico "' . $lapso->nombre_laa . '" y sus cohortes dependientes fueron eliminados correctamente.');
            });
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar el lapso académico: ' . $e->getMessage()]);
        }
    }

    protected function desactivarHijosEnCascada(int $lapsoId): void
    {
        $cohorteIds = DB::table('cohorte')
            ->where('laa_id', $lapsoId)
            ->whereNull('deleted_at')
            ->pluck('id_coh');

        if ($cohorteIds->isEmpty()) {
            return;
        }

        $now = now();
        $userId = auth()->id();

        DB::table('cohorte')
            ->whereIn('id_coh', $cohorteIds)
            ->update([
                'status_id'  => LapsoAcademico::STATUS_INACTIVO,
                'updated_id' => $userId,
                'updated_at' => $now,
            ]);

        DB::table('cohorte_sede')
            ->whereIn('coh_id', $cohorteIds)
            ->update([
                'status_id'  => LapsoAcademico::STATUS_INACTIVO,
                'updated_id' => $userId,
                'updated_at' => $now,
            ]);
    }
}
