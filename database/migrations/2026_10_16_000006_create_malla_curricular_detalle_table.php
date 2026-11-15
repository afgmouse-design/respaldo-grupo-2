<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `malla_curricular_detalle` (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Almacena las unidades curriculares pertenecientes a una malla específica,
 * asociadas a un trayecto, eje curricular y escala de medición, calcando los tipos de
 * datos y restricciones únicas definidas en el modelo entidad-relación SOGAC_ER_MODEL.sql.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('malla_curricular_detalle', function (Blueprint $table) {
            $table->id('id_mcd')->comment('Identificador único del renglón/materia configurada dentro de una malla');

            // Métricas y cargas académicas
            $table->integer('unidades_credito_mcd')->comment('Unidades de crédito (UC) otorgadas por la materia en esta malla específica');
            $table->integer('htea_mcd')->nullable()->comment('Horas de Trabajo Estudiantil Acompañado / Asistidas por docente semanales');
            $table->integer('htei_mcd')->nullable()->comment('Horas de Trabajo Estudiantil Independiente / Estudio autónomo semanales');
            $table->integer('thte_mcd')->nullable()->comment('Total de Horas de Trabajo Estudiantil semanales (HTEA + HTEI)');
            $table->integer('duracion_semanas_mcd')->nullable()->comment('Duración en semanas lectivas de la materia en esta malla (Ej. 12, 16, 36 semanas)');

            // Llaves foráneas
            $table->foreignId('mac_id')
                ->constrained('malla_curricular', 'id_mac')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
                ->comment('Plan de estudios cabecera al que pertenece esta materia (Ref: malla_curricular)');

            $table->foreignId('unc_id')
                ->constrained('unidad_curricular', 'id_unc')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Materia universal del catálogo maestro (Ref: unidad_curricular)');

            $table->foreignId('tra_id')
                ->constrained('trayecto', 'id_tra')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Trayecto o nivel en el que se imparte la materia en esta malla (Ref: trayecto)');

            $table->foreignId('ecu_id')
                ->nullable()
                ->constrained('eje_curricular', 'id_ecu')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Eje curricular formativo asignado a la materia en esta malla (Ref: eje_curricular)');

            $table->foreignId('esm_id')
                ->nullable()
                ->constrained('escala_medicion', 'id_esm')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Escala de evaluación y nota aprobatoria asignada a la materia en esta malla (Ref: escala_medicion)');

            // Estatus lógico
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');
            $table->integer('status_id')->default(1)->comment('Estado lógico del registro (Ref: status. 1=Activo, 2=Inactivo)');

            // Auditoría y trazabilidad
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario creador');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario actualizador');

            $table->timestampsTz();
            $table->softDeletesTz();

            // Restricción única compuesta según ER SOGAC
            $table->unique(['mac_id', 'unc_id', 'tra_id'], 'uk_mcd_mac_unc_tra');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('malla_curricular_detalle');
    }
};

