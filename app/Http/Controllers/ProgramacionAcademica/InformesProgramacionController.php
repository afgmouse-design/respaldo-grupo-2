<?php

/**
 * Propósito: Controlador de Informes, Cuadrículas y Exportación PDF de Programación Académica.
 * Historial: Refactorizado completamente sobre `Horario`, `CargaAcademica` (`pep_id -> persona_perfil.id_pep -> persona`), `Programa`, `TipoSeccion` y `status_id`, eliminando `SeccionHorario`, `SeccionUnidadDocente`, `docente_per_id` y `User.name`.
 */

namespace App\Http\Controllers\ProgramacionAcademica;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use App\Models\Personas\PersonaPerfil;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use App\Models\ProgramacionAcademica\Maestros\Horario;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Services\Reportes\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InformesProgramacionController extends Controller
{
    /**
     * Filtra secciones pertenecientes a la modalidad Intensiva vía tabla `tipo_seccion` (`tis_id`).
     */
    private function querySeccionesIntensivas()
    {
        return Seccion::query()
            ->with([
                'cohorteSede.cohorte',
                'programa',
                'tipoSeccion',
                'mallaCurricular.programa',
                'cargasAcademicas' => fn ($q) => $q->where('status_id', 1),
                'cargasAcademicas.unidadCurricular',
                'cargasAcademicas.docentePerfil.persona',
            ])
            ->where(function ($q) {
                $q->whereHas('tipoSeccion', function ($t) {
                    $t->where('codigo_tis', 'INT')
                        ->orWhere('nombre_tis', 'ilike', '%intensiv%');
                })->orWhere('tis_id', 5);
            })
            ->orderBy('nombre_sec');
    }

    /**
     * Consulta base de horarios activos con su carga académica, sección, asignatura, docente y aula.
     */
    private function queryHorariosActivos()
    {
        return Horario::query()
            ->with([
                'cargaAcademica.seccion',
                'cargaAcademica.unidadCurricular',
                'cargaAcademica.docentePerfil.persona',
                'aula',
            ])
            ->where('status_id', 1);
    }

    /**
     * Listado De Chequeo y Gestor de Cursos Intensivos.
     */
    public function chequeoIntensivo(Request $request): Response
    {
        $seccionesIntensivas = $this->querySeccionesIntensivas()
            ->paginate(15)
            ->withQueryString();

        $unidadesCurriculares = UnidadCurricular::query()
            ->where('status_id', 1)
            ->orderBy('codigo_unc')
            ->get(['id_unc', 'codigo_unc', 'nombre_unc', 'tuc_id']);

        return Inertia::render('ProgramacionAcademica/Informes/ListadoChequeoIntensivo', [
            'secciones'            => $seccionesIntensivas,
            'unidadesCurriculares' => $unidadesCurriculares,
        ]);
    }

    /**
     * Descarga del Plan y Cronograma Diario del Intensivo en PDF.
     */
    public function descargarGuiaIntensivoPdf(Request $request, PdfGeneratorService $pdfService)
    {
        $uncId = $request->input('unc_id');
        $unidad = $uncId ? UnidadCurricular::find($uncId) : null;

        $asignatura = $unidad ? $unidad->nombre_unc : ($request->input('materia') ?: 'Bases de Datos / Programación Avanzada');
        $codigo = $unidad ? ($unidad->codigo_unc ?: 'S/C') : ($request->input('codigo') ?: 'INF-2204');

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
        $secciones = $this->querySeccionesIntensivas()->get();

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

        $clases = $this->queryHorariosActivos()
            ->when($dia, fn ($q) => $q->where('dia_semana_hor', $dia))
            ->orderBy('dia_semana_hor')
            ->orderBy('hora_inicio_hor')
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
                'programa',
                'tipoSeccion',
                'mallaCurricular',
                'cargasAcademicas' => fn ($q) => $q->where('status_id', 1),
                'cargasAcademicas.unidadCurricular',
                'cargasAcademicas.docentePerfil.persona',
            ])
            ->when($search, function ($q, string $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nombre_sec', 'ilike', "%{$search}%")
                        ->orWhere('codigo_sec', 'ilike', "%{$search}%");
                });
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
     * Secciones Abiertas (`status_id = 1`).
     */
    public function seccionesAbiertas(Request $request): Response
    {
        $search = $request->input('search');

        $secciones = Seccion::query()
            ->with(['cohorteSede.cohorte', 'mallaCurricular', 'programa'])
            ->where('status_id', 1)
            ->when($search, function ($q, string $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nombre_sec', 'ilike', "%{$search}%")
                        ->orWhere('codigo_sec', 'ilike', "%{$search}%");
                });
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
        $aulas = Aula::where('status_id', 1)->orderBy('codigo_aula_aul')->get();

        $aulaSeleccionada = $aulaId ? Aula::find($aulaId) : $aulas->first();
        $horarios = $aulaSeleccionada
            ? $this->queryHorariosActivos()
                ->where('aul_id', $aulaSeleccionada->id_aul)
                ->orderBy('dia_semana_hor')
                ->orderBy('hora_inicio_hor')
                ->get()
            : [];

        return Inertia::render('ProgramacionAcademica/Informes/HorarioAula', [
            'aulas'            => $aulas,
            'aulaSeleccionada' => $aulaSeleccionada,
            'horarios'         => $horarios,
        ]);
    }

    /**
     * Horario De Docente (Carga horaria filtrada por perfil docente `pep_id`).
     */
    public function horarioDocente(Request $request): Response
    {
        $pepId = $request->input('pep_id', $request->input('docente_id'));
        $docentes = PersonaPerfil::obtenerDocentesActivosParaSelect();

        $docenteSeleccionado = $pepId
            ? $docentes->firstWhere('id_pep', (int) $pepId)
            : $docentes->first();

        $horarios = $docenteSeleccionado
            ? $this->queryHorariosActivos()
                ->whereHas('cargaAcademica', fn ($q) => $q->where('pep_id', $docenteSeleccionado->id_pep))
                ->orderBy('dia_semana_hor')
                ->orderBy('hora_inicio_hor')
                ->get()
            : [];

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
        $secciones = Seccion::where('status_id', 1)->orderBy('nombre_sec')->get();

        $seccionSeleccionada = $seccionId ? Seccion::find($seccionId) : $secciones->first();
        $horarios = $seccionSeleccionada
            ? $this->queryHorariosActivos()
                ->whereHas('cargaAcademica', fn ($q) => $q->where('sec_id', $seccionSeleccionada->id_sec))
                ->orderBy('dia_semana_hor')
                ->orderBy('hora_inicio_hor')
                ->get()
            : [];

        return Inertia::render('ProgramacionAcademica/Informes/HorarioSeccion', [
            'secciones'           => $secciones,
            'seccionSeleccionada' => $seccionSeleccionada,
            'horarios'            => $horarios,
        ]);
    }

    /**
     * Emisión oficial en PDF de Horarios por Semanas y Turnos sobre `horario` + `carga_academica`.
     */
    public function exportarHorariosSemanalesTurnosPdf(Request $request, PdfGeneratorService $pdfService)
    {
        $seccionId = $request->input('seccion_id');
        $seccionFiltro = $seccionId ? Seccion::find($seccionId) : null;

        $lapsoAcademico = DB::table('lapso_academico')
            ->where('status_id', 1)
            ->whereNull('deleted_at')
            ->first()
            ?? DB::table('lapso_academico')->whereNull('deleted_at')->first();

        $diasMap = [
            'Lunes'     => 1, '1' => 1,
            'Martes'    => 2, '2' => 2,
            'Miércoles' => 3, 'Miercoles' => 3, '3' => 3,
            'Jueves'    => 4, '4' => 4,
            'Viernes'   => 5, '5' => 5,
            'Sábado'    => 6, 'Sabado' => 6, '6' => 6,
            'Domingo'   => 7, '7' => 7,
        ];

        $clases = DB::table('horario as h')
            ->join('carga_academica as caa', 'caa.id_caa', '=', 'h.caa_id')
            ->join('seccion as sec', 'sec.id_sec', '=', 'caa.sec_id')
            ->join('unidad_curricular as unc', 'unc.id_unc', '=', 'caa.unc_id')
            ->leftJoin('persona_perfil as pep', 'pep.id_pep', '=', 'caa.pep_id')
            ->leftJoin('persona as per', 'per.id_per', '=', 'pep.per_id')
            ->leftJoin('aula as aul', 'aul.id_aul', '=', 'h.aul_id')
            ->where('h.status_id', 1)
            ->whereNull('h.deleted_at')
            ->where('caa.status_id', 1)
            ->whereNull('caa.deleted_at')
            ->when($seccionId, fn ($q) => $q->where('sec.id_sec', $seccionId))
            ->select(
                'h.dia_semana_hor',
                'h.hora_inicio_hor as hora_inicio',
                'h.hora_fin_hor as hora_fin',
                'sec.nombre_sec',
                'sec.codigo_sec',
                'sec.turno_sec',
                'unc.nombre_unc',
                'unc.codigo_unc',
                DB::raw("COALESCE(NULLIF(TRIM(per.nombres_per || ' ' || per.apellidos_per), ''), 'Por Asignar') as nombre_docente"),
                DB::raw("COALESCE(per.telefono_movil_per, per.telefono_fijo_per, '') as docente_telefono"),
                DB::raw("COALESCE(per.correo_per, '') as docente_email"),
                DB::raw("COALESCE(aul.codigo_aula_aul, 'Sin Aula') as codigo_aula_aul")
            )
            ->get()
            ->map(function ($c) use ($diasMap) {
                $c->dia_semana = $diasMap[$c->dia_semana_hor] ?? 1;

                return $c;
            });

        $turnos = [
            [
                'codigo'  => 'MAT',
                'nombre'  => 'MATUTINO',
                'bloques' => [
                    ['numero' => 1, 'hora_texto_inicio' => '07:30:00', 'hora_texto_fin' => '08:15:00', 'inicio' => '07:00:00', 'fin' => '08:15:00'],
                    ['numero' => 2, 'hora_texto_inicio' => '08:15:00', 'hora_texto_fin' => '09:00:00', 'inicio' => '08:15:00', 'fin' => '09:10:00'],
                    ['numero' => 3, 'hora_texto_inicio' => '09:20:00', 'hora_texto_fin' => '10:05:00', 'inicio' => '09:10:00', 'fin' => '10:05:00'],
                    ['numero' => 4, 'hora_texto_inicio' => '10:05:00', 'hora_texto_fin' => '10:50:00', 'inicio' => '10:05:00', 'fin' => '10:50:00'],
                    ['numero' => 5, 'hora_texto_inicio' => '10:50:00', 'hora_texto_fin' => '11:35:00', 'inicio' => '10:50:00', 'fin' => '11:35:00'],
                    ['numero' => 6, 'hora_texto_inicio' => '11:35:00', 'hora_texto_fin' => '12:20:00', 'inicio' => '11:35:00', 'fin' => '12:30:00'],
                ],
            ],
            [
                'codigo'  => 'VES',
                'nombre'  => 'VESPERTINO',
                'bloques' => [
                    ['numero' => 1, 'hora_texto_inicio' => '01:30:00', 'hora_texto_fin' => '02:15:00', 'inicio' => '13:00:00', 'fin' => '14:15:00'],
                    ['numero' => 2, 'hora_texto_inicio' => '02:15:00', 'hora_texto_fin' => '03:00:00', 'inicio' => '14:15:00', 'fin' => '15:00:00'],
                    ['numero' => 3, 'hora_texto_inicio' => '03:00:00', 'hora_texto_fin' => '03:45:00', 'inicio' => '15:00:00', 'fin' => '15:45:00'],
                    ['numero' => 4, 'hora_texto_inicio' => '03:45:00', 'hora_texto_fin' => '04:30:00', 'inicio' => '15:45:00', 'fin' => '16:30:00'],
                    ['numero' => 5, 'hora_texto_inicio' => '04:30:00', 'hora_texto_fin' => '05:15:00', 'inicio' => '16:30:00', 'fin' => '17:15:00'],
                    ['numero' => 6, 'hora_texto_inicio' => '05:15:00', 'hora_texto_fin' => '06:00:00', 'inicio' => '17:15:00', 'fin' => '18:30:00'],
                ],
            ],
            [
                'codigo'  => 'NOC',
                'nombre'  => 'NOCTURNO',
                'bloques' => [
                    ['numero' => 1, 'hora_texto_inicio' => '06:00:00', 'hora_texto_fin' => '06:40:00', 'inicio' => '18:00:00', 'fin' => '18:40:00'],
                    ['numero' => 2, 'hora_texto_inicio' => '06:40:00', 'hora_texto_fin' => '07:20:00', 'inicio' => '18:40:00', 'fin' => '19:20:00'],
                    ['numero' => 3, 'hora_texto_inicio' => '07:20:00', 'hora_texto_fin' => '08:00:00', 'inicio' => '19:20:00', 'fin' => '20:00:00'],
                    ['numero' => 4, 'hora_texto_inicio' => '08:00:00', 'hora_texto_fin' => '08:40:00', 'inicio' => '20:00:00', 'fin' => '20:40:00'],
                    ['numero' => 5, 'hora_texto_inicio' => '08:40:00', 'hora_texto_fin' => '09:20:00', 'inicio' => '20:40:00', 'fin' => '21:20:00'],
                    ['numero' => 6, 'hora_texto_inicio' => '09:20:00', 'hora_texto_fin' => '10:00:00', 'inicio' => '21:20:00', 'fin' => '22:30:00'],
                ],
            ],
        ];

        $diasSemanales = [
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIÉRCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SÁBADO',
            7 => 'DOMINGO',
        ];

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

        $user = auth()->user();
        $tituloDoc = $seccionFiltro ? 'HORARIO DE SECCIÓN' : 'HORARIO DE ESTUDIANTE';

        if ($seccionFiltro) {
            $nombrePersona = 'SECCIÓN: ' . mb_strtoupper($seccionFiltro->nombre_sec) . ' (' . ($seccionFiltro->codigo_sec ?: 'S/C') . ')';
        } elseif ($request->filled('estudiante')) {
            $nombrePersona = mb_strtoupper((string) $request->input('estudiante'));
        } elseif ($user) {
            $nombrePersona = mb_strtoupper((string) ($user->username ?? $user->email));
        } else {
            $nombrePersona = 'HORARIO GENERAL';
        }

        $lapsoCodigo = $lapsoAcademico->codigo_laa ?? $lapsoAcademico->nombre_laa ?? 'ACTIVO';

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
