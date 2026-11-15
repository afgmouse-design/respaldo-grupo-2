<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración del maestro `aula` (Programación Académica).
 * Propósito: ambientes físicos de una sede, clasificados por `tipo_aula`.
 * Depende de `sede` (id_sed) y `tipo_aula` (id_tia). Baja lógica con `status`/`status_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aula')) {
            Schema::create('aula', function (Blueprint $table) {
                $table->id('id_aul');
                $table->string('codigo_aula_aul', 50);
                $table->unsignedInteger('capacidad_maxima_aul');
                $table->string('tipo_espacio_aul', 100)->nullable();
                $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('tia_id')->constrained('tipo_aula', 'id_tia')->cascadeOnUpdate()->restrictOnDelete();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();

                // El código de aula es único dentro de cada sede.
                $table->unique(['sed_id', 'codigo_aula_aul']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aula');
    }
};

