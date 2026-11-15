<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `estado` (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Almacena las entidades federales (estados/provincias) y las
 * vincula con su país mediante `pai_id` → `pais.id_pai`.
 *
 * Reglas de integridad:
 *  - No se permite repetir el nombre de un estado dentro del mismo país.
 *  - `restrictOnDelete`: no se puede eliminar físicamente un país con estados
 *    asociados (la baja debe ser lógica vía `status`).
 */
return new class extends Migration
{
    /**
     * Crea la tabla `estado`.
     */
    public function up(): void
    {
        Schema::create('estado', function (Blueprint $table) {
            $table->id('id_esd')->comment('Identificador autoincremental único del estado o provincia');
            $table->string('nombre_esd', 255)->comment('Nombre de la entidad federal (Ej. Portuguesa)');

            // Relación con país
            $table->foreignId('pai_id')
                ->constrained('pais', 'id_pai')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Clave foránea que vincula con el país correspondiente');

            // Borrado lógico (AGENTS.md §3)
            $table->boolean('status')->default(true)->comment('true = Activo, false = Inactivo');

            // Auditoría: usuario que creó / actualizó el registro
            $table->foreignId('created_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que creó el registro');
            $table->foreignId('updated_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Usuario que actualizó por última vez');

            $table->timestampsTz();

            $table->unique(['pai_id', 'nombre_esd']);
        });
    }

    /**
     * Elimina la tabla `estado`.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado');
    }
};

