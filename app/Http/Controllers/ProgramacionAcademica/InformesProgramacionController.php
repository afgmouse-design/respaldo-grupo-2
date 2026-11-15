<?php

namespace App\Http\Controllers\ProgramacionAcademica;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\SeccionHorario;
use App\Models\ProgramacionAcademica\SeccionUnidadDocente;
use App\Models\User;
use App\Services\Reportes\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador de Informes y Reportes de Programación Académica.
 */
class InformesProgramacionController extends Controller
{
    /**
     * Listado De Chequeo y Gestor de Cursos Intensivos.
     */
    public function chequeoIntensivo(Request $request): Response
    {
        $seccionesIntensivas = Seccion::query()
            ->with([
                'cohorteSede.cohorte',
                'mallaCurricular.pnf',
                'unidadesDocentes.mallaCurricularDetalle.unidadCurricular',
                'unidadesDocentes.docente',
            ])
            ->where(function ($q) {
                $q->where('tipo_seccion', 'Intensivo')
                  ->orWhere('tis_id', 5);
            })
            ->orderBy('nombre_sec')
            ->paginate(15)
            ->withQueryString();

        $unidadesCurriculares = UnidadCurricular::query()
            ->where('status_id', 1)
            ->orderBy('codigo_unc')
            ->get(['id_unc', 'codigo_unc', 'nombre_unc', 'semestre_unc', 'unidades_credito_unc']);

        return Inertia::render('ProgramacionAcademica/Informes/ListadoChequeoIntensivo', [
            'secciones'            => $seccionesIntensivas,
            'unidadesCurriculares' => $unidadesCurriculares,
        ]);
    }

    /**
     * Descarga del Plan de Estudios, Cronograma Diario y Guía de Supervivencia del Intensivo en PDF.
     */
    public function descargarGuiaIntensivoPdf(Request $request, PdfGeneratorService $pdfService)
    {
        $uncId = $request->input('unc_id');
        $unidad = null;

        if ($uncId) {
            $unidad = UnidadCurricular::find($uncId);
        }

        $asignatura = $unidad ? $unidad->nombre_unc : ($request->input('materia') ?: 'Bases de Datos / Programación Avanzada');
        $codigo = $unidad ? $unidad->codigo_unc : ($request->input('codigo') ?: 'INF-2204');

        $data = [
            'titulo'     => 'Plan y Cronograma de Curso Intensivo',
            'asignatura' => $asignatura,
            'codigo'     => $codigo,
        ];

        $nombreArchivo = 'Plan_Intensivo_' . str_replace([' ', '/', '\\'], '_', $codigo) . '.pdf';

        return $pdfService->make('reportes.pdf.guia_intensivo', $data, $nombreArchivo, 'letter', 'portrait')
            ->download();
    }

    /**
     * Exportación de la Auditoría y Listado de Secciones Intensivas en PDF.
     */
    public function exportarChequeoIntensivoPdf(Request $request, PdfGeneratorService $pdfService)
    {
        $secciones = Seccion::query()
            ->with([
                'cohorteSede.cohorte',
                'mallaCurricular.pnf',
                'unidadesDocentes.mallaCurricularDetalle.unidadCurricular',
                'unidadesDocentes.docente',
            ])
            ->where(function ($q) {
                $q->where('tipo_seccion', 'Intensivo')
                  ->orWhere('tis_id', 5);
            })
            ->orderBy('nombre_sec')
            ->get();

        $data = [
            'titulo'    => 'Listado de Chequeo de Cursos Intensivos',
            'secciones' => $secciones,
        ];

        return $pdfService->make('reportes.pdf.listado_secciones_intensivo', $data, 'Listado_Chequeo_Intensivo.pdf', 'letter', 'portrait')
            ->download();
    }

    /**
     * Listado De Clases.
     */
    public function listadoClases(Request $request): Response
    {
        $dia = $request->input('dia');
        $clasesQuery = SeccionHorario::query()
            ->with([
                'seccionUnidadDocente.seccion',
                'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                'seccionUnidadDocente.docente',
                'aula',
            ])
            ->where('status', true);

        if ($dia) {
            $clasesQuery->where('dia_semana', $dia);
        }

        $clases = $clasesQuery->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('ProgramacionAcademica/Informes/ListadoClases', [
            'clases'  => $clases,
            'filters' => $request->only(['dia']),
        ]);
    }

    /**
     * Listado De Secciones (General).
     */
    public function listadoSecciones(Request $request): Response
    {
        $search = $request->input('search');

        $secciones = Seccion::query()
            ->with([
                'cohorteSede.cohorte',
                'mallaCurricular',
                'unidadesDocentes.mallaCurricularDetalle.unidadCurricular',
                'unidadesDocentes.docente',
            ])
            ->when($search, function ($q, $search) {
                $q->where('nombre_sec', 'ilike', "%{$search}%")
                  ->orWhere('codigo_sec', 'ilike', "%{$search}%");
            })
            ->orderBy('nombre_sec')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('ProgramacionAcademica/Informes/ListadoSecciones', [
            'secciones' => $secciones,
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Secciones Abiertas (Solo con estatus activo).
     */
    public function seccionesAbiertas(Request $request): Response
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
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('ProgramacionAcademica/Informes/SeccionesAbiertas', [
            'secciones' => $secciones,
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Horario De Aula (Cuadrícula de ocupación por espacio físico).
     */
    public function horarioAula(Request $request): Response
    {
        $aulaId = $request->input('aula_id');
        $aulas = Aula::where('status', true)->orderBy('codigo_aula_aul')->get();

        $horarios = [];
        $aulaSeleccionada = null;

        if ($aulaId) {
            $aulaSeleccionada = Aula::find($aulaId);
            if ($aulaSeleccionada) {
                $horarios = SeccionHorario::with([
                    'seccionUnidadDocente.seccion',
                    'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                    'seccionUnidadDocente.docente',
                ])
                ->where('aul_id', $aulaId)
                ->where('status', true)
                ->orderBy('dia_semana')
                ->orderBy('hora_inicio')
                ->get();
            }
        } elseif ($aulas->isNotEmpty()) {
            $aulaSeleccionada = $aulas->first();
            $horarios = SeccionHorario::with([
                'seccionUnidadDocente.seccion',
                'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                'seccionUnidadDocente.docente',
            ])
            ->where('aul_id', $aulaSeleccionada->id_aul)
            ->where('status', true)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();
        }

        return Inertia::render('ProgramacionAcademica/Informes/HorarioAula', [
            'aulas'            => $aulas,
            'aulaSeleccionada' => $aulaSeleccionada,
            'horarios'         => $horarios,
        ]);
    }

    /**
     * Horario De Docente (Carga horaria de un profesor).
     */
    public function horarioDocente(Request $request): Response
    {
        $docenteId = $request->input('docente_id');
        $docentes = User::query()->orderBy('name')->limit(50)->get(['id', 'name', 'email']);

        $horarios = [];
        $docenteSeleccionado = null;

        if ($docenteId) {
            $docenteSeleccionado = User::find($docenteId);
            if ($docenteSeleccionado) {
                $horarios = SeccionHorario::with([
                    'seccionUnidadDocente.seccion',
                    'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                    'aula',
                ])
                ->whereHas('seccionUnidadDocente', function ($q) use ($docenteId) {
                    $q->where('docente_id', $docenteId);
                })
                ->where('status', true)
                ->orderBy('dia_semana')
                ->orderBy('hora_inicio')
                ->get();
            }
        } elseif ($docentes->isNotEmpty()) {
            $docenteSeleccionado = $docentes->first();
            $horarios = SeccionHorario::with([
                'seccionUnidadDocente.seccion',
                'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                'aula',
            ])
            ->whereHas('seccionUnidadDocente', function ($q) use ($docenteSeleccionado) {
                $q->where('docente_id', $docenteSeleccionado->id);
            })
            ->where('status', true)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();
        }

        return Inertia::render('ProgramacionAcademica/Informes/HorarioDocente', [
            'docentes'            => $docentes,
            'docenteSeleccionado' => $docenteSeleccionado,
            'horarios'            => $horarios,
        ]);
    }

    /**
     * Horario De Sección (Cuadrícula completa de una sección).
     */
    public function horarioSeccion(Request $request): Response
    {
        $seccionId = $request->input('seccion_id');
        $secciones = Seccion::where('status', true)->orderBy('nombre_sec')->get();

        $horarios = [];
        $seccionSeleccionada = null;

        if ($seccionId) {
            $seccionSeleccionada = Seccion::find($seccionId);
            if ($seccionSeleccionada) {
                $horarios = SeccionHorario::with([
                    'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                    'seccionUnidadDocente.docente',
                    'aula',
                ])
                ->whereHas('seccionUnidadDocente', function ($q) use ($seccionId) {
                    $q->where('sec_id', $seccionId);
                })
                ->where('status', true)
                ->orderBy('dia_semana')
                ->orderBy('hora_inicio')
                ->get();
            }
        } elseif ($secciones->isNotEmpty()) {
            $seccionSeleccionada = $secciones->first();
            $horarios = SeccionHorario::with([
                'seccionUnidadDocente.mallaCurricularDetalle.unidadCurricular',
                'seccionUnidadDocente.docente',
                'aula',
            ])
            ->whereHas('seccionUnidadDocente', function ($q) use ($seccionSeleccionada) {
                $q->where('sec_id', $seccionSeleccionada->id_sec);
            })
            ->where('status', true)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();
        }

        return Inertia::render('ProgramacionAcademica/Informes/HorarioSeccion', [
            'secciones'           => $secciones,
            'seccionSeleccionada' => $seccionSeleccionada,
            'horarios'            => $horarios,
        ]);
    }

    /**
     * Emisión oficial en PDF de Horarios por Semanas y por Turnos en Hoja Vertical.
     */
    /**
     * Emisión oficial en PDF de Horarios por Semanas y por Turnos en Hoja Vertical.
     * Basado en la plantilla oficial de Horario de Estudiante / Sección UPTP.
     */
    public function exportarHorariosSemanalesTurnosPdf(Request $request, PdfGeneratorService $pdfService)
    {
        $seccionId = $request->input('seccion_id');
        $seccionFiltro = $seccionId ? Seccion::find($seccionId) : null;

        $lapsoAcademico = DB::table('lapso_academico')->where('status_id', 1)->first()
            ?? DB::table('lapso_academico')->first();

        // 1. Clases registradas en seccion_horario (SUD)
        $clasesSho = DB::table('seccion_horario as sho')
            ->join('seccion_unidad_docente as sud', 'sud.id_sud', '=', 'sho.sud_id')
            ->join('seccion as sec', 'sec.id_sec', '=', 'sud.sec_id')
            ->join('malla_curricular_detalle as mcd', 'mcd.id_mcd', '=', 'sud.mcd_id')
            ->join('unidad_curricular as unc', 'unc.id_unc', '=', 'mcd.unc_id')
            ->leftJoin('users as u', 'u.id', '=', 'sud.docente_id')
            ->leftJoin('persona as per', function ($join) {
                $join->on('per.per_email', '=', 'u.email')
                     ->orOn(DB::raw("TRIM(LOWER(per.per_nombres || ' ' || per.per_apellidos))"), '=', DB::raw("TRIM(LOWER(u.name))"));
            })
            ->leftJoin('aula as aul', 'aul.id_aul', '=', 'sho.aul_id')
            ->where('sho.status', true)
            ->when($seccionId, fn($q) => $q->where('sec.id_sec', $seccionId))
            ->select(
                'sho.dia_semana',
                'sho.hora_inicio',
                'sho.hora_fin',
                'sec.nombre_sec',
                'sec.codigo_sec',
                'sec.turno_sec',
                'unc.nombre_unc',
                'unc.codigo_unc',
                DB::raw("COALESCE(u.name, 'Por Asignar') as nombre_docente"),
                DB::raw("COALESCE(per.per_telefono_movil, per.per_telefono_principal, '') as docente_telefono"),
                DB::raw("COALESCE(per.per_email, u.email, '') as docente_email"),
                DB::raw("COALESCE(aul.codigo_aula_aul, 'Sin Aula') as codigo_aula_aul")
            )
            ->get();

        // 2. Clases registradas en horario (maestro Grupo 3)
        $diasMap = [
            'Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Miercoles' => 3,
            'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6, 'Sabado' => 6, 'Domingo' => 7
        ];
        $clasesHor = DB::table('horario as h')
            ->leftJoin('carga_academica as caa', 'caa.id_caa', '=', 'h.caa_id')
            ->leftJoin('seccion as sec', 'sec.id_sec', '=', 'caa.sec_id')
            ->leftJoin('unidad_curricular as unc', 'unc.id_unc', '=', 'caa.unc_id')
            ->leftJoin('users as u', 'u.id', '=', 'caa.docente_per_id')
            ->leftJoin('persona as per', function ($join) {
                $join->on('per.per_email', '=', 'u.email')
                     ->orOn(DB::raw("TRIM(LOWER(per.per_nombres || ' ' || per.per_apellidos))"), '=', DB::raw("TRIM(LOWER(u.name))"));
            })
            ->leftJoin('aula as aul', 'aul.id_aul', '=', 'h.aul_id')
            ->where('h.status', true)
            ->when($seccionId, fn($q) => $q->where('sec.id_sec', $seccionId))
            ->select(
                'h.dia_semana_hor',
                'h.hora_inicio_hor as hora_inicio',
                'h.hora_fin_hor as hora_fin',
                'sec.nombre_sec',
                'sec.codigo_sec',
                'sec.turno_sec',
                'unc.nombre_unc',
                'unc.codigo_unc',
                DB::raw("COALESCE(u.name, 'Por Asignar') as nombre_docente"),
                DB::raw("COALESCE(per.per_telefono_movil, per.per_telefono_principal, '') as docente_telefono"),
                DB::raw("COALESCE(per.per_email, u.email, '') as docente_email"),
                DB::raw("COALESCE(aul.codigo_aula_aul, 'Sin Aula') as codigo_aula_aul")
            )
            ->get()
            ->map(function ($c) use ($diasMap) {
                $c->dia_semana = $diasMap[$c->dia_semana_hor] ?? 1;
                return $c;
            });

        $clases = $clasesSho->concat($clasesHor);

        // Estructura oficial de Turnos con Bloques exactos UPTP
        $turnos = [
            [
                'codigo'      => 'MAT',
                'nombre'      => 'MATUTINO',
                'bloques'     => [
                    ['numero' => 1, 'hora_texto_inicio' => '07:30:00', 'hora_texto_fin' => '08:15:00', 'inicio' => '07:00:00', 'fin' => '08:15:00'],
                    ['numero' => 2, 'hora_texto_inicio' => '08:15:00', 'hora_texto_fin' => '09:00:00', 'inicio' => '08:15:00', 'fin' => '09:10:00'],
                    ['numero' => 3, 'hora_texto_inicio' => '09:20:00', 'hora_texto_fin' => '10:05:00', 'inicio' => '09:10:00', 'fin' => '10:05:00'],
                    ['numero' => 4, 'hora_texto_inicio' => '10:05:00', 'hora_texto_fin' => '10:50:00', 'inicio' => '10:05:00', 'fin' => '10:50:00'],
                    ['numero' => 5, 'hora_texto_inicio' => '10:50:00', 'hora_texto_fin' => '11:35:00', 'inicio' => '10:50:00', 'fin' => '11:35:00'],
                    ['numero' => 6, 'hora_texto_inicio' => '11:35:00', 'hora_texto_fin' => '12:20:00', 'inicio' => '11:35:00', 'fin' => '12:30:00'],
                ],
            ],
            [
                'codigo'      => 'VES',
                'nombre'      => 'VESPERTINO',
                'bloques'     => [
                    ['numero' => 1, 'hora_texto_inicio' => '01:30:00', 'hora_texto_fin' => '02:15:00', 'inicio' => '13:00:00', 'fin' => '14:15:00'],
                    ['numero' => 2, 'hora_texto_inicio' => '02:15:00', 'hora_texto_fin' => '03:00:00', 'inicio' => '14:15:00', 'fin' => '15:00:00'],
                    ['numero' => 3, 'hora_texto_inicio' => '03:00:00', 'hora_texto_fin' => '03:45:00', 'inicio' => '15:00:00', 'fin' => '15:45:00'],
                    ['numero' => 4, 'hora_texto_inicio' => '03:45:00', 'hora_texto_fin' => '04:30:00', 'inicio' => '15:45:00', 'fin' => '16:30:00'],
                    ['numero' => 5, 'hora_texto_inicio' => '04:30:00', 'hora_texto_fin' => '05:15:00', 'inicio' => '16:30:00', 'fin' => '17:15:00'],
                    ['numero' => 6, 'hora_texto_inicio' => '05:15:00', 'hora_texto_fin' => '06:00:00', 'inicio' => '17:15:00', 'fin' => '18:30:00'],
                ],
            ],
            [
                'codigo'      => 'NOC',
                'nombre'      => 'NOCTURNO',
                'bloques'     => [
                    ['numero' => 1, 'hora_texto_inicio' => '06:00:00', 'hora_texto_fin' => '06:40:00', 'inicio' => '18:00:00', 'fin' => '18:40:00'],
                    ['numero' => 2, 'hora_texto_inicio' => '06:40:00', 'hora_texto_fin' => '07:20:00', 'inicio' => '18:40:00', 'fin' => '19:20:00'],
                    ['numero' => 3, 'hora_texto_inicio' => '07:20:00', 'hora_texto_fin' => '08:00:00', 'inicio' => '19:20:00', 'fin' => '20:00:00'],
                    ['numero' => 4, 'hora_texto_inicio' => '08:00:00', 'hora_texto_fin' => '08:40:00', 'inicio' => '20:00:00', 'fin' => '20:40:00'],
                    ['numero' => 5, 'hora_texto_inicio' => '08:40:00', 'hora_texto_fin' => '09:20:00', 'inicio' => '20:40:00', 'fin' => '21:20:00'],
                    ['numero' => 6, 'hora_texto_inicio' => '09:20:00', 'hora_texto_fin' => '10:00:00', 'inicio' => '21:20:00', 'fin' => '22:30:00'],
                ],
            ],
        ];

        // 7 Días de la semana según el formato oficial UPTP
        $diasSemanales = [
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIÉRCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SABADO',
            7 => 'DOMINGO',
        ];

        // Extracción dinámica de materias reales para la leyenda inferior
        $materiasUnicas = [];
        foreach ($clases as $cl) {
            $key = $cl->codigo_unc ?? $cl->nombre_unc;
            if ($key && !isset($materiasUnicas[$key])) {
                $materiasUnicas[$key] = [
                    'codigo'   => $cl->codigo_unc ?: 'S/C',
                    'nombre'   => $cl->nombre_unc,
                    'docente'  => $cl->nombre_docente,
                    'telefono' => $cl->docente_telefono,
                    'email'    => $cl->docente_email,
                ];
            }
        }

        ksort($materiasUnicas);
        $materiasLista = array_values($materiasUnicas);

        // Determinación dinámica del encabezado de persona / sección
        $user = auth()->user();
        $tituloDoc = $seccionFiltro ? 'HORARIO DE SECCIÓN' : 'HORARIO DE ESTUDIANTE';

        if ($seccionFiltro) {
            $nombrePersona = 'SECCIÓN: ' . mb_strtoupper($seccionFiltro->nombre_sec) . ' (' . ($seccionFiltro->codigo_sec ?: 'S/C') . ')';
        } elseif ($request->filled('estudiante')) {
            $nombrePersona = mb_strtoupper($request->input('estudiante'));
        } elseif ($user) {
            $nombrePersona = mb_strtoupper($user->name);
        } else {
            $nombrePersona = 'HORARIO GENERAL';
        }

        $lapsoCodigo = $lapsoAcademico->codigo_lap ?? $lapsoAcademico->nombre_lap ?? 'ACTIVO';

        $data = [
            'titulo'         => $tituloDoc,
            'tituloDoc'      => $tituloDoc,
            'lapsoAcademico' => $lapsoAcademico,
            'lapsoCodigo'    => $lapsoCodigo,
            'seccionFiltro'  => $seccionFiltro,
            'nombrePersona'  => $nombrePersona,
            'turnos'         => $turnos,
            'diasSemanales'  => $diasSemanales,
            'clases'         => $clases,
            'materiasLista'  => $materiasLista,
        ];

        $filename = 'Horario_Estudiante_' . ($seccionFiltro ? $seccionFiltro->codigo_sec : 'General') . '.pdf';

        return $pdfService->make('reportes.pdf.horarios_semanal_turnos', $data, $filename, 'letter', 'portrait')
            ->stream();
    }
}

