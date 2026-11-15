<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Extensión de campos académicos y cohorte en tabla `seccion`.
 * Módulo: Programación Académica / Oferta y Planificación.
 *
 * Incorpora: tipo_seccion, cos_id (cohorte_sede), mac_id (malla_curricular),
 * semestre_sec y trayecto_sec para blindar la sección a su diseño curricular.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seccion', function (Blueprint $table) {
            if (!Schema::hasColumn('seccion', 'codigo_sec')) {
                $table->string('codigo_sec', 50)->nullable()->after('id_sec')->comment('Código administrativo corto de la sección');
            }
            if (!Schema::hasColumn('seccion', 'tipo_seccion')) {
                $table->string('tipo_seccion', 50)->default('Regular')->after('nombre_sec')->comment('Régimen: Regular, PER (Plan Especial de Referencia), Repitiente');
            }
            if (!Schema::hasColumn('seccion', 'semestre_sec')) {
                $table->integer('semestre_sec')->default(1)->after('nivel_sec')->comment('Semestre específico del plan (1 al 8)');
            }
            if (!Schema::hasColumn('seccion', 'trayecto_sec')) {
                $table->integer('trayecto_sec')->default(1)->after('semestre_sec')->comment('Trayecto al que pertenece (0=Inicial, 1=T1, etc.)');
            }
            if (!Schema::hasColumn('seccion', 'cos_id')) {
                $table->foreignId('cos_id')
                    ->nullable()
                    ->after('sed_id')
                    ->constrained('cohorte_sede', 'id_cos')
                    ->cascadeOnUpdate()
                    ->nullOnDelete()
                    ->comment('Amarre estricto de cohorte por sede para evitar mezcla de planes');
            }
            if (!Schema::hasColumn('seccion', 'mac_id')) {
                $table->foreignId('mac_id')
                    ->nullable()
                    ->after('cos_id')
                    ->constrained('malla_curricular', 'id_mac')
                    ->cascadeOnUpdate()
                    ->nullOnDelete()
                    ->comment('Malla curricular obligatoria asociada a la sección');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seccion', function (Blueprint $table) {
            if (Schema::hasColumn('seccion', 'mac_id')) {
                $table->dropConstrainedForeignId('mac_id');
            }
            if (Schema::hasColumn('seccion', 'cos_id')) {
                $table->dropConstrainedForeignId('cos_id');
            }
            $table->dropColumn(array_filter([
                Schema::hasColumn('seccion', 'codigo_sec') ? 'codigo_sec' : null,
                Schema::hasColumn('seccion', 'tipo_seccion') ? 'tipo_seccion' : null,
                Schema::hasColumn('seccion', 'semestre_sec') ? 'semestre_sec' : null,
                Schema::hasColumn('seccion', 'trayecto_sec') ? 'trayecto_sec' : null,
            ]));
        });
    }
};

