<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `pais` (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Catálogo raíz de la jerarquía geográfica del sistema SOGAC
 * (País → Estado → Municipio → Parroquia → Localidad). Se usa para
 * normalizar la ubicación de personas, sedes y demás entidades.
 *
 * Convenciones aplicadas (AGENTS.md §3):
 *  - Nombre de tabla en singular.
 *  - Borrado lógico mediante booleano `status` (true = Activo, false = Inactivo),
 *    sin SoftDeletes nativo.
 *  - Campos de auditoría `created_id` / `updated_id` vinculados a `users`.
 */
return new class extends Migration
{
    /**
     * Crea la tabla `pais`.
     */
    public function up(): void
    {
        Schema::create('pais', function (Blueprint $table) {
            $table->id('id_pai')->comment('Identificador autoincremental único del país');
            $table->string('nombre_pai', 255)->unique()->comment('Nombre oficial del país (Ej. Venezuela)');

            // Borrado lógico (AGENTS.md §3)
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');

            // Auditoría: usuario que creó / actualizó el registro
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que creó el registro');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que actualizó por última vez');

            $table->timestampsTz();
        });
    }

    /**
     * Elimina la tabla `pais`.
     */
    public function down(): void
    {
        Schema::dropIfExists('pais');
    }
};

