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
        Schema::table('documentos_consignados', function (Blueprint $table) {
            $table->string('dco_archivo_path', 255)->after('dco_observaciones')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_consignados', function (Blueprint $table) {
            $table->dropColumn('dco_archivo_path');
        });
    }
};
