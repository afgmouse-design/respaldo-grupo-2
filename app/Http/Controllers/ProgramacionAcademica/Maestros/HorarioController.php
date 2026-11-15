<?php

namespace App\Http\Controllers\ProgramacionAcademica\Maestros;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramacionAcademica\Horario\StoreHorarioRequest;
use App\Http\Requests\ProgramacionAcademica\Horario\UpdateHorarioRequest;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\Horario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador: Maestro de Horario (Programación Académica).
 * Grupo 3: Ángel Gonzales - Oferta y Horarios.
 *
 * Propósito: CRUD SPA con Inertia y Modales según estándar oficial SOGAC.
 * Incluye motor anti-choque de aulas para evitar solapamiento de clases
 * y toggle lógico de estatus en destroy().
 */
class HorarioController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Horario::class);

        $perPage = (int) ($request->input('registros_per_page') ?? $request->input('per_page') ?? 10);
        $filtros = [
            'search'     => $request->input('search'),
            'status'     => $request->input('status', 'all'),
            'dia_semana' => $request->input('dia_semana', 'all'),
            'aul_id'     => $request->input('aul_id', 'all'),
        ];

        $horarios = Horario::obtenerPaginados($filtros, $perPage);

        // Catálogo de Cargas Académicas (Sección + Unidad Curricular + Docente)
        $cargasAcademicas = DB::table('carga_academica')
            ->leftJoin('seccion', 'seccion.id_sec', '=', 'carga_academica.sec_id')
            ->leftJoin('unidad_curricular', 'unidad_curricular.id_unc', '=', 'carga_academica.unc_id')
            ->where('carga_academica.status', true)
            ->whereNull('carga_academica.deleted_at')
            ->select(
                'carga_academica.id_caa',
                DB::raw("COALESCE(seccion.nombre_sec, 'Sin Sección') || ' — ' || COALESCE(unidad_curricular.nombre_unc, 'Sin Asignatura') as descripcion_caa")
            )
            ->orderBy('seccion.nombre_sec', 'asc')
            ->get();

        // Catálogo de Aulas activas
        $aulas = Aula::query()
            ->where('status', true)
            ->whereNull('deleted_at')
            ->select('id_aul', 'codigo_aula_aul', 'capacidad_maxima_aul', 'tipo_espacio_aul')
            ->orderBy('codigo_aula_aul', 'asc')
            ->get();

        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        return Inertia::render('ProgramacionAcademica/Maestros/Horario/index', [
            'horarios'         => $horarios,
            'cargasAcademicas' => $cargasAcademicas,
            'aulas'            => $aulas,
            'diasSemana'       => $diasSemana,
            'filters'          => $filtros,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Horario::class);

        $cargasAcademicas = DB::table('carga_academica')
            ->leftJoin('seccion', 'seccion.id_sec', '=', 'carga_academica.sec_id')
            ->leftJoin('unidad_curricular', 'unidad_curricular.id_unc', '=', 'carga_academica.unc_id')
            ->where('carga_academica.status', true)
            ->whereNull('carga_academica.deleted_at')
            ->select(
                'carga_academica.id_caa',
                DB::raw("COALESCE(seccion.nombre_sec, 'Sin Sección') || ' — ' || COALESCE(unidad_curricular.nombre_unc, 'Sin Asignatura') as descripcion_caa")
            )
            ->orderBy('seccion.nombre_sec', 'asc')
            ->get();

        $aulas = Aula::query()
            ->where('status', true)
            ->whereNull('deleted_at')
            ->select('id_aul', 'codigo_aula_aul', 'capacidad_maxima_aul', 'tipo_espacio_aul')
            ->orderBy('codigo_aula_aul', 'asc')
            ->get();

        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        return Inertia::render('ProgramacionAcademica/Maestros/Horario/create', [
            'cargasAcademicas' => $cargasAcademicas,
            'aulas'            => $aulas,
            'diasSemana'       => $diasSemana,
        ]);
    }

    public function show(int $horario): Response
    {
        $registro = Horario::with(['cargaAcademica.seccion', 'cargaAcademica.unidadCurricular', 'aula'])->where('id_hor', $horario)->firstOrFail();
        $this->authorize('view', $registro);

        return Inertia::render('ProgramacionAcademica/Maestros/Horario/show', [
            'horario' => $registro,
        ]);
    }

    public function edit(int $horario): Response
    {
        $registro = Horario::where('id_hor', $horario)->firstOrFail();
        $this->authorize('update', $registro);

        $cargasAcademicas = DB::table('carga_academica')
            ->leftJoin('seccion', 'seccion.id_sec', '=', 'carga_academica.sec_id')
            ->leftJoin('unidad_curricular', 'unidad_curricular.id_unc', '=', 'carga_academica.unc_id')
            ->where('carga_academica.status', true)
            ->whereNull('carga_academica.deleted_at')
            ->select(
                'carga_academica.id_caa',
                DB::raw("COALESCE(seccion.nombre_sec, 'Sin Sección') || ' — ' || COALESCE(unidad_curricular.nombre_unc, 'Sin Asignatura') as descripcion_caa")
            )
            ->orderBy('seccion.nombre_sec', 'asc')
            ->get();

        $aulas = Aula::query()
            ->where('status', true)
            ->whereNull('deleted_at')
            ->select('id_aul', 'codigo_aula_aul', 'capacidad_maxima_aul', 'tipo_espacio_aul')
            ->orderBy('codigo_aula_aul', 'asc')
            ->get();

        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        return Inertia::render('ProgramacionAcademica/Maestros/Horario/edit', [
            'horario'          => $registro,
            'cargasAcademicas' => $cargasAcademicas,
            'aulas'            => $aulas,
            'diasSemana'       => $diasSemana,
        ]);
    }

    public function store(StoreHorarioRequest $request): RedirectResponse
    {
        $this->authorize('create', Horario::class);

        $data = $request->validated();

        // Validación Anti-Choque de Aula
        $conflicto = DB::table('horario')
            ->where('aul_id', $data['aul_id'])
            ->where('dia_semana_hor', $data['dia_semana_hor'])
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($data) {
                $q->whereBetween('hora_inicio_hor', [$data['hora_inicio_hor'], $data['hora_fin_hor']])
                  ->orWhereBetween('hora_fin_hor', [$data['hora_inicio_hor'], $data['hora_fin_hor']])
                  ->orWhere(function ($sub) use ($data) {
                      $sub->where('hora_inicio_hor', '<=', $data['hora_inicio_hor'])
                          ->where('hora_fin_hor', '>=', $data['hora_fin_hor']);
                  });
            })
            ->exists();

        if ($conflicto) {
            return redirect()->back()->withErrors([
                'error' => 'Conflicto de Aula: Ya existe un bloque de clases asignado a esa aula en el mismo día y horario.',
            ]);
        }

        try {
            Horario::create($data + [
                'status'     => true,
                'status_id'  => 1,
                'created_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Horario de clase registrado correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al registrar el horario de clase.']);
        }
    }

    public function update(UpdateHorarioRequest $request, int $horario): RedirectResponse
    {
        $registro = Horario::where('id_hor', $horario)->firstOrFail();
        $this->authorize('update', $registro);

        $data = $request->validated();

        // Validación Anti-Choque ignorando el registro actual
        $conflicto = DB::table('horario')
            ->where('aul_id', $data['aul_id'])
            ->where('dia_semana_hor', $data['dia_semana_hor'])
            ->where('id_hor', '!=', $horario)
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($data) {
                $q->whereBetween('hora_inicio_hor', [$data['hora_inicio_hor'], $data['hora_fin_hor']])
                  ->orWhereBetween('hora_fin_hor', [$data['hora_inicio_hor'], $data['hora_fin_hor']])
                  ->orWhere(function ($sub) use ($data) {
                      $sub->where('hora_inicio_hor', '<=', $data['hora_inicio_hor'])
                          ->where('hora_fin_hor', '>=', $data['hora_fin_hor']);
                  });
            })
            ->exists();

        if ($conflicto) {
            return redirect()->back()->withErrors([
                'error' => 'Conflicto de Aula: Ya existe otro bloque asignado a esa aula en el mismo día y horario.',
            ]);
        }

        try {
            $registro->update($data + ['updated_id' => auth()->id()]);

            return redirect()->back()->with('success', 'Horario de clase actualizado correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al actualizar el horario de clase.']);
        }
    }

    public function destroy(int $horario): RedirectResponse
    {
        $registro = Horario::where('id_hor', $horario)->firstOrFail();
        $this->authorize('delete', $registro);

        try {
            $nuevoEstado = $registro->status_id === 1 ? 2 : 1;
            Horario::alternarEstatus($horario, $nuevoEstado);

            return redirect()->back()->with('success', $nuevoEstado === 1
                ? 'Horario activado correctamente.'
                : 'Horario desactivado correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors(['error' => 'No se pudo alternar el estatus del horario.']);
        }
    }
}
