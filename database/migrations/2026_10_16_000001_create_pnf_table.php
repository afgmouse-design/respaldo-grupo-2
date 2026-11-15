<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `pnf` (Módulo: Estructura Académica / Oferta Académica).
 *
 * Propósito: Almacena los Programas Nacionales de Formación (carreras) ofertados por la institución,
 * calcando exactamente la estructura definida en el modelo entidad-relación SOGAC_ER_MODEL.sql.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('pnf', function (Blueprint $table) {
            $table->id('id_pnf')->comment('Identificador único de la carrera');
            $table->string('codigo_pnf', 30)->nullable()->comment('Código correlativo interno o ministerial de la carrera');
            $table->string('nombre_pnf', 255)->comment('Denominación completa (Ej. PNF en Agroalimentación)');

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
        Schema::dropIfExists('pnf');
    }
};

