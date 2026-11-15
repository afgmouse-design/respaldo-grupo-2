<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración: Incorporación de status_id y status a lapso_academico.
 * Propósito: Cumplir con el estándar estricto del Modelo ER SOGAC y soportar persistencia Eloquent::create()/update()
 * con status_id (1 = Activo, 2 = Inactivo).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lapso_academico')) {
            Schema::table('lapso_academico', function (Blueprint $table) {
                if (!Schema::hasColumn('lapso_academico', 'status_id')) {
                    $table->integer('status_id')->default(1)->nullable();
                }
                if (!Schema::hasColumn('lapso_academico', 'status')) {
                    $table->boolean('status')->default(true)->nullable();
                }
            });

            // Sincronizar registros existentes
            DB::table('lapso_academico')
                ->where('estado_la', false)
                ->update(['status_id' => 2, 'status' => false]);

            DB::table('lapso_academico')
                ->where('estado_la', true)
                ->update(['status_id' => 1, 'status' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lapso_academico')) {
            Schema::table('lapso_academico', function (Blueprint $table) {
                if (Schema::hasColumn('lapso_academico', 'status_id')) {
                    $table->dropColumn('status_id');
                }
                if (Schema::hasColumn('lapso_academico', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};

