<?php

/**
 * Propósito: Controlador CRUD del Maestro Horario (`horario`) con validación anti-choque docente, aula y sección.
 * Historial: Actualizado para consultar `persona_perfil` + `persona` vía `carga_academica.pep_id`, utilizar `MotorAntiChoqueService`, eliminar booleano `status` y trabajar exclusivamente con `status_id`.
 */

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Horario\StoreHorarioRequest;
use App\Http\Requests\ProgramacionAcademica\Horario\UpdateHorarioRequest;
use App\Models\ProgramacionAcademica\CargaAcademica;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\Horario;
use App\Services\ProgramacionAcademica\MotorAntiChoqueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class HorarioController extends Controller
{
    /**
     * Carga catálogos de Cargas Académicas, Aulas activas y Días hábiles.
     */
    private function cargarCatalogos(): array
    {
        $cargasAcademicas = DB::table('carga_academica')
            ->leftJoin('seccion', 'seccion.id_sec', '=', 'carga_academica.sec_id')
            ->leftJoin('unidad_curricular', 'unidad_curricular.id_unc', '=', 'carga_academica.unc_id')
            ->leftJoin('persona_perfil', 'persona_perfil.id_pep', '=', 'carga_academica.pep_id')
            ->leftJoin('persona', 'persona.id_per', '=', 'persona_perfil.per_id')
            ->where('carga_academica.status_id', 1)
            ->whereNull('carga_academica.deleted_at')
            ->select(
                'carga_academica.id_caa',
                DB::raw("COALESCE(seccion.nombre_sec, 'Sin Sección') || ' — ' || COALESCE(unidad_curricular.nombre_unc, 'Sin Asignatura') || COALESCE(' (Prof. ' || persona.nombres_per || ' ' || persona.apellidos_per || ')', '') as descripcion_caa")
            )
            ->orderBy('seccion.nombre_sec', 'asc')
            ->get();

        $aulas = Aula::query()
            ->where('status_id', 1)
            ->select('id_aul', 'codigo_aula_aul', 'capacidad_maxima_aul', 'tipo_espacio_aul')
            ->orderBy('codigo_aula_aul', 'asc')
            ->get();

        return [
            'cargasAcademicas' => $cargasAcademicas,
            'aulas'            => $aulas,
            'diasSemana'       => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        ];
    }

    /**
     * Ejecuta el Motor Anti-Choque contra docente, aula y sección antes de guardar un bloque.
     *
     * @throws ValidationException
     *
     * Historial:
     *  - 2026-11-16: Actualizado para invocar `MotorAntiChoqueService::validarDisponibilidad(array $data, ?int $excludeHorarioId)`.
     */
    private function verificarAntiChoque(array $data, MotorAntiChoqueService $antiChoque, ?int $excludeHorarioId = null): void
    {
        $carga = CargaAcademica::with('seccion')->where('id_caa', $data['caa_id'])->firstOrFail();

        if ($carga->seccion && $carga->seccion->laa_id) {
            $antiChoque->validarDisponibilidad([
                'laa_id'          => (int) $carga->seccion->laa_id,
                'pep_id'          => (int) $carga->pep_id,
                'aul_id'          => (int) $data['aul_id'],
                'sec_id'          => (int) $carga->sec_id,
                'dia_semana_hor'  => $data['dia_semana_hor'],
                'hora_inicio_hor' => $data['hora_inicio_hor'],
                'hora_fin_hor'    => $data['hora_fin_hor'],
            ], $excludeHorarioId);
        }
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Horario::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $diaFiltro = $request->input('dia_semana_hor', $request->input('dia_semana', 'all'));
        $filtros = [
            'search'         => $request->input('search'),
            'status'         => $request->input('status', 'all'),
            'status_id'      => $request->input('status_id'),
            'dia_semana'     => $diaFiltro,
            'dia_semana_hor' => $diaFiltro,
            'aul_id'         => $request->input('aul_id', 'all'),
        ];

        return Inertia::render('ProgramacionAcademica/Maestros/Horario/index', array_merge([
            'horarios' => Horario::obtenerPaginados($filtros, $perPage),
            'filters'  => $filtros,
        ], $this->cargarCatalogos()));
    }

    public function create(): Response
    {
        $this->authorize('create', Horario::class);

        return Inertia::render('ProgramacionAcademica/Maestros/Horario/create', $this->cargarCatalogos());
    }

    public function show(int $horario): Response
    {
        $registro = Horario::with([
            'cargaAcademica.seccion',
            'cargaAcademica.unidadCurricular',
            'cargaAcademica.docentePerfil.persona',
            'aula',
        ])->where('id_hor', $horario)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Horario/show', [
            'horario' => $registro,
        ]);
    }

    public function edit(int $horario): Response
    {
        $registro = Horario::where('id_hor', $horario)->firstOrFail();
        $this->authorize('update', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Horario/edit', array_merge([
            'horario' => $registro,
        ], $this->cargarCatalogos()));
    }

    public function store(StoreHorarioRequest $request, MotorAntiChoqueService $antiChoque): RedirectResponse
    {
        $this->authorize('create', Horario::class);

        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $antiChoque) {
                $this->verificarAntiChoque($data, $antiChoque);

                Horario::create($data + [
                    'tipo_sesion_hor' => $data['tipo_sesion_hor'] ?? 'Teorica',
                    'status_id'       => Horario::STATUS_ACTIVO,
                    'created_id'      => auth()->id(),
                    'updated_id'      => auth()->id(),
                ]);
            });

            return redirect()->back()->with('success', 'Horario de clase registrado correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al registrar el horario de clase.']);
        }
    }

    public function update(UpdateHorarioRequest $request, int $horario, MotorAntiChoqueService $antiChoque): RedirectResponse
    {
        $registro = Horario::where('id_hor', $horario)->firstOrFail();
        $this->authorize('update', $registro);

        $data = $request->validated();

        try {
            DB::transaction(function () use ($registro, $data, $antiChoque) {
                $this->verificarAntiChoque($data, $antiChoque, $registro->id_hor);
                $registro->update($data + ['updated_id' => auth()->id()]);
            });

            return redirect()->back()->with('success', 'Horario de clase actualizado correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar el horario de clase.']);
        }
    }

    /**
     * Si recibe `eliminar=1`, ejecuta baja lógica en interfaz (`deleted_at` + `status_id = 2`);
     * de lo contrario invierte únicamente el estatus lógico (`status_id` 1 <-> 2).
     *
     * Historial:
     * - 2026-11-16: Separada la eliminación lógica (`eliminar=1`) del toggle de estado.
     */
    public function destroy(Request $request, int $horario): RedirectResponse
    {
        $registro = Horario::where('id_hor', $horario)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            if ($request->boolean('eliminar')) {
                Horario::ejecutarBajaLogicaModelo($registro, auth()->id(), Horario::STATUS_INACTIVO);

                return redirect()->back()->with('success', 'Horario eliminado correctamente.');
            }

            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Horario::alternarEstatus($horario, $nuevoEstado);

            return redirect()->back()->with(
                'success',
                $nuevoEstado === 1
                    ? 'Horario activado correctamente.'
                    : 'Horario desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la solicitud sobre el horario.']);
        }
    }
}
