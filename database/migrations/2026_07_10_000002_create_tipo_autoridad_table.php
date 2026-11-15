<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_autoridad', function (Blueprint $table) {
            $table->id('tau_id');
            $table->string('tau_nombre', 150);
            $table->unsignedTinyInteger('tau_nivel')->default(1);
            $table->char('tau_estatus', 1)->default('A');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_autoridad');
    }
};
