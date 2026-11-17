<?php

namespace Database\Seeders\EstructuraAcademica;

use App\Models\Academico\TipoLapso;
use App\Models\EstructuraAcademica\Maestros\EjeCurricular;
use App\Models\EstructuraAcademica\Maestros\EscalaMedicion;
use App\Models\EstructuraAcademica\Maestros\ModalidadEstudio;
use App\Models\EstructuraAcademica\Maestros\RegimenEstudio;
use App\Models\EstructuraAcademica\Maestros\TipoPrograma;
use App\Models\EstructuraAcademica\Maestros\TipoUnidadCurricular;
use App\Models\ProgramacionAcademica\Maestros\TipoAula;
use App\Models\ProgramacionAcademica\Maestros\TipoSeccion;
use App\Models\ProgramacionAcademica\Maestros\Turno;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: Catálogos Base Académicos y de Programación.
 *
 * Propósito: Poblar de forma idempotente los catálogos maestros requeridos por mallas,
 * programas, lapsos y programación académica (`tipo_lapso`, `tipo_programa`, `modalidad_estudio`,
 * `regimen_estudio`, `tipo_seccion`, `escala_medicion`, `eje_curricular`, `tipo_unidad_curricular`,
 * `tipo_aula`, `turno`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql` sin uso de columnas legacy duplicadas de `turno`.
 */
class CatalogosAcademicosSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $tiposLapso = [
                [
                    'codigo_tla'           => 'TRA',
                    'nombre_tla'           => 'Trayecto / Anual',
                    'nomenclatura_tla'     => 'Trayecto',
                    'descripcion_tla'      => 'Régimen anual estructurado por trayectos (PNF).',
                    'duracion_semanas_tla' => 36,
                ],
                [
                    'codigo_tla'           => 'SEM',
                    'nombre_tla'           => 'Semestral',
                    'nomenclatura_tla'     => 'Semestre',
                    'descripcion_tla'      => 'Lapso académico semestral regular.',
                    'duracion_semanas_tla' => 16,
                ],
                [
                    'codigo_tla'           => 'TRI',
                    'nombre_tla'           => 'Trimestral',
                    'nomenclatura_tla'     => 'Trimestre',
                    'descripcion_tla'      => 'Lapso académico trimestral.',
                    'duracion_semanas_tla' => 12,
                ],
                [
                    'codigo_tla'           => 'INT',
                    'nombre_tla'           => 'Intensivo',
                    'nomenclatura_tla'     => 'Intensivo',
                    'descripcion_tla'      => 'Período académico intensivo o vacacional.',
                    'duracion_semanas_tla' => 6,
                ],
            ];

            foreach ($tiposLapso as $tla) {
                TipoLapso::withTrashed()->updateOrCreate(
                    ['codigo_tla' => $tla['codigo_tla']],
                    array_merge($tla, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            foreach ([
                ['codigo_tpg' => 'PNF', 'nombre_tpg' => 'Programa Nacional de Formación', 'descripcion_tpg' => 'Programa nacional conducente a título de TSU o Ingeniería/Licenciatura.', 'conducente_grado_tpg' => true],
                ['codigo_tpg' => 'CARRERA', 'nombre_tpg' => 'Carrera Universitaria', 'descripcion_tpg' => 'Carrera universitaria tradicional conducente a grado.', 'conducente_grado_tpg' => true],
                ['codigo_tpg' => 'PNFA', 'nombre_tpg' => 'PNF Avanzado (Postgrado)', 'descripcion_tpg' => 'Programa de formación avanzada conducente a especialización/maestría.', 'conducente_grado_tpg' => true],
                ['codigo_tpg' => 'DIPLOMADO', 'nombre_tpg' => 'Diplomado / Extensión', 'descripcion_tpg' => 'Programa de formación continua no conducente a grado.', 'conducente_grado_tpg' => false],
            ] as $tpg) {
                TipoPrograma::withTrashed()->updateOrCreate(
                    ['codigo_tpg' => $tpg['codigo_tpg']],
                    array_merge($tpg, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            foreach ([
                ['codigo_moe' => 'PRES', 'nombre_moe' => 'Presencial', 'descripcion_moe' => 'Actividades formativas ejecutadas presencialmente en campus/aulas.'],
                ['codigo_moe' => 'SEMI', 'nombre_moe' => 'Semipresencial', 'descripcion_moe' => 'Modalidad mixta presencial y en entornos virtuales.'],
                ['codigo_moe' => 'DIST', 'nombre_moe' => 'A Distancia', 'descripcion_moe' => 'Modalidad totalmente mediada por entornos virtuales.'],
            ] as $moe) {
                ModalidadEstudio::withTrashed()->updateOrCreate(
                    ['codigo_moe' => $moe['codigo_moe']],
                    array_merge($moe, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            foreach ([
                ['codigo_ree' => 'ANUAL', 'nombre_ree' => 'Anual / Trayecto', 'descripcion_ree' => 'Régimen académico de duración anual por trayectos.'],
                ['codigo_ree' => 'SEM', 'nombre_ree' => 'Semestral', 'descripcion_ree' => 'Régimen dividido en dos períodos semestrales por año.'],
                ['codigo_ree' => 'TRIM', 'nombre_ree' => 'Trimestral', 'descripcion_ree' => 'Régimen dividido en períodos trimestrales.'],
            ] as $ree) {
                RegimenEstudio::withTrashed()->updateOrCreate(
                    ['codigo_ree' => $ree['codigo_ree']],
                    array_merge($ree, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            foreach ([
                ['codigo_tis' => 'REG', 'nombre_tis' => 'Regular', 'descripcion_tis' => 'Sección ordinaria de período académico regular.'],
                ['codigo_tis' => 'INT', 'nombre_tis' => 'Intensivo', 'descripcion_tis' => 'Sección para cursos intensivos o de nivelación.'],
                ['codigo_tis' => 'PER', 'nombre_tis' => 'Período Especial', 'descripcion_tis' => 'Sección especial / tutorial autorizada por Consejo Académico.'],
            ] as $tis) {
                TipoSeccion::withTrashed()->updateOrCreate(
                    ['codigo_tis' => $tis['codigo_tis']],
                    array_merge($tis, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            foreach ([
                ['nombre_esm' => 'Escala Oficial PNF (1-20)', 'nota_aprobatoria_esm' => 12, 'nota_minima_esm' => 1, 'nota_maxima_esm' => 20],
                ['nombre_esm' => 'Escala Proyecto Sociointegrador (1-20)', 'nota_aprobatoria_esm' => 16, 'nota_minima_esm' => 1, 'nota_maxima_esm' => 20],
                ['nombre_esm' => 'Escala Porcentual (0-100)', 'nota_aprobatoria_esm' => 60, 'nota_minima_esm' => 0, 'nota_maxima_esm' => 100],
            ] as $esm) {
                EscalaMedicion::withTrashed()->updateOrCreate(
                    ['nombre_esm' => $esm['nombre_esm']],
                    array_merge($esm, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            foreach ([
                ['codigo_ecu' => 'EPIST', 'nombre_ecu' => 'Epistemológico', 'descripcion_ecu' => 'Eje de fundamentación científica y humanística.'],
                ['codigo_ecu' => 'ETICO', 'nombre_ecu' => 'Ético - Político', 'descripcion_ecu' => 'Eje de formación ciudadana y responsabilidad social.'],
                ['codigo_ecu' => 'TRAB', 'nombre_ecu' => 'Trabajo Productivo', 'descripcion_ecu' => 'Eje vinculado al proyecto sociointegrador y praxis profesional.'],
                ['codigo_ecu' => 'ESTET', 'nombre_ecu' => 'Estético - Lúdico', 'descripcion_ecu' => 'Eje cultural, deportivo y recreativo.'],
                ['codigo_ecu' => 'AMBI', 'nombre_ecu' => 'Socioambiental', 'descripcion_ecu' => 'Eje de sustentabilidad y entorno comunitario.'],
            ] as $ecu) {
                EjeCurricular::withTrashed()->updateOrCreate(
                    ['codigo_ecu' => $ecu['codigo_ecu']],
                    array_merge($ecu, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            foreach (['Obligatoria', 'Electiva', 'Acreditable', 'Proyecto Sociointegrador', 'Práctica Profesional'] as $nombreTuc) {
                TipoUnidadCurricular::withTrashed()->updateOrCreate(
                    ['nombre_tuc' => $nombreTuc],
                    ['status_id' => 1, 'deleted_at' => null]
                );
            }

            foreach ([
                ['codigo_tia' => 'AULA', 'nombre_tia' => 'Aula Teórica', 'descripcion_tia' => 'Salón estándar para clases magistrales.'],
                ['codigo_tia' => 'LAB_INF', 'nombre_tia' => 'Laboratorio de Informática', 'descripcion_tia' => 'Sala equipada con estaciones de cómputo.'],
                ['codigo_tia' => 'TALLER', 'nombre_tia' => 'Taller / Laboratorio Técnico', 'descripcion_tia' => 'Espacio de prácticas técnicas y experimentales.'],
                ['codigo_tia' => 'AUDIT', 'nombre_tia' => 'Auditorio / Salón Múltiple', 'descripcion_tia' => 'Espacio de gran aforo para conferencias y actos académicos.'],
            ] as $tia) {
                TipoAula::withTrashed()->updateOrCreate(
                    ['codigo_tia' => $tia['codigo_tia']],
                    array_merge($tia, ['status_id' => 1, 'deleted_at' => null])
                );
            }

            foreach ([
                ['codigo_tur' => 'MAÑ', 'nombre_tur' => 'Mañana', 'hora_inicio_tur' => '07:00', 'hora_fin_tur' => '12:15', 'descripcion_tur' => 'Jornada matutina ordinaria.'],
                ['codigo_tur' => 'TAR', 'nombre_tur' => 'Tarde', 'hora_inicio_tur' => '13:00', 'hora_fin_tur' => '17:45', 'descripcion_tur' => 'Jornada vespertina ordinaria.'],
                ['codigo_tur' => 'NOC', 'nombre_tur' => 'Nocturno', 'hora_inicio_tur' => '18:00', 'hora_fin_tur' => '21:30', 'descripcion_tur' => 'Jornada nocturna ordinaria.'],
                ['codigo_tur' => 'FIN', 'nombre_tur' => 'Fines de Semana', 'hora_inicio_tur' => '07:30', 'hora_fin_tur' => '17:00', 'descripcion_tur' => 'Jornada sabatina/dominical para modalidades especiales.'],
            ] as $tur) {
                Turno::withTrashed()->updateOrCreate(
                    ['codigo_tur' => $tur['codigo_tur']],
                    array_merge($tur, ['status_id' => 1, 'deleted_at' => null])
                );
            }
        });
    }
}
