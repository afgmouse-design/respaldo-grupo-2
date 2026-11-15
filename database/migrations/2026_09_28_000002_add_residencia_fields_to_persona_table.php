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
            if (!Schema::hasColumn('persona', 'per_estado_residencia')) {
                $table->string('per_estado_residencia', 100)->nullable()->after('per_direccion_habitacion');
            }
            if (!Schema::hasColumn('persona', 'per_municipio_residencia')) {
                $table->string('per_municipio_residencia', 100)->nullable()->after('per_estado_residencia');
            }
            if (!Schema::hasColumn('persona', 'per_parroquia_residencia')) {
                $table->string('per_parroquia_residencia', 100)->nullable()->after('per_municipio_residencia');
            }
            if (!Schema::hasColumn('persona', 'per_documento_alternativo')) {
                $table->string('per_documento_alternativo', 50)->nullable()->after('per_tipo_documento');
            }
            if (!Schema::hasColumn('persona', 'per_codigo')) {
                $table->unsignedBigInteger('per_codigo')->nullable()->after('per_cedula');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $cols = [
                'per_estado_residencia',
                'per_municipio_residencia',
                'per_parroquia_residencia',
                'per_documento_alternativo',
                'per_codigo',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('persona', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
