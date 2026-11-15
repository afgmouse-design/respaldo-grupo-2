<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `municipio` (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Almacena los municipios y los vincula con su estado mediante
 * `esd_id` → `estado.id_esd`.
 *
 * Reglas de integridad:
 *  - No se permite repetir el nombre de un municipio dentro del mismo estado.
 *  - `restrictOnDelete`: no se puede eliminar físicamente un estado con
 *    municipios asociados (la baja debe ser lógica vía `status`).
 */
return new class extends Migration
{
    /**
     * Crea la tabla `municipio`.
     */
    public function up(): void
    {
        Schema::create('municipio', function (Blueprint $table) {
            $table->id('id_mun')->comment('Identificador autoincremental único del municipio');
            $table->string('nombre_mun', 255)->comment('Nombre del municipio (Ej. Páez, Araure)');

            // Relación con estado
            $table->foreignId('esd_id')
                ->constrained('estado', 'id_esd')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Clave foránea que vincula con el estado correspondiente');

            // Borrado lógico (AGENTS.md §3)
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');

            // Auditoría: usuario que creó / actualizó el registro
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que creó el registro');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que actualizó por última vez');

            $table->timestampsTz();

            $table->unique(['esd_id', 'nombre_mun']);
        });
    }

    /**
     * Elimina la tabla `municipio`.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipio');
    }
};

