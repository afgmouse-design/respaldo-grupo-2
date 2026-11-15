<?php

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Cohorte\StoreCohorteRequest;
use App\Http\Requests\ProgramacionAcademica\Cohorte\UpdateCohorteRequest;
use App\Models\ProgramacionAcademica\Maestros\Cohorte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Cohorte (Programación Académica).
 *
 * Propósito: CRUD SPA con Inertia. Crear/editar/ver se resuelven mediante modales.
 * Envía el catálogo de lapsos académicos para alimentar los selectores de los modales.
 *
 * Actualización 2026-10-21 (Regla de Negocio: Única Cohorte Activa y Eliminación Segura):
 * Se implementó la regla que restringe a una (1) sola Cohorte activa simultáneamente en el sistema.
 * Si se crea o actualiza un registro activo existiendo ya otra cohorte activa, se fuerza silenciosamente
 * a Inactiva (status_id = 2, status = false) y se emite notificación flash de advertencia.
 * En destroy(), se comprueba si la cohorte posee asignaciones en `cohorte_sede` antes de eliminar
 * físicamente el registro o aplicar baja lógica preventiva.
 *
 * Actualización 2026-10-21 (Visualización Predeterminada de Todas las Cohortes y Lapsos en Filtros):
 * Se ajustó `index()` para pasar los filtros con valor por defecto 'all' para estatus y lapso,
 * permitiendo listar todas las cohortes registradas al ingresar sin restringir indebidamente a activas.
 * Se removió la restricción `estado_la = true` del catálogo de lapsos para el filtrado.
 *
 * Actualización 2026-10-21 (Protección de Padre Inmutable y Fechas en Catálogo de Lapsos):
 * Se agregaron `fecha_inicio_la` y `fecha_fin_la` al select de lapsos para que los modales de creación
 * y edición puedan validar límites de fechas. En `update()`, se fijó `laa_id` con el valor del registro
 * original para impedir la edición del lapso académico padre.
 *
 * Actualización 2026-10-21 (Lógica de Jerarquía: Eliminación Efectiva y Desactivación Subordinada):
 * Se actualizó `destroy()` para eliminar efectivamente la cohorte mediante baja lógica (deleted_at),
 * desactivando en cascada sus asignaciones por sede en `cohorte_sede` sin quebrar restricciones de clave foránea.
 * Se añadió filtro `whereNull('deleted_at')` al listado de lapsos en `index()`.
 */
class CohorteController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Cohorte::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = [
            'search' => $request->input('search'),
            'status' => $request->input('status', 'all'),
            'laa_id' => $request->input('laa_id', 'all'),
        ];

        $cohortes = Cohorte::obtenerPaginados($filtros, $perPage);
        $lapsos = DB::table('lapso_academico')
            ->select('id_la', 'nombre_la', 'fecha_inicio_la', 'fecha_fin_la')
            ->whereNull('deleted_at')
            ->orderBy('nombre_la', 'desc')
            ->get();

        return Inertia::render('ProgramacionAcademica/Maestros/Cohorte/index', [
            'cohortes' => $cohortes,
            'lapsos'   => $lapsos,
            'filters'  => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Cohorte::class);

        return Inertia::render('ProgramacionAcademica/Maestros/Cohorte/create', [
            'lapsos' => DB::table('lapso_academico')
                ->select('id_la', 'nombre_la', 'fecha_inicio_la', 'fecha_fin_la')
                ->whereNull('deleted_at')
                ->orderBy('nombre_la', 'desc')
                ->get(),
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
            'lapsos'  => DB::table('lapso_academico')
                ->select('id_la', 'nombre_la', 'fecha_inicio_la', 'fecha_fin_la')
                ->whereNull('deleted_at')
                ->orderBy('nombre_la', 'desc')
                ->get(),
        ]);
    }

    public function store(StoreCohorteRequest $request): RedirectResponse
    {
        $this->authorize('create', Cohorte::class);

        try {
            $data = $request->validated();

            // Determinar status deseado (por defecto activo)
            $statusId = 1;
            if (isset($data['status_id'])) {
                $statusId = (int) $data['status_id'];
            } elseif (array_key_exists('status', $data) && $data['status'] !== null) {
                $statusId = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 2;
            }

            $mensajeAlerta = 'La cohorte se guardó como Inactiva porque el sistema detectó que ya existe otra cohorte activa en curso. Solo puede haber una cohorte activa a la vez.';

            // Regla de Negocio "Única Cohorte Activa":
            if ($statusId === 1 && Cohorte::where('status_id', 1)->exists()) {
                $data['status_id']  = 2;
                $data['status']     = false;
                $data['created_id'] = auth()->id();

                Cohorte::create($data);

                return redirect()->route('cohorte.index')
                    ->with('warning', $mensajeAlerta)
                    ->with('message', $mensajeAlerta);
            }

            $data['status_id']  = $statusId;
            $data['status']     = ($statusId === 1);
            $data['created_id'] = auth()->id();

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

            // Regla de Negocio: No se permite modificar el lapso académico padre durante la edición
            $data['laa_id'] = $registro->laa_id;

            // Determinar status deseado (conservar actual si no viene)
            $statusId = $registro->status_id ?? ($registro->status ? 1 : 2);
            if (isset($data['status_id'])) {
                $statusId = (int) $data['status_id'];
            } elseif (array_key_exists('status', $data) && $data['status'] !== null) {
                $statusId = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 2;
            }

            $mensajeAlerta = 'La cohorte se guardó como Inactiva porque el sistema detectó que ya existe otra cohorte activa en curso. Solo puede haber una cohorte activa a la vez.';

            // Regla "Única Cohorte Activa" excluyendo la cohorte actual
            if ($statusId === 1 && Cohorte::where('id_coh', '!=', $cohorte)->where('status_id', 1)->exists()) {
                $data['status_id']  = 2;
                $data['status']     = false;
                $data['updated_id'] = auth()->id();

                $registro->update($data);

                // Desactivación en cascada a cohorte_sede
                DB::table('cohorte_sede')
                    ->where('coh_id', $cohorte)
                    ->update([
                        'status_id'  => 2,
                        'status'     => false,
                        'updated_id' => auth()->id(),
                        'updated_at' => now(),
                    ]);

                return redirect()->back()
                    ->with('warning', $mensajeAlerta)
                    ->with('message', $mensajeAlerta);
            }

            $data['status_id']  = $statusId;
            $data['status']     = ($statusId === 1);
            $data['updated_id'] = auth()->id();

            $registro->update($data);

            // Si se actualizó a inactiva, propagar en cascada
            if ($statusId === 2) {
                DB::table('cohorte_sede')
                    ->where('coh_id', $cohorte)
                    ->update([
                        'status_id'  => 2,
                        'status'     => false,
                        'updated_id' => auth()->id(),
                        'updated_at' => now(),
                    ]);
            }

            return redirect()->route('cohorte.index')->with('success', 'Cohorte actualizada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar la cohorte: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estatus de la cohorte (activo <-> inactivo).
     */
    public function alternarEstatus(Request $request, int $cohorte): RedirectResponse
    {
        $registro = Cohorte::where('id_coh', $cohorte)->firstOrFail();
        $this->authorize('update', $registro);

        try {
            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Cohorte::alternarEstatus($cohorte, $nuevoEstado);

            return redirect()->back()->with('success', $nuevoEstado === 1
                ? 'Cohorte activada correctamente.'
                : 'Cohorte desactivada correctamente.');
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo alternar el estatus de la cohorte: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina la cohorte (soft delete con deleted_at) y desactiva sus asignaciones por sede en cascada.
     */
    public function destroy(int $cohorte): RedirectResponse
    {
        $registro = Cohorte::where('id_coh', $cohorte)->whereNull('deleted_at')->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            return DB::transaction(function () use ($registro, $cohorte) {
                $ahora = now();
                $userId = auth()->id();

                // 1. Regla Jerarquía: Después de cohorte solo se desactivan las asignaciones por sede
                DB::table('cohorte_sede')
                    ->where('coh_id', $cohorte)
                    ->update([
                        'status'     => false,
                        'status_id'  => 2,
                        'updated_id' => $userId,
                        'updated_at' => $ahora,
                    ]);

                // 2. Eliminación de la cohorte (soft delete)
                $nombre = $registro->nombre_coh;
                $registro->update([
                    'deleted_at' => $ahora,
                    'status'     => false,
                    'status_id'  => 2,
                    'updated_id' => $userId,
                ]);

                return redirect()->back()->with('success', 'Cohorte "' . $nombre . '" eliminada correctamente y sus asignaciones por sede fueron desactivadas.');
            });
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'No se pudo eliminar la cohorte: ' . $e->getMessage()]);
        }
    }
}

