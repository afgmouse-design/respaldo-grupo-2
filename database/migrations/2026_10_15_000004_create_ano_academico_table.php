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
        if (!Schema::hasTable('ano_academico')) {
            Schema::create('ano_academico', function (Blueprint $table) {
                $table->id('id_ano');
                $table->string('nombre_ano', 100);
                $table->date('fecha_inicio_ano')->nullable();
                $table->date('fecha_fin_ano')->nullable();
                $table->boolean('status')->default(true);
            });
        }

        if (Schema::hasTable('lapso_academico')) {
            Schema::table('lapso_academico', function (Blueprint $table) {
                if (!Schema::hasColumn('lapso_academico', 'ano_id')) {
                    $table->unsignedBigInteger('ano_id')->nullable()->after('id_la');
                    $table->foreign('ano_id')->references('id_ano')->on('ano_academico')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lapso_academico')) {
            Schema::table('lapso_academico', function (Blueprint $table) {
                if (Schema::hasColumn('lapso_academico', 'ano_id')) {
                    $table->dropForeign(['ano_id']);
                    $table->dropColumn('ano_id');
                }
            });
        }
        Schema::dropIfExists('ano_academico');
    }
};

