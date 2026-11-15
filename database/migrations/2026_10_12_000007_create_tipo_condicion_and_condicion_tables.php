<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tablas `tipo_condicion` y `condicion` (Módulo 2 - Organización Institucional).
 *
 * Propósito: Catálogo para clasificar y definir condiciones o estados funcionales
 * aplicables a instituciones, sedes, dependencias o departamentos.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tipo_condicion')) {
            Schema::create('tipo_condicion', function (Blueprint $table) {
                $table->id('id_tco')->comment('Identificador único del tipo o ámbito de condición');
                $table->string('nombre_tco', 100)->unique()->comment('Nombre del ámbito (ej. Universidad, Sede, Departamento)');
                $table->text('descripcion_tco')->nullable()->comment('Descripción conceptual del ámbito');
                $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');
                $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestampsTz();
                $table->softDeletesTz();
            });
        }

        if (!Schema::hasTable('condicion')) {
            Schema::create('condicion', function (Blueprint $table) {
                $table->id('id_cdn')->comment('Identificador autoincremental de la condición');
                // Alias o compatibilidad id_cdn
                $table->string('nombre_cdn', 100)->comment('Nombre de la condición (ej. Sede Principal, Extensión, Núcleo)');
                $table->text('descripcion_cdn')->nullable()->comment('Descripción conceptual');

                $table->foreignId('tco_id')
                    ->constrained('tipo_condicion', 'id_tco')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete()
                    ->comment('Ámbito al que aplica la condición');

                $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');
                $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestampsTz();
                $table->softDeletesTz();

                $table->unique(['tco_id', 'nombre_cdn']);
            });

            // Renombrar llave primaria si es necesario o asegurar id_cdn
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('condicion');
        Schema::dropIfExists('tipo_condicion');
    }
};
