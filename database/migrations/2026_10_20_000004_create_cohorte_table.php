<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `cohorte` (Módulo: Programación Académica / Ecosistema de Ingreso).
 * Propósito: Agrupación temporal de estudiantes de nuevo ingreso vinculada a un Lapso Académico.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cohorte')) {
            Schema::create('cohorte', function (Blueprint $table) {
                $table->id('id_coh');
                $table->string('nombre_coh', 255);
                $table->date('fecha_inicio_coh');
                $table->foreignId('laa_id')->constrained('lapso_academico', 'id_la')->cascadeOnUpdate()->restrictOnDelete();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cohorte');
    }
};

