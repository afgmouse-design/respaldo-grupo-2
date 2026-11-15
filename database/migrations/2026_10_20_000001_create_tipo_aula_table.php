<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración del maestro `tipo_aula` (Programación Académica).
 * Propósito: catálogo independiente que clasifica los ambientes físicos
 * (aula teórica, laboratorio, taller, etc.). Es referenciado luego por `aula.tia_id`.
 * Usa borrado lógico mediante el campo booleano `status` (true = Activo).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tipo_aula')) {
            Schema::create('tipo_aula', function (Blueprint $table) {
                $table->id('id_tia');
                $table->string('nombre_tia', 150);
                $table->string('codigo_tia', 30)->nullable();
                $table->text('descripcion_tia')->nullable();
                $table->boolean('status')->default(true);
                // Actualización: se añaden status_id (1 = activo, 2 = inactivo), auditoría de usuario y
                // deleted_at para alinear la tabla con el patrón de los demás maestros y con
                // obtenerPaginados()/alternarEstatus().
                $table->integer('status_id')->default(1);
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_aula');
    }
};
