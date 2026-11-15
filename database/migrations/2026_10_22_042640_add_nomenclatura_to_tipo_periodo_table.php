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
        Schema::table('tipo_periodo', function (Blueprint $table) {
            $table->string('nomenclatura', 50)->nullable()->after('codigo_tpd')
                ->comment('Identificador visual del período: Semestre, Trimestre o Anual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_periodo', function (Blueprint $table) {
            $table->dropColumn('nomenclatura');
        });
    }
};
