<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `eje_curricular` (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Almacena los ejes curriculares formativos de los planes de estudio
 * (ej. Eje Profesional, Eje de Proyecto, Eje Socio-Crítico, Eje Estético-Lúdico),
 * calcando exactamente la estructura definida en el modelo entidad-relación SOGAC_ER_MODEL.sql.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('eje_curricular', function (Blueprint $table) {
            $table->id('id_ecu')->comment('Identificador del eje curricular formativo');
            $table->string('nombre_ecu', 100)->comment('Nombre del eje (Ej. Eje Profesional, Eje de Proyecto, Eje Socio-Crítico, Eje Estético-Lúdico)');
            $table->string('codigo_ecu', 30)->nullable()->comment('Código alfanumérico del eje');
            $table->text('descripcion_ecu')->nullable()->comment('Descripción o propósito formativo del eje');

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
        Schema::dropIfExists('eje_curricular');
    }
};

