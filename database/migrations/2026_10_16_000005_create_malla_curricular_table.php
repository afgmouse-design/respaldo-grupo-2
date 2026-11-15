<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `malla_curricular` (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Almacena las mallas curriculares y diseños de pénsum de estudio de los PNF,
 * calcando los tipos de datos definidos en el modelo entidad-relación SOGAC_ER_MODEL.sql.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('malla_curricular', function (Blueprint $table) {
            $table->id('id_mac')->comment('Identificador único del plan de estudios / diseño curricular cabecera');
            $table->string('nombre_mac', 255)->comment('Nombre oficial de la malla (Ej. Malla PNF Informática 2024)');
            $table->string('codigo_gaceta_mac', 100)->nullable()->comment('Número de Gaceta Oficial o Resolución ministerial que aprueba el pénsum');
            $table->date('fecha_aprobacion_mac')->nullable()->comment('Fecha de aprobación u oficialización del plan de estudios');
            $table->text('observacion_mac')->nullable()->comment('Notas sobre la vigencia, modalidad de estudio o transición de la malla');

            // Llave foránea hacia pnf (id_pnf)
            $table->foreignId('pnf_id')
                ->constrained('pnf', 'id_pnf')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Carrera o PNF al que pertenece este plan de estudios (Ref: pnf)');

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
        Schema::dropIfExists('malla_curricular');
    }
};

