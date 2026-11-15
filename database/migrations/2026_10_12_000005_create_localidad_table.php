<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `localidad` (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Último nivel de la jerarquía geográfica. Almacena ciudades,
 * caseríos o sectores y los vincula con su parroquia mediante
 * `par_id` → `parroquia.id_par`.
 *
 * Reglas de integridad:
 *  - No se permite repetir el nombre de una localidad dentro de la misma parroquia.
 *  - `restrictOnDelete`: no se puede eliminar físicamente una parroquia con
 *    localidades asociadas (la baja debe ser lógica vía `status`).
 */
return new class extends Migration
{
    /**
     * Crea la tabla `localidad`.
     */
    public function up(): void
    {
        Schema::create('localidad', function (Blueprint $table) {
            $table->id('id_loc')->comment('Identificador autoincremental único de la localidad');
            $table->string('nombre_loc', 255)->comment('Nombre específico de la ciudad, caserío o sector');

            // Relación con parroquia
            $table->foreignId('par_id')
                ->constrained('parroquia', 'id_par')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Clave foránea que vincula con la parroquia correspondiente');

            // Borrado lógico (AGENTS.md §3)
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');

            // Auditoría: usuario que creó / actualizó el registro
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que creó el registro');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que actualizó por última vez');

            $table->timestampsTz();

            $table->unique(['par_id', 'nombre_loc']);
        });
    }

    /**
     * Elimina la tabla `localidad`.
     */
    public function down(): void
    {
        Schema::dropIfExists('localidad');
    }
};

