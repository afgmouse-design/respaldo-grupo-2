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
        Schema::create('lapsos_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('lap_nombre', 20);
            $table->date('lap_fecha_inicio')->nullable();
            $table->date('lap_fecha_fin')->nullable();
            $table->foreignId('lap_tipo')->constrained('tipo_lapsos');
            $table->integer('lap_cod_universidad');
            $table->char('lap_condicion', 1)->default('A');
            $table->char('lap_cerrado', 1)->default('N');
            $table->char('lap_nota', 1)->default('N');
            $table->boolean('lap_estatus')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lapsos_academicos');
    }
};
