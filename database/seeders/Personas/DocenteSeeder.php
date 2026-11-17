<?php

namespace Database\Seeders\Personas;

use App\Models\Personas\CategoriaDocente;
use App\Models\Personas\CondicionLaboral;
use App\Models\Personas\DedicacionDocente;
use App\Models\Personas\Perfil;
use App\Models\Personas\Persona;
use App\Models\Personas\PersonaPerfil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: Catálogos Laborales Docentes y 15 Docentes de Prueba (`dedicacion_docente`,
 * `categoria_docente`, `condicion_laboral`, `persona`, `persona_perfil`).
 *
 * Propósito: Poblar de forma idempotente las 4 dedicaciones laborales universitarias
 * (Dedicación Exclusiva, Tiempo Completo, Medio Tiempo y Tiempo Convencional) junto con
 * 15 docentes venezolanos realistas habilitados con perfil `DOCENTE` para su asignación
 * inmediata en Carga Académica (`carga_academica`) y Horarios.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial alineada a `sogac_mio.sql` (`persona.doc_*`, `persona_perfil.id_pep`).
 */
class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $perfilDocente = Perfil::withTrashed()->updateOrCreate(
                ['codigo_prf' => 'DOCENTE'],
                [
                    'nombre_prf'      => 'Personal Académico (Docente)',
                    'descripcion_prf' => 'Perfil de docente o investigador asignable en carga_academica y calificaciones.',
                    'status_id'       => 1,
                    'deleted_at'      => null,
                ]
            );

            $dedicacionesData = [
                'exclusiva' => [
                    'nombre_ded'                => 'Dedicación Exclusiva',
                    'horas_academicas_ded'      => 24,
                    'horas_administrativas_ded' => 16,
                ],
                'completo' => [
                    'nombre_ded'                => 'Tiempo Completo',
                    'horas_academicas_ded'      => 20,
                    'horas_administrativas_ded' => 16,
                ],
                'medio' => [
                    'nombre_ded'                => 'Medio Tiempo',
                    'horas_academicas_ded'      => 12,
                    'horas_administrativas_ded' => 8,
                ],
                'convencional' => [
                    'nombre_ded'                => 'Tiempo Convencional',
                    'horas_academicas_ded'      => 8,
                    'horas_administrativas_ded' => 0,
                ],
            ];

            $dedicaciones = [];
            foreach ($dedicacionesData as $clave => $item) {
                $dedicaciones[$clave] = DedicacionDocente::withTrashed()->updateOrCreate(
                    ['nombre_ded' => $item['nombre_ded']],
                    array_merge($item, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            $categoriasData = [
                'instructor' => ['codigo_cad' => 'INST', 'nombre_cad' => 'Instructor', 'nivel_escalafon_cad' => 1, 'anos_experiencia_cad' => 0],
                'asistente'  => ['codigo_cad' => 'ASIS', 'nombre_cad' => 'Asistente', 'nivel_escalafon_cad' => 2, 'anos_experiencia_cad' => 2],
                'agregado'   => ['codigo_cad' => 'AGRE', 'nombre_cad' => 'Agregado', 'nivel_escalafon_cad' => 3, 'anos_experiencia_cad' => 5],
                'asociado'   => ['codigo_cad' => 'ASOC', 'nombre_cad' => 'Asociado', 'nivel_escalafon_cad' => 4, 'anos_experiencia_cad' => 9],
                'titular'    => ['codigo_cad' => 'TITU', 'nombre_cad' => 'Titular', 'nivel_escalafon_cad' => 5, 'anos_experiencia_cad' => 12],
            ];

            $categorias = [];
            foreach ($categoriasData as $clave => $item) {
                $categorias[$clave] = CategoriaDocente::withTrashed()->updateOrCreate(
                    ['codigo_cad' => $item['codigo_cad']],
                    array_merge($item, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            $condicionesData = [
                'ordinario' => ['nombre_cla' => 'Ordinario', 'descripcion_cla' => 'Personal docente ordinario de planta institucional.'],
                'contratado' => ['nombre_cla' => 'Contratado', 'descripcion_cla' => 'Personal docente contratado por período lectivo.'],
                'suplente' => ['nombre_cla' => 'Suplente', 'descripcion_cla' => 'Personal docente en modalidad de suplencia temporal.'],
            ];

            $condiciones = [];
            foreach ($condicionesData as $clave => $item) {
                $condiciones[$clave] = CondicionLaboral::withTrashed()->updateOrCreate(
                    ['nombre_cla' => $item['nombre_cla']],
                    array_merge($item, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            $docentes = [
                ['doc' => '12458790', 'nom' => 'Carlos Eduardo', 'ape' => 'Mendoza Rodríguez', 'gen' => 'M', 'tel' => '0414-5521034', 'ded' => 'exclusiva', 'cad' => 'titular', 'cla' => 'ordinario', 'esp' => 'Ingeniería Mecánica y Termodinámica', 'ing' => '2012-03-15'],
                ['doc' => '13789412', 'nom' => 'María Elena', 'ape' => 'Torrealba Pérez', 'gen' => 'F', 'tel' => '0424-5189021', 'ded' => 'exclusiva', 'cad' => 'asociado', 'cla' => 'ordinario', 'esp' => 'Matemática Aplicada y Cálculo', 'ing' => '2014-09-01'],
                ['doc' => '14210567', 'nom' => 'José Gregorio', 'ape' => 'Alvarado Colmenárez', 'gen' => 'M', 'tel' => '0416-6542310', 'ded' => 'exclusiva', 'cad' => 'agregado', 'cla' => 'ordinario', 'esp' => 'Ingeniería en Informática y Redes', 'ing' => '2016-02-10'],
                ['doc' => '15340891', 'nom' => 'Yelitza Josefina', 'ape' => 'Gómez Linárez', 'gen' => 'F', 'tel' => '0412-5023488', 'ded' => 'exclusiva', 'cad' => 'asociado', 'cla' => 'ordinario', 'esp' => 'Metodología de Proyecto Sociotecnológico', 'ing' => '2013-05-20'],
                ['doc' => '15987123', 'nom' => 'Rafael Antonio', 'ape' => 'Quintero Escalona', 'gen' => 'M', 'tel' => '0414-7891234', 'ded' => 'completo', 'cad' => 'agregado', 'cla' => 'ordinario', 'esp' => 'Resistencia de Materiales y Diseño', 'ing' => '2017-01-16'],
                ['doc' => '16450932', 'nom' => 'Ana Gabriela', 'ape' => 'Briceño Castillo', 'gen' => 'F', 'tel' => '0424-5678901', 'ded' => 'completo', 'cad' => 'asistente', 'cla' => 'ordinario', 'esp' => 'Física Aplicada y Electromagnetismo', 'ing' => '2018-04-02'],
                ['doc' => '16892341', 'nom' => 'Luis Alberto', 'ape' => 'Vargas Montilla', 'gen' => 'M', 'tel' => '0416-8904512', 'ded' => 'completo', 'cad' => 'asistente', 'cla' => 'contratado', 'esp' => 'Algoritmos y Bases de Datos', 'ing' => '2019-10-07'],
                ['doc' => '17320455', 'nom' => 'Carmen Lucía', 'ape' => 'Chirinos Sequera', 'gen' => 'F', 'tel' => '0414-3217740', 'ded' => 'completo', 'cad' => 'agregado', 'cla' => 'ordinario', 'esp' => 'Química General y Materiales', 'ing' => '2015-11-12'],
                ['doc' => '17984510', 'nom' => 'Héctor Ramón', 'ape' => 'Peraza Mujica', 'gen' => 'M', 'tel' => '0412-6731905', 'ded' => 'medio', 'cad' => 'asistente', 'cla' => 'contratado', 'esp' => 'Procesos de Manufactura y CNC', 'ing' => '2020-02-03'],
                ['doc' => '18421098', 'nom' => 'Dayana Coromoto', 'ape' => 'Graterol Peña', 'gen' => 'F', 'tel' => '0424-9012387', 'ded' => 'medio', 'cad' => 'instructor', 'cla' => 'contratado', 'esp' => 'Formación Socio-Crítica y Ética', 'ing' => '2021-06-14'],
                ['doc' => '18903412', 'nom' => 'Wilmer Alexander', 'ape' => 'Jiménez Lugo', 'gen' => 'M', 'tel' => '0416-4328890', 'ded' => 'medio', 'cad' => 'asistente', 'cla' => 'contratado', 'esp' => 'Electrónica Industrial y Automatización', 'ing' => '2020-09-21'],
                ['doc' => '19345120', 'nom' => 'Norys del Valle', 'ape' => 'Salazar Lucena', 'gen' => 'F', 'tel' => '0414-8741299', 'ded' => 'medio', 'cad' => 'instructor', 'cla' => 'suplente', 'esp' => 'Inglés Técnico e Idiomas', 'ing' => '2022-03-07'],
                ['doc' => '19876543', 'nom' => 'Jesús Manuel', 'ape' => 'Rojas Barreto', 'gen' => 'M', 'tel' => '0426-5112098', 'ded' => 'convencional', 'cad' => 'instructor', 'cla' => 'contratado', 'esp' => 'Dibujo Técnico y CAD/CAM', 'ing' => '2023-01-18'],
                ['doc' => '20412890', 'nom' => 'Karelys Beatriz', 'ape' => 'Paredes Navarro', 'gen' => 'F', 'tel' => '0424-7340912', 'ded' => 'convencional', 'cad' => 'instructor', 'cla' => 'contratado', 'esp' => 'Estadística e Investigación de Operaciones', 'ing' => '2023-09-04'],
                ['doc' => '21098345', 'nom' => 'Ángel David', 'ape' => 'Silva Morales', 'gen' => 'M', 'tel' => '0412-9981204', 'ded' => 'convencional', 'cad' => 'instructor', 'cla' => 'suplente', 'esp' => 'Seguridad Higiene y Ambiente Industrial', 'ing' => '2024-02-12'],
            ];

            foreach ($docentes as $idx => $d) {
                $cedula = 'V' . $d['doc'];
                $correoSlug = strtolower(
                    str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'], ['.', 'a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'], explode(' ', $d['nom'])[0] . '.' . explode(' ', $d['ape'])[0])
                ) . '.' . ($idx + 1) . '@uptp.edu.ve';

                $persona = Persona::withTrashed()->updateOrCreate(
                    ['cedula_per' => $cedula],
                    [
                        'nacionalidad_per'   => 'V',
                        'documento_per'      => $d['doc'],
                        'nombres_per'        => $d['nom'],
                        'apellidos_per'      => $d['ape'],
                        'genero_per'         => $d['gen'],
                        'correo_per'         => $correoSlug,
                        'telefono_movil_per' => $d['tel'],
                        'doc_cad_id'         => $categorias[$d['cad']]->id_cad,
                        'doc_ded_id'         => $dedicaciones[$d['ded']]->id_ded,
                        'doc_cla_id'         => $condiciones[$d['cla']]->id_cla,
                        'doc_especialidad'   => $d['esp'],
                        'doc_fecha_ingreso'  => $d['ing'],
                        'status_id'          => 1,
                        'deleted_at'         => null,
                    ]
                );

                PersonaPerfil::withTrashed()->updateOrCreate(
                    [
                        'per_id' => $persona->id_per,
                        'prf_id' => $perfilDocente->id_prf,
                    ],
                    [
                        'fecha_inicio_pep' => $d['ing'],
                        'status_id'        => 1,
                        'deleted_at'       => null,
                    ]
                );
            }
        });
    }
}
