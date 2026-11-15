<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `unidad_curricular` (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Almacena el catálogo de materias o unidades curriculares base de la institución,
 * asociadas a su tipo de unidad curricular respectivo según el modelo SOGAC_ER_MODEL.sql.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('unidad_curricular', function (Blueprint $table) {
            $table->id('id_unc')->comment('Identificador de la materia base del catálogo universal');
            $table->string('codigo_unc', 30)->nullable()->comment('Alfanumérico referencial único (Ej. ALG-101)');
            $table->string('nombre_unc', 255)->comment('Título descriptivo de la materia');

            // Llave foránea hacia tipo_unidad_curricular (id_tuc)
            $table->foreignId('tuc_id')
                ->nullable()
                ->constrained('tipo_unidad_curricular', 'id_tuc')
                ->nullOnDelete()
                ->comment('Tipo de Unidad Curricular (Ref: tipo_unidad_curricular)');

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
        Schema::dropIfExists('unidad_curricular');
    }
};

