<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `seccion` (Módulo: Programación Académica / Oferta, Planificación e Inscripción).
 * Propósito: Agrupación macro de alumnos para cursar unidades curriculares en un lapso académico.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seccion')) {
            Schema::create('seccion', function (Blueprint $table) {
                $table->id('id_sec');
                $table->string('nombre_sec', 255)->comment('Nomenclatura administrativa (Ej. INF-1A-D)');
                $table->integer('nivel_sec')->default(1)->comment('Semestre o trayecto base esperado (Ej. Trayecto 1)');
                $table->string('turno_sec', 255)->comment('Bloque horario general (Mañana, Tarde, Noche)');
                $table->integer('cupo_maximo_sec')->default(35)->comment('Límite total de matriculados permitidos en el bloque');

                $table->foreignId('laa_id')->constrained('lapso_academico', 'id_la')->cascadeOnUpdate()->restrictOnDelete()->comment('Período lectivo de la sección');
                $table->foreignId('pnf_id')->constrained('pnf', 'id_pnf')->cascadeOnUpdate()->restrictOnDelete()->comment('Carrera a la que pertenece el grupo');
                $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnUpdate()->restrictOnDelete()->comment('Núcleo donde verán clases físicamente');

                $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');
                $table->integer('status_id')->default(1)->comment('1 = Activo, 2 = Inactivo');
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();

                $table->unique(['nombre_sec', 'laa_id', 'sed_id'], 'seccion_nombre_laa_sed_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seccion');
    }
};

