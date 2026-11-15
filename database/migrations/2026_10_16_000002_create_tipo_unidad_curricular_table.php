<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `tipo_unidad_curricular` (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Almacena los tipos o naturalezas de unidades curriculares (ej. Teórica, Práctica, Proyecto),
 * calcando exactamente la estructura definida en el modelo entidad-relación SOGAC_ER_MODEL.sql.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('tipo_unidad_curricular', function (Blueprint $table) {
            $table->id('id_tuc')->comment('Identificador del tipo de unidad curricular');
            $table->string('nombre_tuc', 150)->comment('Nombre del tipo (Ej. Teórica, Práctica, Proyecto)');

            // Estatus lógico reactivo y compatibilidad ER
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');
            $table->integer('status_id')->default(1)->comment('Estado lógico del registro (Ref: status. 1=Activo, 2=Inactivo)');

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
        Schema::dropIfExists('tipo_unidad_curricular');
    }
};

