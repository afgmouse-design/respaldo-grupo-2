<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar campos a malla_curricular
        Schema::table('malla_curricular', function (Blueprint $table) {
            if (!Schema::hasColumn('malla_curricular', 'version_pensum_mac')) {
                $table->string('version_pensum_mac', 50)->nullable()->comment('Versión del Pensum (ej: Pensum-2026-V1)');
            }
            if (!Schema::hasColumn('malla_curricular', 'materias_obligatorias_tsu_mac')) {
                $table->integer('materias_obligatorias_tsu_mac')->nullable()->comment('Total de materias obligatorias requeridas para TSU');
            }
            if (!Schema::hasColumn('malla_curricular', 'materias_obligatorias_ing_mac')) {
                $table->integer('materias_obligatorias_ing_mac')->nullable()->comment('Total de materias acumuladas requeridas para Ingeniería');
            }
        });

        // 2. Agregar campos a malla_curricular_detalle
        Schema::table('malla_curricular_detalle', function (Blueprint $table) {
            if (!Schema::hasColumn('malla_curricular_detalle', 'semestre_mcd')) {
                $table->integer('semestre_mcd')->nullable()->default(1)->comment('Semestre o trimestre del trayecto');
            }
            if (!Schema::hasColumn('malla_curricular_detalle', 'etapa_curricular_mcd')) {
                $table->string('etapa_curricular_mcd', 50)->nullable()->default('TSU')->comment('TSU o INGENIERIA');
            }
        });

        // 3. Poblar datos por defecto en la malla actual de Informática
        DB::table('malla_curricular')->where('id_mac', 1)->update([
            'version_pensum_mac'             => 'PENSUM-PNFI-2026-V1',
            'creditos_minimos_tsu_mac'       => 96,
            'horas_minimas_tsu_mac'          => 2880,
            'materias_obligatorias_tsu_mac'  => 20,
            'creditos_minimos_ing_mac'       => 180,
            'horas_minimas_ing_mac'          => 5400,
            'materias_obligatorias_ing_mac'  => 38,
            'creditos_minimos_prosecucion_mac' => 84,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('malla_curricular', function (Blueprint $table) {
            $table->dropColumn([
                'version_pensum_mac',
                'materias_obligatorias_tsu_mac',
                'materias_obligatorias_ing_mac',
            ]);
        });

        Schema::table('malla_curricular_detalle', function (Blueprint $table) {
            $table->dropColumn([
                'semestre_mcd',
                'etapa_curricular_mcd',
            ]);
        });
    }
};

