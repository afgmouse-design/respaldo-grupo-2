<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `trayecto` (Módulo: Estructura Académica / Niveles).
 *
 * Propósito: Almacena los trayectos o niveles académicos pertenecientes a modalidades académicas (tipo_periodo).
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('trayecto', function (Blueprint $table) {
            $table->id('id_tra')->comment('Identificador único del trayecto o nivel académico');
            $table->string('codigo_tra', 20)->nullable()->comment('Código referencial (ej. T-INI, T-1, T-2)');
            $table->string('nombre_tra', 100)->comment('Nombre oficial del nivel (ej. Trayecto Inicial, Trayecto I)');
            $table->integer('numero_orden_tra')->default(1)->comment('Número correlativo de orden');

            // Foránea hacia tipo_periodo
            $table->foreignId('tpd_id')
                ->constrained('tipo_periodo', 'id_tpd')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Modalidad académica padre');

            // Estatus lógico reactivo
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
        Schema::dropIfExists('trayecto');
    }
};

