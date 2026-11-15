<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Incorporación de deleted_at para soporte de eliminación lógica/cascada
 * en las tablas `ano_academico` y `lapso_academico`.
 *
 * Propósito: Permitir la eliminación en cascada sin quebrar la integridad referencial
 * de PostgreSQL ni generar errores de clave foránea con tablas dependientes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ano_academico') && !Schema::hasColumn('ano_academico', 'deleted_at')) {
            Schema::table('ano_academico', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('lapso_academico') && !Schema::hasColumn('lapso_academico', 'deleted_at')) {
            Schema::table('lapso_academico', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ano_academico') && Schema::hasColumn('ano_academico', 'deleted_at')) {
            Schema::table('ano_academico', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }

        if (Schema::hasTable('lapso_academico') && Schema::hasColumn('lapso_academico', 'deleted_at')) {
            Schema::table('lapso_academico', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }
};

