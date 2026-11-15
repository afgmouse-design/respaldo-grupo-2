<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `parroquia` (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Almacena las parroquias (división político-territorial menor)
 * y las vincula con su municipio mediante `mun_id` → `municipio.id_mun`.
 *
 * Reglas de integridad:
 *  - No se permite repetir el nombre de una parroquia dentro del mismo municipio.
 *  - `restrictOnDelete`: no se puede eliminar físicamente un municipio con
 *    parroquias asociadas (la baja debe ser lógica vía `status`).
 */
return new class extends Migration
{
    /**
     * Crea la tabla `parroquia`.
     */
    public function up(): void
    {
        Schema::create('parroquia', function (Blueprint $table) {
            $table->id('id_par')->comment('Identificador autoincremental único de la parroquia');
            $table->string('nombre_par', 255)->comment('Nombre de la división político-territorial menor');

            // Relación con municipio
            $table->foreignId('mun_id')
                ->constrained('municipio', 'id_mun')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Clave foránea que vincula con el municipio correspondiente');

            // Borrado lógico (AGENTS.md §3)
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');

            // Auditoría: usuario que creó / actualizó el registro
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que creó el registro');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que actualizó por última vez');

            $table->timestampsTz();

            $table->unique(['mun_id', 'nombre_par']);
        });
    }

    /**
     * Elimina la tabla `parroquia`.
     */
    public function down(): void
    {
        Schema::dropIfExists('parroquia');
    }
};

