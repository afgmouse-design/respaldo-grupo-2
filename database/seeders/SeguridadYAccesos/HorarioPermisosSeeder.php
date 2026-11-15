<?php

namespace Database\Seeders\SeguridadYAccesos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Permisos del maestro Horario (Grupo 3: Ángel Gonzales).
 *
 * Propósito: Registra de forma idempotente (con updateOrInsert) el módulo
 * `programacion_academica` y los permisos horario.ver / crear / editar / eliminar
 * en las tablas `modulo` y `permiso`.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\HorarioPermisosSeeder"
 */
class HorarioPermisosSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('modulo') || !Schema::hasTable('permiso')) {
            $this->command->warn('Tablas "modulo" o "permiso" no existen en la BD actual. Omitiendo inserción de permisos.');
            return;
        }

        $now = now();

        DB::table('modulo')->updateOrInsert(
            ['key' => 'programacion_academica'],
            ['nombre' => 'Programación Académica', 'activo' => true, 'created_at' => $now, 'updated_at' => $now]
        );
        $moduloId = DB::table('modulo')->where('key', 'programacion_academica')->value('id_modulo');

        $permisos = [
            'horario.ver'      => 'Ver horarios académicos',
            'horario.crear'    => 'Crear horarios académicos',
            'horario.editar'   => 'Editar horarios académicos',
            'horario.eliminar' => 'Activar/desactivar horarios académicos',
        ];

        foreach ($permisos as $key => $nombre) {
            DB::table('permiso')->updateOrInsert(
                ['key' => $key],
                ['nombre' => $nombre, 'modulo_id' => $moduloId, 'activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

