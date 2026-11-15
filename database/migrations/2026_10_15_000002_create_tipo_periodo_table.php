<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `tipo_periodo` (Módulo: Estructura Académica / Régimen).
 *
 * Propósito: Almacena las modalidades de períodos académicos (Semestral, Anual, Trayecto, Trimestral, etc.).
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('tipo_periodo', function (Blueprint $table) {
            $table->id('id_tpd')->comment('Identificador de la modalidad de período');
            $table->string('nombre_tpd', 100)->unique()->comment('Nombre (ej. Semestral, Trayecto/Anual, Trimestral)');
            $table->string('codigo_tpd', 30)->nullable()->comment('Código referencial (ej. SEM, TRA, TRI)');
            $table->integer('duracion_semanas_tpd')->nullable()->comment('Duración estándar en semanas');
            $table->text('descripcion_tpd')->nullable()->comment('Descripción reglamentaria');

            // Estatus lógico
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');
            $table->integer('status_id')->default(1)->comment('1=Activo, 2=Inactivo');

            // Auditoría
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_periodo');
    }
};

