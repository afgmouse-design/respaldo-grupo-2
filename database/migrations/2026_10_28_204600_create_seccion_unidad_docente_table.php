<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `seccion_unidad_docente` (Asignación Académica Docente por Sección).
 * Módulo: Programación Académica / Oferta y Carga Docente.
 *
 * Vincula una sección con una materia de la malla curricular y un docente responsable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seccion_unidad_docente')) {
            Schema::create('seccion_unidad_docente', function (Blueprint $table) {
                $table->id('id_sud');
                $table->foreignId('sec_id')
                    ->constrained('seccion', 'id_sec')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete()
                    ->comment('Sección a la que pertenece esta unidad curricular');

                $table->foreignId('mcd_id')
                    ->constrained('malla_curricular_detalle', 'id_mcd')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete()
                    ->comment('Unidad curricular específica de la malla');

                $table->foreignId('docente_id')
                    ->constrained('users', 'id')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete()
                    ->comment('Docente asignado a la materia');

                $table->string('tipo_contratacion', 50)->default('Titular')->comment('Titular, Auxiliar, Suplente');
                $table->integer('horas_semanales')->default(4)->comment('Carga horaria semanal asignada');
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamps();

                $table->unique(['sec_id', 'mcd_id'], 'uk_sud_sec_mcd');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seccion_unidad_docente');
    }
};

