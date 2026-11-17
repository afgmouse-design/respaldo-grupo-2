<?php

namespace Database\Seeders\SeguridadYAccesos;

use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Submodulo;
use App\Models\Seguridad\Vista;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Navegación y Menús Laterales (Sidebar - 3 Niveles).
 *
 * Propósito: Sincronizar de forma idempotente la jerarquía de navegación de SOGAC
 * (`modulo` -> `submodulo` -> `vista`) con claves únicas (`clave_vis`), rutas oficiales
 * e íconos Lucide en formato PascalCase sobre las tablas de `sogac_mio.sql`.
 *
 * Historial:
 *  - 2026-11-14: Reescrito íntegramente sobre `modulo(id_mod)`, `submodulo(id_sbm)` y
 *    `vista(id_vis, clave_vis, ruta_vis)` reemplazando `activo` / `id_modulo` / `id_submodulo`.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\NavigationSeeder"
 */
class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('modulo') || !Schema::hasTable('submodulo') || !Schema::hasTable('vista')) {
            $this->command?->warn('Tablas de navegación ("modulo", "submodulo", "vista") no existen en la BD actual. Omitiendo.');
            return;
        }

        $estructura = $this->definirEstructura();

        DB::transaction(function () use ($estructura): void {
            foreach ($estructura as $modData) {
                $modulo = Modulo::withTrashed()->updateOrCreate(
                    ['nombre_mod' => $modData['nombre']],
                    [
                        'es_sistema_mod'  => (bool) ($modData['es_sistema'] ?? false),
                        'descripcion_mod' => $modData['descripcion'] ?? $modData['nombre'],
                        'icono_mod'       => $modData['icono'],
                        'orden_mod'       => $modData['orden'],
                        'status_id'       => 1,
                        'deleted_at'      => null,
                    ]
                );

                foreach ($modData['submodulos'] as $subData) {
                    $submodulo = Submodulo::withTrashed()->updateOrCreate(
                        ['mod_id' => $modulo->id_mod, 'nombre_sbm' => $subData['nombre']],
                        [
                            'icono_sbm'  => $subData['icono'],
                            'orden_sbm'  => $subData['orden'],
                            'status_id'  => 1,
                            'deleted_at' => null,
                        ]
                    );

                    foreach ($subData['vistas'] as $visData) {
                        Vista::withTrashed()->updateOrCreate(
                            ['clave_vis' => $visData['clave']],
                            [
                                'mod_id'          => $modulo->id_mod,
                                'sbm_id'          => $submodulo->id_sbm,
                                'nombre_vis'      => $visData['nombre'],
                                'descripcion_vis' => $visData['descripcion'] ?? $visData['nombre'],
                                'ruta_vis'        => $visData['ruta'],
                                'icono_vis'       => $visData['icono'],
                                'orden_vis'       => $visData['orden'],
                                'status_id'       => 1,
                                'deleted_at'      => null,
                            ]
                        );
                    }
                }
            }
        });

        $this->command?->info('✓ Estructura de navegación sincronizada correctamente sobre modulo/submodulo/vista.');
    }

    /**
     * Define la estructura oficial de 3 niveles de navegación SOGAC.
     *
     * @return array<int, array<string, mixed>>
     */
    private function definirEstructura(): array
    {
        return [
            [
                'nombre'      => 'Estructura Geográfica',
                'descripcion' => 'Catálogo político-territorial del sistema.',
                'icono'       => 'Globe',
                'orden'       => 1,
                'submodulos'  => [
                    [
                        'nombre' => 'Catálogo Territorial',
                        'icono'  => 'MapPin',
                        'orden'  => 1,
                        'vistas' => [
                            ['clave' => 'geografia.pais', 'nombre' => 'Países', 'ruta' => 'geografia.pais.index', 'icono' => 'Globe', 'orden' => 1],
                            ['clave' => 'geografia.estado', 'nombre' => 'Estados', 'ruta' => 'geografia.estado.index', 'icono' => 'Map', 'orden' => 2],
                            ['clave' => 'geografia.municipio', 'nombre' => 'Municipios', 'ruta' => 'geografia.municipio.index', 'icono' => 'Compass', 'orden' => 3],
                            ['clave' => 'geografia.parroquia', 'nombre' => 'Parroquias', 'ruta' => 'geografia.parroquia.index', 'icono' => 'Navigation', 'orden' => 4],
                            ['clave' => 'geografia.localidad', 'nombre' => 'Localidades', 'ruta' => 'geografia.localidad.index', 'icono' => 'MapPin', 'orden' => 5],
                        ],
                    ],
                ],
            ],
            [
                'nombre'      => 'Organización Institucional',
                'descripcion' => 'Universidades, sedes académicas y condiciones de sede.',
                'icono'       => 'Landmark',
                'orden'       => 2,
                'submodulos'  => [
                    [
                        'nombre' => 'Institución y Sedes',
                        'icono'  => 'Building2',
                        'orden'  => 1,
                        'vistas' => [
                            ['clave' => 'organizacion.universidad', 'nombre' => 'Universidades', 'ruta' => 'organizacion.universidad.index', 'icono' => 'Landmark', 'orden' => 1],
                            ['clave' => 'organizacion.sede', 'nombre' => 'Sedes', 'ruta' => 'organizacion.sede.index', 'icono' => 'Building2', 'orden' => 2],
                            ['clave' => 'organizacion.condicion_sede', 'nombre' => 'Condiciones de Sede', 'ruta' => 'condicion.index', 'icono' => 'ShieldCheck', 'orden' => 3],
                        ],
                    ],
                ],
            ],
            [
                'nombre'      => 'Estructura Académica',
                'descripcion' => 'Programas de formación, mallas curriculares, calendario y escalas.',
                'icono'       => 'BookOpen',
                'orden'       => 3,
                'submodulos'  => [
                    [
                        'nombre' => 'Diseño Curricular',
                        'icono'  => 'GraduationCap',
                        'orden'  => 1,
                        'vistas' => [
                            ['clave' => 'academico.programa', 'nombre' => 'Programas / PNF', 'ruta' => 'academico.pnf.index', 'icono' => 'GraduationCap', 'orden' => 1],
                            ['clave' => 'academico.malla_curricular', 'nombre' => 'Mallas Curriculares', 'ruta' => 'academico.malla-curricular.index', 'icono' => 'FileSpreadsheet', 'orden' => 2],
                            ['clave' => 'academico.unidad_curricular', 'nombre' => 'Unidades Curriculares', 'ruta' => 'academico.unidad-curricular.index', 'icono' => 'BookOpen', 'orden' => 3],
                            ['clave' => 'academico.trayecto', 'nombre' => 'Trayectos', 'ruta' => 'academico.trayecto.index', 'icono' => 'Layers', 'orden' => 4],
                            ['clave' => 'academico.semestre', 'nombre' => 'Semestres', 'ruta' => 'semestres.index', 'icono' => 'ListOrdered', 'orden' => 5],
                        ],
                    ],
                    [
                        'nombre' => 'Catálogos Curriculares',
                        'icono'  => 'FolderOpen',
                        'orden'  => 2,
                        'vistas' => [
                            ['clave' => 'academico.eje_curricular', 'nombre' => 'Ejes Curriculares', 'ruta' => 'academico.eje-curricular.index', 'icono' => 'Compass', 'orden' => 1],
                            ['clave' => 'academico.escala_medicion', 'nombre' => 'Escalas de Medición', 'ruta' => 'academico.escala-medicion.index', 'icono' => 'Scale', 'orden' => 2],
                            ['clave' => 'academico.tipo_unidad_curricular', 'nombre' => 'Tipos de Unidad Curricular', 'ruta' => 'academico.tipo-unidad-curricular.index', 'icono' => 'Tags', 'orden' => 3],
                        ],
                    ],
                    [
                        'nombre' => 'Tiempo Académico',
                        'icono'  => 'CalendarDays',
                        'orden'  => 3,
                        'vistas' => [
                            ['clave' => 'academico.ano_academico', 'nombre' => 'Años Académicos', 'ruta' => 'academico.ano-academico.index', 'icono' => 'Calendar', 'orden' => 1],
                            ['clave' => 'academico.lapso_academico', 'nombre' => 'Lapsos Académicos', 'ruta' => 'academico.lapso-academico.index', 'icono' => 'CalendarDays', 'orden' => 2],
                            ['clave' => 'academico.tipo_lapso', 'nombre' => 'Tipos de Lapso', 'ruta' => 'tipo_lapso.index', 'icono' => 'Clock', 'orden' => 3],
                        ],
                    ],
                    [
                        'nombre' => 'Reportes Curriculares',
                        'icono'  => 'FileText',
                        'orden'  => 4,
                        'vistas' => [
                            ['clave' => 'academico.reportes.listado_malla', 'nombre' => 'Listado de Malla', 'ruta' => 'academico.reportes.listado-malla', 'icono' => 'FileText', 'orden' => 1],
                            ['clave' => 'academico.reportes.listado_malla_prelacion', 'nombre' => 'Malla con Prelaciones', 'ruta' => 'academico.reportes.listado-malla-prelacion', 'icono' => 'GitMerge', 'orden' => 2],
                            ['clave' => 'academico.reportes.relacion_unidades', 'nombre' => 'Relación de Unidades', 'ruta' => 'academico.reportes.relacion-unidades', 'icono' => 'ListChecks', 'orden' => 3],
                            ['clave' => 'academico.reportes.relacion_unidades_general', 'nombre' => 'Relación General de Unidades', 'ruta' => 'academico.reportes.relacion-unidades-general', 'icono' => 'FileCheck', 'orden' => 4],
                        ],
                    ],
                ],
            ],
            [
                'nombre'      => 'Programación Académica',
                'descripcion' => 'Oferta académica, cohortes, secciones, espacios físicos y horarios.',
                'icono'       => 'Calendar',
                'orden'       => 4,
                'submodulos'  => [
                    [
                        'nombre' => 'Lapsos y Cohortes',
                        'icono'  => 'Clock',
                        'orden'  => 1,
                        'vistas' => [
                            ['clave' => 'programacion.cohorte', 'nombre' => 'Cohortes', 'ruta' => 'cohorte.index', 'icono' => 'Users', 'orden' => 1],
                            ['clave' => 'programacion.sede_programa', 'nombre' => 'Sede Programa', 'ruta' => 'sede_pnf.index', 'icono' => 'Building2', 'orden' => 2],
                            ['clave' => 'programacion.cohorte_sede', 'nombre' => 'Cohortes por Sede', 'ruta' => 'cohorte_sede.index', 'icono' => 'School', 'orden' => 3],
                            ['clave' => 'programacion.cursos_intensivos', 'nombre' => 'Cursos Intensivos', 'ruta' => 'informes.chequeo_intensivo', 'icono' => 'Flame', 'orden' => 4],
                        ],
                    ],
                    [
                        'nombre' => 'Secciones',
                        'icono'  => 'Users',
                        'orden'  => 2,
                        'vistas' => [
                            ['clave' => 'programacion.seccion', 'nombre' => 'Secciones', 'ruta' => 'seccion.index', 'icono' => 'Layers', 'orden' => 1],
                            ['clave' => 'programacion.carga_academica', 'nombre' => 'Carga Académica y Docente', 'ruta' => 'seccion_unidad_docente.index', 'icono' => 'UserCheck', 'orden' => 2],
                            ['clave' => 'programacion.tipo_seccion', 'nombre' => 'Tipos de Sección', 'ruta' => 'tipo_seccion.index', 'icono' => 'Bookmark', 'orden' => 3],
                            ['clave' => 'programacion.seccion_abrir', 'nombre' => 'Abrir Sección', 'ruta' => 'seccion.abrir', 'icono' => 'DoorOpen', 'orden' => 4],
                            ['clave' => 'programacion.seccion_cerrar', 'nombre' => 'Cerrar Sección', 'ruta' => 'seccion.cerrar', 'icono' => 'DoorClosed', 'orden' => 5],
                            ['clave' => 'programacion.seccion_fusionar', 'nombre' => 'Fusionar Secciones', 'ruta' => 'seccion.fusionar', 'icono' => 'GitMerge', 'orden' => 6],
                            ['clave' => 'programacion.seccion_capacidad', 'nombre' => 'Capacidad por Sección', 'ruta' => 'seccion.capacidad', 'icono' => 'Users', 'orden' => 7],
                        ],
                    ],
                    [
                        'nombre' => 'Espacios y Tiempo',
                        'icono'  => 'Clock',
                        'orden'  => 3,
                        'vistas' => [
                            ['clave' => 'programacion.aula', 'nombre' => 'Aulas', 'ruta' => 'aula.index', 'icono' => 'School', 'orden' => 1],
                            ['clave' => 'programacion.tipo_aula', 'nombre' => 'Tipos de Aula', 'ruta' => 'tipo_aula.index', 'icono' => 'Bookmark', 'orden' => 2],
                            ['clave' => 'programacion.turno', 'nombre' => 'Turnos', 'ruta' => 'turno.index', 'icono' => 'Sun', 'orden' => 3],
                            ['clave' => 'programacion.horario', 'nombre' => 'Horarios', 'ruta' => 'horario.index', 'icono' => 'CalendarClock', 'orden' => 4],
                            ['clave' => 'programacion.horarios', 'nombre' => 'Programación de Horarios', 'ruta' => 'horarios.index', 'icono' => 'Calendar', 'orden' => 5],
                        ],
                    ],
                    [
                        'nombre' => 'Informes',
                        'icono'  => 'FileText',
                        'orden'  => 4,
                        'vistas' => [
                            ['clave' => 'programacion.informe_horario_estudiante', 'nombre' => 'Horario Estudiante', 'ruta' => 'seccion.horario_estudiante', 'icono' => 'GraduationCap', 'orden' => 1],
                            ['clave' => 'programacion.informe_chequeo_intensivo', 'nombre' => 'Chequeo Intensivo', 'ruta' => 'informes.chequeo_intensivo', 'icono' => 'FileCheck', 'orden' => 2],
                            ['clave' => 'programacion.informe_clases', 'nombre' => 'Listado de Clases', 'ruta' => 'informes.clases', 'icono' => 'Calendar', 'orden' => 3],
                            ['clave' => 'programacion.informe_secciones', 'nombre' => 'Listado de Secciones', 'ruta' => 'informes.secciones', 'icono' => 'Layers', 'orden' => 4],
                            ['clave' => 'programacion.informe_secciones_abiertas', 'nombre' => 'Secciones Abiertas', 'ruta' => 'informes.secciones_abiertas', 'icono' => 'DoorOpen', 'orden' => 5],
                            ['clave' => 'programacion.informe_horario_aula', 'nombre' => 'Horario de Aula', 'ruta' => 'informes.horario_aula', 'icono' => 'School', 'orden' => 6],
                            ['clave' => 'programacion.informe_horario_docente', 'nombre' => 'Horario de Docente', 'ruta' => 'informes.horario_docente', 'icono' => 'UserCheck', 'orden' => 7],
                            ['clave' => 'programacion.informe_horario_seccion', 'nombre' => 'Horario de Sección', 'ruta' => 'informes.horario_seccion', 'icono' => 'Layers', 'orden' => 8],
                        ],
                    ],
                ],
            ],
        ];
    }
}
