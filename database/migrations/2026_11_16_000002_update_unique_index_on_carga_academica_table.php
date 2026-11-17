<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Índice Único Compuesto `(sec_id, unc_id)` en `carga_academica` (SUD).
 *
 * Propósito: Reemplaza la restricción anterior `(sec_id, unc_id, pep_id)` por un índice
 * único parcial `UNIQUE (sec_id, unc_id) WHERE deleted_at IS NULL`, garantizando a nivel
 * de base de datos que no puedan asignarse dos docentes distintos a una misma unidad
 * curricular dentro de la misma sección mientras las asignaciones anteriores eliminadas
 * lógicamente conservan su historial auditable.
 *
 * Historial:
 *  - 2026-11-16: Creación de `uq_carga_academica_seccion_unidad` y retiro de `uq_carga_seccion_materia_docente`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('carga_academica')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE carga_academica DROP CONSTRAINT IF EXISTS uq_carga_seccion_materia_docente');
            DB::statement('DROP INDEX IF EXISTS uq_carga_seccion_materia_docente');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS uq_carga_academica_seccion_unidad ON carga_academica (sec_id, unc_id) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('carga_academica')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS uq_carga_academica_seccion_unidad');
            DB::statement('ALTER TABLE carga_academica ADD CONSTRAINT uq_carga_seccion_materia_docente UNIQUE (sec_id, unc_id, pep_id)');
        }
    }
};
