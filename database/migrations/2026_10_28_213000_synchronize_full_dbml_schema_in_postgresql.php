<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración Integral de Sincronización con el Esquema Oficial DBML en PostgreSQL.
 *
 * Propósito: Garantizar que todas las tablas y columnas de los Módulos 1, 2, 4, 7 y 9
 * existan físicamente en PostgreSQL con sus nombres, tipos de datos y restricciones exactas.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // MÓDULO 1: ESTRUCTURA GEOGRÁFICA
        // ==========================================
        foreach (['pais', 'estado', 'municipio', 'parroquia', 'localidad'] as $geoTable) {
            if (Schema::hasTable($geoTable)) {
                Schema::table($geoTable, function (Blueprint $table) use ($geoTable) {
                    if (!Schema::hasColumn($geoTable, 'status_id')) {
                        $table->integer('status_id')->default(1);
                    }
                    if (!Schema::hasColumn($geoTable, 'deleted_at')) {
                        $table->timestampTz('deleted_at')->nullable();
                    }
                });
            }
        }

        // ==========================================
        // MÓDULO 2: ORGANIZACIÓN INSTITUCIONAL Y SEDES
        // ==========================================
        if (!Schema::hasTable('dependencia_institucion')) {
            Schema::create('dependencia_institucion', function (Blueprint $table) {
                $table->increments('id_dpi');
                $table->string('nombre_dpi', 100);
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        if (Schema::hasTable('tipo_condicion')) {
            Schema::table('tipo_condicion', function (Blueprint $table) {
                if (!Schema::hasColumn('tipo_condicion', 'status_id')) {
                    $table->integer('status_id')->default(1);
                }
            });
        }

        if (Schema::hasTable('condicion')) {
            Schema::table('condicion', function (Blueprint $table) {
                if (!Schema::hasColumn('condicion', 'status_id')) {
                    $table->integer('status_id')->default(1);
                }
            });
        }

        if (Schema::hasTable('universidad')) {
            Schema::table('universidad', function (Blueprint $table) {
                if (!Schema::hasColumn('universidad', 'status_id')) {
                    $table->integer('status_id')->default(1);
                }
                if (!Schema::hasColumn('universidad', 'deleted_at')) {
                    $table->timestampTz('deleted_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('sede_pnf')) {
            Schema::table('sede_pnf', function (Blueprint $table) {
                if (!Schema::hasColumn('sede_pnf', 'codigo_ministerio_sep')) {
                    $table->string('codigo_ministerio_sep', 20)->nullable();
                }
                if (!Schema::hasColumn('sede_pnf', 'cupos_asignados_sep')) {
                    $table->integer('cupos_asignados_sep')->nullable();
                }
                if (!Schema::hasColumn('sede_pnf', 'prg_id')) {
                    $table->integer('prg_id')->nullable();
                }
            });
        }

        // ==========================================
        // MÓDULO 4: ESCALAS DE MEDICIÓN Y CURRÍCULO
        // ==========================================
        if (!Schema::hasTable('escala_nota')) {
            Schema::create('escala_nota', function (Blueprint $table) {
                $table->increments('id_esn');
                $table->decimal('nota_original_esn', 8, 2);
                $table->decimal('nota_100_esn', 8, 2)->nullable();
                $table->decimal('nota_20_esn', 8, 2)->nullable();
                $table->integer('esm_id');
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        if (!Schema::hasTable('modalidad_estudio')) {
            Schema::create('modalidad_estudio', function (Blueprint $table) {
                $table->increments('id_moe');
                $table->string('codigo_moe', 20)->unique();
                $table->string('nombre_moe', 100);
                $table->text('descripcion_moe')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        if (!Schema::hasTable('regimen_estudio')) {
            Schema::create('regimen_estudio', function (Blueprint $table) {
                $table->increments('id_ree');
                $table->string('codigo_ree', 20)->unique();
                $table->string('nombre_ree', 100);
                $table->text('descripcion_ree')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        if (!Schema::hasTable('tipo_programa')) {
            Schema::create('tipo_programa', function (Blueprint $table) {
                $table->increments('id_tpg');
                $table->string('codigo_tpg', 20)->unique();
                $table->string('nombre_tpg', 100);
                $table->text('descripcion_tpg')->nullable();
                $table->boolean('conducente_grado_tpg')->default(true);
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        if (!Schema::hasTable('programa')) {
            Schema::create('programa', function (Blueprint $table) {
                $table->increments('id_prg');
                $table->string('codigo_prg', 30)->unique();
                $table->string('codigo_ministerio_prg', 30)->nullable();
                $table->string('nombre_prg', 255);
                $table->string('siglas_prg', 20)->nullable();
                $table->string('tipo_prg', 30)->default('PNF');
                $table->string('gaceta_prg', 100)->nullable();
                $table->date('fecha_gaceta_prg')->nullable();
                $table->string('resolucion_prg', 100)->nullable();
                $table->date('fecha_resolucion_prg')->nullable();
                $table->boolean('tiene_salida_intermedia_prg')->default(true);
                $table->decimal('arancel_prg', 10, 2)->default(0.00);
                $table->string('nombre_carnet_prg', 50)->nullable();
                $table->string('color_fondo_carnet_prg', 7)->nullable();
                $table->string('color_texto_carnet_prg', 7)->nullable();
                $table->text('comentario_prg')->nullable();
                $table->integer('tpg_id')->nullable();
                $table->integer('uni_id')->nullable();
                $table->integer('ree_id')->nullable();
                $table->integer('moe_id')->nullable();
                $table->integer('esm_id')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        if (Schema::hasTable('unidad_curricular')) {
            Schema::table('unidad_curricular', function (Blueprint $table) {
                if (!Schema::hasColumn('unidad_curricular', 'codigo_anterior_unc')) {
                    $table->string('codigo_anterior_unc', 30)->nullable();
                }
                if (!Schema::hasColumn('unidad_curricular', 'dep_id')) {
                    $table->integer('dep_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('malla_curricular')) {
            Schema::table('malla_curricular', function (Blueprint $table) {
                if (!Schema::hasColumn('malla_curricular', 'creditos_minimos_tsu_mac')) {
                    $table->integer('creditos_minimos_tsu_mac')->nullable();
                }
                if (!Schema::hasColumn('malla_curricular', 'creditos_minimos_ing_mac')) {
                    $table->integer('creditos_minimos_ing_mac')->nullable();
                }
                if (!Schema::hasColumn('malla_curricular', 'creditos_minimos_prosecucion_mac')) {
                    $table->integer('creditos_minimos_prosecucion_mac')->nullable();
                }
                if (!Schema::hasColumn('malla_curricular', 'horas_minimas_tsu_mac')) {
                    $table->integer('horas_minimas_tsu_mac')->nullable();
                }
                if (!Schema::hasColumn('malla_curricular', 'horas_minimas_ing_mac')) {
                    $table->integer('horas_minimas_ing_mac')->nullable();
                }
                if (!Schema::hasColumn('malla_curricular', 'prg_id')) {
                    $table->integer('prg_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('malla_curricular_detalle')) {
            Schema::table('malla_curricular_detalle', function (Blueprint $table) {
                if (!Schema::hasColumn('malla_curricular_detalle', 'nota_minima_aprobatoria_mcd')) {
                    $table->decimal('nota_minima_aprobatoria_mcd', 4, 2)->nullable();
                }
            });
        }

        // ==========================================
        // MÓDULO 7: GESTIÓN DEL TIEMPO
        // ==========================================
        if (Schema::hasTable('ano_academico')) {
            Schema::table('ano_academico', function (Blueprint $table) {
                if (!Schema::hasColumn('ano_academico', 'nombre_ana')) {
                    $table->string('nombre_ana', 255)->nullable();
                }
                if (!Schema::hasColumn('ano_academico', 'fecha_inicio_ana')) {
                    $table->date('fecha_inicio_ana')->nullable();
                }
                if (!Schema::hasColumn('ano_academico', 'fecha_fin_ana')) {
                    $table->date('fecha_fin_ana')->nullable();
                }
                if (!Schema::hasColumn('ano_academico', 'status_id')) {
                    $table->integer('status_id')->default(1);
                }
                if (!Schema::hasColumn('ano_academico', 'created_at')) {
                    $table->timestampTz('created_at')->useCurrent();
                }
                if (!Schema::hasColumn('ano_academico', 'updated_at')) {
                    $table->timestampTz('updated_at')->nullable();
                }
                if (!Schema::hasColumn('ano_academico', 'created_id')) {
                    $table->integer('created_id')->nullable();
                }
                if (!Schema::hasColumn('ano_academico', 'updated_id')) {
                    $table->integer('updated_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('lapso_academico')) {
            Schema::table('lapso_academico', function (Blueprint $table) {
                if (!Schema::hasColumn('lapso_academico', 'codigo_laa')) {
                    $table->string('codigo_laa', 30)->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'nombre_laa')) {
                    $table->string('nombre_laa', 255)->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'fecha_inicio_laa')) {
                    $table->date('fecha_inicio_laa')->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'fecha_fin_laa')) {
                    $table->date('fecha_fin_laa')->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'fase_operativa_laa')) {
                    $table->string('fase_operativa_laa', 50)->default('Planificado');
                }
                if (!Schema::hasColumn('lapso_academico', 'ana_id')) {
                    $table->integer('ana_id')->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'tpd_id')) {
                    $table->integer('tpd_id')->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'created_at')) {
                    $table->timestampTz('created_at')->useCurrent();
                }
                if (!Schema::hasColumn('lapso_academico', 'updated_at')) {
                    $table->timestampTz('updated_at')->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'created_id')) {
                    $table->integer('created_id')->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'updated_id')) {
                    $table->integer('updated_id')->nullable();
                }
            });
        }

        if (!Schema::hasTable('evento_calendario')) {
            Schema::create('evento_calendario', function (Blueprint $table) {
                $table->increments('id_evc');
                $table->string('tipo_evento_evc', 255)->nullable();
                $table->date('fecha_inicio_evc')->nullable();
                $table->date('fecha_fin_evc')->nullable();
                $table->integer('laa_id');
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        // ==========================================
        // MÓDULO 9: OFERTA, PLANIFICACIÓN E INSCRIPCIÓN
        // ==========================================
        if (!Schema::hasTable('tipo_seccion')) {
            Schema::create('tipo_seccion', function (Blueprint $table) {
                $table->increments('id_tis');
                $table->string('codigo_tis', 20)->unique();
                $table->string('nombre_tis', 100);
                $table->text('descripcion_tis')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        if (!Schema::hasTable('turno')) {
            Schema::create('turno', function (Blueprint $table) {
                $table->increments('id_tur');
                $table->string('codigo_tur', 20)->unique();
                $table->string('nombre_tur', 100);
                $table->time('hora_inicio_tur')->nullable();
                $table->time('hora_fin_tur')->nullable();
                $table->text('descripcion_tur')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        if (Schema::hasTable('seccion')) {
            Schema::table('seccion', function (Blueprint $table) {
                if (!Schema::hasColumn('seccion', 'prg_id')) {
                    $table->integer('prg_id')->nullable();
                }
                if (!Schema::hasColumn('seccion', 'tis_id')) {
                    $table->integer('tis_id')->nullable();
                }
                if (!Schema::hasColumn('seccion', 'tur_id')) {
                    $table->integer('tur_id')->nullable();
                }
            });
        }

        if (!Schema::hasTable('carga_academica')) {
            Schema::create('carga_academica', function (Blueprint $table) {
                $table->increments('id_caa');
                $table->integer('sec_id');
                $table->integer('unc_id');
                $table->integer('docente_per_id');
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();

                $table->unique(['sec_id', 'unc_id', 'docente_per_id'], 'carga_academica_sec_unc_doc_unique');
            });
        }

        if (!Schema::hasTable('horario')) {
            Schema::create('horario', function (Blueprint $table) {
                $table->increments('id_hor');
                $table->string('dia_semana_hor', 255)->nullable();
                $table->time('hora_inicio_hor')->nullable();
                $table->time('hora_fin_hor')->nullable();
                $table->integer('caa_id');
                $table->integer('aul_id');
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();
            });
        }

        // ==========================================
        // SEEDING INICIAL DE CATÁLOGOS BASE
        // ==========================================
        $now = now();

        if (DB::table('tipo_seccion')->count() === 0) {
            DB::table('tipo_seccion')->insert([
                ['codigo_tis' => 'REG', 'nombre_tis' => 'Regular', 'descripcion_tis' => 'Sección estándar para estudiantes regulares en secuencia.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tis' => 'T-INI', 'nombre_tis' => 'Trayecto Inicial', 'descripcion_tis' => 'Sección de iniciación universitaria para nuevo ingreso.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tis' => 'PER', 'nombre_tis' => 'Plan Especial de Recuperación (PER)', 'descripcion_tis' => 'Acompañamiento intensivo para nivelación.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tis' => 'REP', 'nombre_tis' => 'Repitencia / Arrastre', 'descripcion_tis' => 'Recursado de unidades curriculares reprobadas.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tis' => 'INT', 'nombre_tis' => 'Intensivo', 'descripcion_tis' => 'Periodo intersemestral intensivo de verano.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tis' => 'PROS', 'nombre_tis' => 'Prosecución', 'descripcion_tis' => 'Sección especial para egresados TSU en prosecución a Ingeniería/Licenciatura.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
            ]);
        }

        if (DB::table('turno')->count() === 0) {
            DB::table('turno')->insert([
                ['codigo_tur' => 'DIU', 'nombre_tur' => 'Diurno / Mañana', 'hora_inicio_tur' => '07:00:00', 'hora_fin_tur' => '12:30:00', 'descripcion_tur' => 'Jornada matutina estándar.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tur' => 'VES', 'nombre_tur' => 'Vespertino / Tarde', 'hora_inicio_tur' => '13:00:00', 'hora_fin_tur' => '18:30:00', 'descripcion_tur' => 'Jornada vespertina.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tur' => 'NOC', 'nombre_tur' => 'Nocturno', 'hora_inicio_tur' => '18:30:00', 'hora_fin_tur' => '22:00:00', 'descripcion_tur' => 'Jornada nocturna.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tur' => 'SAB', 'nombre_tur' => 'Sabatino / Fin de Semana', 'hora_inicio_tur' => '07:30:00', 'hora_fin_tur' => '16:30:00', 'descripcion_tur' => 'Jornada de fin de semana.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
            ]);
        }

        if (DB::table('dependencia_institucion')->count() === 0) {
            DB::table('dependencia_institucion')->insert([
                ['nombre_dpi' => 'Territorial MPPEU (Pública)', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['nombre_dpi' => 'Autónoma', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['nombre_dpi' => 'Privada', 'status' => true, 'status_id' => 1, 'created_at' => $now],
            ]);
        }

        if (DB::table('modalidad_estudio')->count() === 0) {
            DB::table('modalidad_estudio')->insert([
                ['codigo_moe' => 'PRES', 'nombre_moe' => 'Presencial', 'descripcion_moe' => 'Asistencia regular en aula.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_moe' => 'SEMIPRES', 'nombre_moe' => 'Semipresencial', 'descripcion_moe' => 'Modalidad mixta o híbrida.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_moe' => 'DIST', 'nombre_moe' => 'A Distancia / Virtual', 'descripcion_moe' => 'Mediación telemática.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
            ]);
        }

        if (DB::table('regimen_estudio')->count() === 0) {
            DB::table('regimen_estudio')->insert([
                ['codigo_ree' => 'REG-ANUAL', 'nombre_ree' => 'Regular Anual (Por Trayectos)', 'descripcion_ree' => 'Régimen anual dividido en tramos o semestres.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_ree' => 'REG-SEM', 'nombre_ree' => 'Semestralizado', 'descripcion_ree' => 'Régimen por semestres independientes.', 'status' => true, 'status_id' => 1, 'created_at' => $now],
            ]);
        }

        if (DB::table('tipo_programa')->count() === 0) {
            DB::table('tipo_programa')->insert([
                ['codigo_tpg' => 'PNF', 'nombre_tpg' => 'Programa Nacional de Formación', 'conducente_grado_tpg' => true, 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tpg' => 'PNFA', 'nombre_tpg' => 'Programa Nacional de Formación Avanzada', 'conducente_grado_tpg' => true, 'status' => true, 'status_id' => 1, 'created_at' => $now],
                ['codigo_tpg' => 'TRAD', 'nombre_tpg' => 'Carrera Tradicional', 'conducente_grado_tpg' => true, 'status' => true, 'status_id' => 1, 'created_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('horario');
        Schema::dropIfExists('carga_academica');
        Schema::dropIfExists('turno');
        Schema::dropIfExists('tipo_seccion');
        Schema::dropIfExists('evento_calendario');
        Schema::dropIfExists('programa');
        Schema::dropIfExists('tipo_programa');
        Schema::dropIfExists('regimen_estudio');
        Schema::dropIfExists('modalidad_estudio');
        Schema::dropIfExists('escala_nota');
        Schema::dropIfExists('dependencia_institucion');
    }
};

