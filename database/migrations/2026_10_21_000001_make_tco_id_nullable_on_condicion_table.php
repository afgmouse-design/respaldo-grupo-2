<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: vuelve opcional (nullable) la FK `tco_id` de la tabla `condicion`.
 * Propósito: por ahora el maestro de Condición no solicita "Tipo de Condición" en sus formularios,
 * por lo que el registro debe poder guardarse sin ese dato. Se conserva la FK hacia tipo_condicion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condicion', function (Blueprint $table) {
            $table->unsignedBigInteger('tco_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Nota: fallará si existen condiciones con tco_id NULL; asígneles un tipo antes de revertir.
        Schema::table('condicion', function (Blueprint $table) {
            $table->unsignedBigInteger('tco_id')->nullable(false)->change();
        });
    }
};

