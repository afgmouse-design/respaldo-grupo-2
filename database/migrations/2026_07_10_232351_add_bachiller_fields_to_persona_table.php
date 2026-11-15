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
        Schema::table('persona', function (Blueprint $table) {
            $table->string('est_bachiller_dependencia', 50)->nullable()->after('est_bachiller_ano_egreso');
            $table->string('est_bachiller_estado', 100)->nullable()->after('est_bachiller_dependencia');
            $table->string('est_bachiller_municipio', 100)->nullable()->after('est_bachiller_estado');
            $table->string('est_bachiller_parroquia', 100)->nullable()->after('est_bachiller_municipio');
            $table->string('est_bachiller_localidad', 100)->nullable()->after('est_bachiller_parroquia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $table->dropColumn([
                'est_bachiller_dependencia',
                'est_bachiller_estado',
                'est_bachiller_municipio',
                'est_bachiller_parroquia',
                'est_bachiller_localidad',
            ]);
        });
    }
};
