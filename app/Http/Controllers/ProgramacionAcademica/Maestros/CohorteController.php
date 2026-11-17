<?php

/**
 * Propósito: Controlador CRUD del Maestro Cohorte (`cohorte`) aplicando la regla de única cohorte activa y cascada hacia `cohorte_sede`.
 * Historial: Actualizado para consultar `lapso_academico` con columnas oficiales (`id_laa`, `nombre_laa`, `fecha_inicio_laa`, `fecha_fin_laa`) y operar exclusivamente con `status_id` (`1`/`2`).
 */

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Cohorte\StoreCohorteRequest;
use App\Http\Requests\ProgramacionAcademica\Cohorte\UpdateCohorteRequest;
use App\Models\ProgramacionAcademica\Maestros\Cohorte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CohorteController extends Controller
{
    /**
     * Obtiene el catálogo de lapsos académicos usando las columnas oficiales de `sogac_mio.sql`.
     */
    private function cargarLapsos(): Collection
    {
        return DB::table('lapso_academico')
            ->select('id_laa', 'nombre_laa', 'fecha_inicio_laa', 'fecha_fin_laa')
            ->whereNull('deleted_at')
            ->orderBy('nombre_laa', 'desc')
            ->get();
    }

    /**
     * Desactiva en cascada las asignaciones `cohorte_sede` pertenecientes a una cohorte.
     */
    private function desactivarCohortesSede(int $cohId, ?int $userId): void
    {
        DB::table('cohorte_sede')
            ->where('coh_id', $cohId)
            ->update([
                'status_id'  => 2,
                'updated_id' => $userId,
                'updated_at' => now(),
            ]);
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Cohorte::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = [
            'search' => $request->input('search'),
            'status' => $request->input('status', 'all'),
            'laa_id' => $request->input('laa_id', 'all'),
        ];

        return Inertia::render('ProgramacionAcademica/Maestros/Cohorte/index', [
            'cohortes' => Cohorte::obtenerPaginados($filtros, $perPage),
            'lapsos'   => $this->cargarLapsos(),
            'filters'  => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Cohorte::class);

        return Inertia::render('ProgramacionAcademica/Maestros/Cohorte/create', [
            'lapsos' => $this->cargarLapsos(),
        ]);
    }

    public function show(int $cohorte): Response
    {
        $registro = Cohorte::with('lapsoAcademico')->where('id_coh', $cohorte)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Cohorte/show', [
            'cohorte' => $registro,
        ]);
    }

    public function edit(int $cohorte): Response
    {
        $registro = Cohorte::where('id_coh', $cohorte)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Cohorte/edit', [
            'cohorte' => $registro,
            'lapsos'  => $this->cargarLapsos(),
        ]);
    }

    public function store(StoreCohorteRequest $request): RedirectResponse
    {
        $this->authorize('create', Cohorte::class);

        try {
            $data = $request->validated();
            unset($data['status']);

            $statusId = 1;
            if (isset($data['status_id'])) {
                $statusId = (int) $data['status_id'];
            } elseif ($request->has('status') && $request->input('status') !== null) {
                $statusId = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN) ? 1 : 2;
            }

            $mensajeAlerta = 'La cohorte se guardó como Inactiva porque el sistema detectó que ya existe otra cohorte activa en curso. Solo puede haber una cohorte activa a la vez.';

            if ($statusId === 1 && Cohorte::where('status_id', 1)->exists()) {
                $data['status_id'] = 2;
                $data['created_id'] = auth()->id();
                $data['updated_id'] = auth()->id();

                Cohorte::create($data);

                return redirect()->route('cohorte.index')
                    ->with('warning', $mensajeAlerta)
                    ->with('message', $mensajeAlerta);
            }

            $data['status_id'] = $statusId;
            $data['created_id'] = auth()->id();
            $data['updated_id'] = auth()->id();

            Cohorte::create($data);

            return redirect()->route('cohorte.index')->with('success', 'Cohorte creada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al registrar la cohorte: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateCohorteRequest $request, int $cohorte): RedirectResponse
    {
        $registro = Cohorte::where('id_coh', $cohorte)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $data = $request->validated();
            unset($data['status']);
            $data['laa_id'] = $registro->laa_id;

            $statusId = $registro->status_id ?? 1;
            if (isset($data['status_id'])) {
                $statusId = (int) $data['status_id'];
            } elseif ($request->has('status') && $request->input('status') !== null) {
                $statusId = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN) ? 1 : 2;
            }

            $mensajeAlerta = 'La cohorte se guardó como Inactiva porque el sistema detectó que ya existe otra cohorte activa en curso. Solo puede haber una cohorte activa a la vez.';

            if ($statusId === 1 && Cohorte::where('id_coh', '!=', $cohorte)->where('status_id', 1)->exists()) {
                $data['status_id'] = 2;
                $data['updated_id'] = auth()->id();

                $registro->update($data);
                $this->desactivarCohortesSede($cohorte, auth()->id());

                return redirect()->back()
                    ->with('warning', $mensajeAlerta)
                    ->with('message', $mensajeAlerta);
            }

            $data['status_id'] = $statusId;
            $data['updated_id'] = auth()->id();

            $registro->update($data);

            if ($statusId === 2) {
                $this->desactivarCohortesSede($cohorte, auth()->id());
            }

            return redirect()->route('cohorte.index')->with('success', 'Cohorte actualizada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar la cohorte: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus lógico de la cohorte (`1` Activo <-> `2` Inactivo).
     */
    public function alternarEstatus(Request $request, int $cohorte): RedirectResponse
    {
        $registro = Cohorte::where('id_coh', $cohorte)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Cohorte::alternarEstatus($cohorte, $nuevoEstado);

            return redirect()->back()->with(
                'success',
                $nuevoEstado === 1
                    ? 'Cohorte activada correctamente.'
                    : 'Cohorte desactivada correctamente.'
            );
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo alternar el estatus de la cohorte: ' . $e->getMessage()]);
        }
    }

    /**
     * Aplica SoftDelete a la cohorte y desactiva en cascada sus asignaciones `cohorte_sede`.
     */
    public function destroy(int $cohorte): RedirectResponse
    {
        $registro = Cohorte::where('id_coh', $cohorte)->whereNull('deleted_at')->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            return DB::transaction(function () use ($registro, $cohorte) {
                $userId = auth()->id();

                $this->desactivarCohortesSede($cohorte, $userId);

                $nombre = $registro->nombre_coh;
                $registro->update([
                    'status_id'  => 2,
                    'updated_id' => $userId,
                ]);
                $registro->delete();

                return redirect()->back()->with('success', 'Cohorte "' . $nombre . '" eliminada correctamente y sus asignaciones por sede fueron desactivadas.');
            });
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar la cohorte: ' . $e->getMessage()]);
        }
    }
}
