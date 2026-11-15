<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('lapso_academico')) {
            Schema::create('lapso_academico', function (Blueprint $table) {
                $table->id('id_la');
                $table->string('nombre_la', 100);
                $table->date('fecha_inicio_la');
                $table->date('fecha_fin_la');
                $table->boolean('estado_la')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lapso_academico');
    }
};

