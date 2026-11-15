<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Incorporar semestre, trayecto y carga académica a `unidad_curricular`.
 *
 * Propósito: Permite identificar de manera directa a qué semestre y trayecto pertenece
 * una unidad curricular (materia) dentro del diseño curricular universitario.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::table('unidad_curricular', function (Blueprint $table) {
            $table->integer('semestre_unc')->nullable()->default(1)->after('tuc_id')->comment('Semestre o periodo académico sugerido (1, 2, etc.)');
            $table->foreignId('tra_id')->nullable()->after('semestre_unc')->constrained('trayecto', 'id_tra')->nullOnDelete()->comment('Trayecto formativo al que pertenece');
            $table->integer('unidades_credito_unc')->nullable()->default(3)->after('tra_id')->comment('Unidades de crédito (UC)');
            $table->integer('htea_unc')->nullable()->default(3)->after('unidades_credito_unc')->comment('Horas de acompañamiento docente');
            $table->integer('htei_unc')->nullable()->default(3)->after('htea_unc')->comment('Horas de estudio independiente');
            $table->integer('thte_unc')->nullable()->default(6)->after('htei_unc')->comment('Total horas de trabajo del estudiante');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::table('unidad_curricular', function (Blueprint $table) {
            $table->dropForeign(['tra_id']);
            $table->dropColumn([
                'semestre_unc',
                'tra_id',
                'unidades_credito_unc',
                'htea_unc',
                'htei_unc',
                'thte_unc',
            ]);
        });
    }
};

