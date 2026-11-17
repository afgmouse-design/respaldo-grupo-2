<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Parámetros Académicos en `malla_curricular_detalle`.
 *
 * Propósito: Agrega `duracion_semestres_mcd` (1 o 2 semestres, por defecto 1) y amplía la precisión
 * de `nota_minima_aprobatoria_mcd` a `decimal(5, 2)` (homologada con `escala_medicion.nota_aprobatoria_esm`)
 * para evitar desbordamiento numérico en escalas porcentuales (0–100). Las columnas `ecu_id`, `esm_id`,
 * `htea_mcd`, `htei_mcd` y `thte_mcd` ya existen en la tabla oficial `malla_curricular_detalle`.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial de columna aditiva `duracion_semestres_mcd` y ampliación decimal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('malla_curricular_detalle')) {
            return;
        }

        Schema::table('malla_curricular_detalle', function (Blueprint $table) {
            if (! Schema::hasColumn('malla_curricular_detalle', 'duracion_semestres_mcd')) {
                $table->smallInteger('duracion_semestres_mcd')->default(1)->after('duracion_semanas_mcd');
            }
        });

        if (Schema::hasColumn('malla_curricular_detalle', 'nota_minima_aprobatoria_mcd')) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE malla_curricular_detalle ALTER COLUMN nota_minima_aprobatoria_mcd TYPE numeric(5,2)');
            } else {
                Schema::table('malla_curricular_detalle', function (Blueprint $table) {
                    $table->decimal('nota_minima_aprobatoria_mcd', 5, 2)->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('malla_curricular_detalle')) {
            return;
        }

        if (Schema::hasColumn('malla_curricular_detalle', 'duracion_semestres_mcd')) {
            Schema::table('malla_curricular_detalle', function (Blueprint $table) {
                $table->dropColumn('duracion_semestres_mcd');
            });
        }
    }
};
