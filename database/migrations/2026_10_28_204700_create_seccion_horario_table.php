<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `seccion_horario` (Bloques Horarios y Espacios de Clases).
 * Módulo: Programación Académica / Motorización de Horarios y Aulas.
 *
 * Registra cada bloque horario semanal asignando el aula/laboratorio físico.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seccion_horario')) {
            Schema::create('seccion_horario', function (Blueprint $table) {
                $table->id('id_sho');
                $table->foreignId('sud_id')
                    ->constrained('seccion_unidad_docente', 'id_sud')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete()
                    ->comment('Asignación materia-docente-sección');

                $table->foreignId('aul_id')
                    ->constrained('aula', 'id_aul')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete()
                    ->comment('Ambiente o aula asignada');

                $table->smallInteger('dia_semana')->comment('1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado');
                $table->time('hora_inicio')->comment('Hora de inicio del bloque (ej. 07:30)');
                $table->time('hora_fin')->comment('Hora de fin del bloque (ej. 09:00)');
                $table->string('tipo_sesion', 50)->default('Teorica')->comment('Teorica, Practica/Lab');
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamps();

                $table->index(['aul_id', 'dia_semana', 'hora_inicio', 'hora_fin'], 'idx_sho_aula_anti_choque');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seccion_horario');
    }
};

