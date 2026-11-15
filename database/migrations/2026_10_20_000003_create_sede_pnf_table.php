<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla puente `sede_pnf` (Oferta académica por núcleo/sede).
 * Propósito: Vincula una sede con los PNF que en ella se imparten.
 * Referenciada por `cohorte_sede.sep_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sede_pnf')) {
            Schema::create('sede_pnf', function (Blueprint $table) {
                $table->id('id_sep');
                $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('pnf_id')->constrained('pnf', 'id_pnf')->cascadeOnUpdate()->cascadeOnDelete();
                $table->boolean('status')->default(true);
                $table->integer('status_id')->default(1);
                $table->unsignedBigInteger('created_id')->nullable();
                $table->unsignedBigInteger('updated_id')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();

                $table->unique(['sed_id', 'pnf_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sede_pnf');
    }
};

