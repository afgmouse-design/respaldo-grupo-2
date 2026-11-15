<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `escala_medicion` (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Almacena los modelos y escalas de calificación académica (ej. Escala 1 al 20,
 * Cualitativa, 1 al 100), con notas mínimas, máximas y nota aprobatoria.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('escala_medicion', function (Blueprint $table) {
            $table->id('id_esm')->comment('Identificador único del modelo de calificación');
            $table->string('nombre_esm', 255)->unique()->comment('Nombre o tipo de escala (ej. Escala 1 al 20, Cualitativa)');
            $table->decimal('nota_aprobatoria_esm', 5, 2)->comment('Valor mínimo necesario para aprobar la unidad curricular');
            $table->decimal('nota_minima_esm', 5, 2)->default(0.00)->nullable()->comment('Límite inferior de la escala permitida');
            $table->decimal('nota_maxima_esm', 5, 2)->default(20.00)->nullable()->comment('Límite superior absoluto de la escala');

            // Estatus lógico reactivo (AGENTS.md §3)
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');
            $table->integer('status_id')->default(1)->comment('Estado numérico de compatibilidad ER');

            // Auditoría y trazabilidad
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario creador');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario actualizador');

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('escala_medicion');
    }
};

