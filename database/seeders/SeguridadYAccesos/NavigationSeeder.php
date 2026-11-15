<?php

namespace Database\Seeders\SeguridadYAccesos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Navegación y Menús Laterales (Sidebar).
 * Propósito: Gestionar de forma centralizada e idempotente la estructura de navegación en 3 niveles
 * (Módulo -> Submódulo -> Vista) con iconos Lucide y rutas del sistema.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\NavigationSeeder"
 */
class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        // Si las tablas de navegación dinámica no existen aún en la base de datos, advertir y salir limpiamente
        if (!Schema::hasTable('modulo') || !Schema::hasTable('submodulo') || !Schema::hasTable('vista')) {
            $this->command->warn('Tablas de navegación dinámica ("modulo", "submodulo", "vista") no existen en la BD actual. Omitiendo inserción.');
            return;
        }

        $now = now();

        /**
         * Estructura jerárquica de 3 niveles exigida por el estándar de desarrollo.
         */
        $estructura = [
            [
                'nombre'     => 'Programación Académica',
                'icono'      => 'Calendar',
                'orden'      => 1,
                'submodulos' => [
                    [
                        'nombre' => 'Lapsos y Cohortes',
                        'icono'  => 'Clock',
                        'orden'  => 1,
                        'vistas' => [
                            [
                                'nombre' => 'Cohortes',
                                'ruta'   => 'cohorte.index',
                                'icono'  => 'Users',
                                'orden'  => 1,
                            ],
                            [
                                'nombre' => 'Sede PNF',
                                'ruta'   => 'sede_pnf.index',
                                'icono'  => 'Building2',
                                'orden'  => 2,
                            ],
                            [
                                'nombre' => 'Cohortes por Sede',
                                'ruta'   => 'cohorte_sede.index',
                                'icono'  => 'School',
                                'orden'  => 3,
                            ],
                            [
                                'nombre' => 'Cursos Intensivos',
                                'ruta'   => 'informes.chequeo_intensivo',
                                'icono'  => 'Flame',
                                'orden'  => 4,
                            ],
                        ],
                    ],
                    [
                        'nombre' => 'Secciones',
                        'icono'  => 'Users',
                        'orden'  => 2,
                        'vistas' => [
                            ['nombre' => 'Secciones', 'ruta' => 'seccion.index', 'icono' => 'Layers', 'orden' => 1],
                            ['nombre' => 'Sección (SUD)', 'ruta' => 'seccion_unidad_docente.index', 'icono' => 'UserCheck', 'orden' => 2],
                            ['nombre' => 'Tipo De Sección (sin Tabla)', 'ruta' => 'tipo_seccion.index', 'icono' => 'Bookmark', 'orden' => 3],
                            ['nombre' => 'Abrir Sección', 'ruta' => 'seccion.abrir', 'icono' => 'DoorOpen', 'orden' => 4],
                            ['nombre' => 'Cerrar Sección', 'ruta' => 'seccion.cerrar', 'icono' => 'DoorClosed', 'orden' => 5],
                            ['nombre' => 'Fusionar Secciones (pronto)', 'ruta' => 'seccion.fusionar', 'icono' => 'GitMerge', 'orden' => 6],
                            ['nombre' => 'Cambiar Capacidad Por Sección', 'ruta' => 'seccion.capacidad', 'icono' => 'Users', 'orden' => 7],
                        ],
                    ],
                    [
                        'nombre' => 'Espacios y tiempo',
                        'icono'  => 'Clock',
                        'orden'  => 3,
                        'vistas' => [
                            ['nombre' => 'Aulas', 'ruta' => 'aula.index', 'icono' => 'School', 'orden' => 1],
                            ['nombre' => 'Tipos de Aula', 'ruta' => 'tipo_aula.index', 'icono' => 'Bookmark', 'orden' => 2],
                            ['nombre' => 'Turno', 'ruta' => 'turno.index', 'icono' => 'Sun', 'orden' => 3],
                            ['nombre' => 'Horarios', 'ruta' => 'horario.index', 'icono' => 'CalendarClock', 'orden' => 4],
                            ['nombre' => 'Programación de Horarios', 'ruta' => 'horarios.index', 'icono' => 'Calendar', 'orden' => 5],
                        ],
                    ],
                    [
                        'nombre' => 'Informes',
                        'icono'  => 'FileText',
                        'orden'  => 4,
                        'vistas' => [
                            ['nombre' => 'Horario Estudiante', 'ruta' => 'seccion.horario_estudiante', 'icono' => 'GraduationCap', 'orden' => 1],
                            ['nombre' => 'Listado De Chequeo Intensivo', 'ruta' => 'informes.chequeo_intensivo', 'icono' => 'FileCheck', 'orden' => 2],
                            ['nombre' => 'Listado De Clases', 'ruta' => 'informes.clases', 'icono' => 'Calendar', 'orden' => 3],
                            ['nombre' => 'Listado De Secciones', 'ruta' => 'informes.secciones', 'icono' => 'Layers', 'orden' => 4],
                            ['nombre' => 'Secciones Abiertas', 'ruta' => 'informes.secciones_abiertas', 'icono' => 'DoorOpen', 'orden' => 5],
                            ['nombre' => 'Horario De Aula', 'ruta' => 'informes.horario_aula', 'icono' => 'School', 'orden' => 6],
                            ['nombre' => 'Horario De Docente', 'ruta' => 'informes.horario_docente', 'icono' => 'UserCheck', 'orden' => 7],
                            ['nombre' => 'Horario De Sección', 'ruta' => 'informes.horario_seccion', 'icono' => 'Layers', 'orden' => 8],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($estructura as $modData) {
            $modulo = DB::table('modulo')->updateOrInsert(
                ['nombre' => $modData['nombre']],
                [
                    'icono'      => $modData['icono'],
                    'orden'      => $modData['orden'],
                    'activo'     => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $moduloId = DB::table('modulo')->where('nombre', $modData['nombre'])->value('id_modulo');

            foreach ($modData['submodulos'] as $subData) {
                DB::table('submodulo')->updateOrInsert(
                    ['nombre' => $subData['nombre'], 'modulo_id' => $moduloId],
                    [
                        'icono'      => $subData['icono'],
                        'orden'      => $subData['orden'],
                        'activo'     => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $submoduloId = DB::table('submodulo')
                    ->where('nombre', $subData['nombre'])
                    ->where('modulo_id', $moduloId)
                    ->value('id_submodulo');

                foreach ($subData['vistas'] as $visData) {
                    DB::table('vista')->updateOrInsert(
                        ['nombre' => $visData['nombre'], 'submodulo_id' => $submoduloId],
                        [
                            'ruta'       => $visData['ruta'],
                            'icono'      => $visData['icono'],
                            'orden'      => $visData['orden'],
                            'activo'     => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        }

        $this->command->info('Estructura de navegación sincronizada correctamente.');
    }
}

