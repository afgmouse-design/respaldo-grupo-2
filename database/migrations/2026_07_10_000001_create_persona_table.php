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
        Schema::create('persona', function (Blueprint $table) {
            $table->string('per_cedula', 12)->primary();
            $table->string('per_documento', 20)->nullable();
            $table->string('per_nacionalidad', 2)->nullable();
            $table->string('per_nombres', 100);
            $table->string('per_apellidos', 100);
            $table->string('per_genero', 1)->nullable();
            $table->string('per_edo_civil', 1)->nullable();
            $table->date('per_fecha_nacimiento')->nullable();
            $table->string('per_email', 100)->nullable();
            $table->string('per_telefono_movil', 15)->nullable();
            $table->string('per_telefono_fijo', 15)->nullable();
            $table->string('per_telefono_trabajo', 15)->nullable();
            $table->string('per_pais_nacimiento', 100)->nullable();
            $table->string('per_estado_nacimiento', 100)->nullable();
            $table->string('per_cod_etnia', 10)->nullable();
            $table->string('per_estatus', 1)->default('A');
            $table->year('est_bachiller_ano_egreso')->nullable();
            $table->string('est_cod_seb', 20)->nullable();
            $table->string('est_cod_reb', 20)->nullable();
            $table->string('doc_cod_profesion', 20)->nullable();
            $table->string('doc_cod_categoria_inicial', 20)->nullable();
            $table->string('doc_cod_condicion_laboral', 20)->nullable();
            $table->string('doc_cod_categoria_actual', 20)->nullable();
            $table->string('doc_cod_dedicacion', 20)->nullable();
            $table->string('aut_cod_tipo_autoridad', 20)->nullable();
            $table->string('aut_gaceta', 50)->nullable();
            $table->date('aut_fecha_gaceta')->nullable();
            $table->string('resolucion_nombramiento', 50)->nullable();
            $table->string('tipo_consejo_directivo', 20)->nullable();
            $table->string('numero_consejo_directivo', 20)->nullable();
            $table->date('fecha_consejo_directivo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
