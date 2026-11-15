<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `cohorte_sede` (Módulo: Programación Académica / Ecosistema de Ingreso).
 * Propósito: Distribución de ingresantes por oferta de sede y PNF (sede_pnf).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cohorte_sede')) {
            Schema::create('cohorte_sede', function (Blueprint $table) {
                $table->id('id_cos');
                $table->string('nombre_cos', 255);
                $table->foreignId('coh_id')->constrained('cohorte', 'id_coh')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('sep_id')->constrained('sede_pnf', 'id_sep')->cascadeOnUpdate()->restrictOnDelete();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();

                $table->unique(['coh_id', 'sep_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cohorte_sede');
    }
};

