<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Sincronización de Tablas y Columnas Faltantes del DDL Oficial SOGAC.
 *
 * Propósito: Garantizar que el esquema de PostgreSQL contenga el 100% de las tablas
 * y columnas especificadas en el script DDL oficial de SOGAC sin romper la compatibilidad
 * con los modelos Eloquent existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla `pais`: asegurar columna `id_pais` sincronizada con `id_pai`
        if (Schema::hasTable('pais') && !Schema::hasColumn('pais', 'id_pais')) {
            DB::statement('ALTER TABLE pais ADD COLUMN id_pais INTEGER GENERATED ALWAYS AS (id_pai) STORED UNIQUE');
        }

        // 2. Tabla `tipo_unidad_curricular`: agregar `codigo_tuc`
        if (Schema::hasTable('tipo_unidad_curricular') && !Schema::hasColumn('tipo_unidad_curricular', 'codigo_tuc')) {
            Schema::table('tipo_unidad_curricular', function (Blueprint $table) {
                $table->string('codigo_tuc', 30)->nullable()->after('nombre_tuc');
            });
        }

        // 3. Tabla `ano_academico`: asegurar columna `id_ana` sincronizada con `id_ano`
        if (Schema::hasTable('ano_academico') && !Schema::hasColumn('ano_academico', 'id_ana')) {
            DB::statement('ALTER TABLE ano_academico ADD COLUMN id_ana INTEGER GENERATED ALWAYS AS (id_ano) STORED UNIQUE');
        }

        // 4. Tabla `lapso_academico`: asegurar columna `id_laa` sincronizada con `id_la`
        if (Schema::hasTable('lapso_academico') && !Schema::hasColumn('lapso_academico', 'id_laa')) {
            DB::statement('ALTER TABLE lapso_academico ADD COLUMN id_laa INTEGER GENERATED ALWAYS AS (id_la) STORED UNIQUE');
        }

        // 5. Tabla `corte_lapso`: crear tabla si no existe
        if (!Schema::hasTable('corte_lapso')) {
            Schema::create('corte_lapso', function (Blueprint $table) {
                $table->increments('id_col');
                $table->integer('numero_corte_col')->nullable();
                $table->decimal('porcentaje_valor_col', 8, 2)->nullable();
                $table->timestampTz('fecha_apertura_notas_col')->nullable();
                $table->timestampTz('fecha_cierre_notas_col')->nullable();
                $table->integer('laa_id');
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->timestampsTz();
                $table->softDeletesTz();
                $table->integer('created_id')->nullable();
                $table->integer('updated_id')->nullable();

                $table->foreign('laa_id')->references('id_la')->on('lapso_academico')->onDelete('cascade');
            });
        }

        // 6. Tabla `programa`: agregar `titulo_prg` y `exm_id`
        if (Schema::hasTable('programa')) {
            Schema::table('programa', function (Blueprint $table) {
                if (!Schema::hasColumn('programa', 'titulo_prg')) {
                    $table->string('titulo_prg', 255)->nullable();
                }
                if (!Schema::hasColumn('programa', 'exm_id')) {
                    $table->integer('exm_id')->nullable();
                }
            });
        }

        // 7. Tabla `malla_curricular_detalle`: agregar `tuc_id`
        if (Schema::hasTable('malla_curricular_detalle') && !Schema::hasColumn('malla_curricular_detalle', 'tuc_id')) {
            Schema::table('malla_curricular_detalle', function (Blueprint $table) {
                $table->integer('tuc_id')->nullable();
                $table->foreign('tuc_id')->references('id_tuc')->on('tipo_unidad_curricular')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('malla_curricular_detalle') && Schema::hasColumn('malla_curricular_detalle', 'tuc_id')) {
            Schema::table('malla_curricular_detalle', function (Blueprint $table) {
                $table->dropForeign(['tuc_id']);
                $table->dropColumn('tuc_id');
            });
        }

        if (Schema::hasTable('programa')) {
            Schema::table('programa', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('programa', 'titulo_prg')) {
                    $cols[] = 'titulo_prg';
                }
                if (Schema::hasColumn('programa', 'exm_id')) {
                    $cols[] = 'exm_id';
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('corte_lapso');

        if (Schema::hasTable('lapso_academico') && Schema::hasColumn('lapso_academico', 'id_laa')) {
            DB::statement('ALTER TABLE lapso_academico DROP COLUMN id_laa');
        }

        if (Schema::hasTable('ano_academico') && Schema::hasColumn('ano_academico', 'id_ana')) {
            DB::statement('ALTER TABLE ano_academico DROP COLUMN id_ana');
        }

        if (Schema::hasTable('tipo_unidad_curricular') && Schema::hasColumn('tipo_unidad_curricular', 'codigo_tuc')) {
            Schema::table('tipo_unidad_curricular', function (Blueprint $table) {
                $table->dropColumn('codigo_tuc');
            });
        }

        if (Schema::hasTable('pais') && Schema::hasColumn('pais', 'id_pais')) {
            DB::statement('ALTER TABLE pais DROP COLUMN id_pais');
        }
    }
};

