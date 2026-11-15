<?php

namespace Database\Seeders\SeguridadYAccesos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Permisos del maestro Sección (módulo Programación Académica).
 * Registra de forma idempotente seccion.ver / crear / editar / eliminar.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\SeccionPermisosSeeder"
 */
class SeccionPermisosSeeder extends Seeder
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
            'seccion.ver'      => 'Ver secciones',
            'seccion.crear'    => 'Crear secciones',
            'seccion.editar'   => 'Editar secciones',
            'seccion.eliminar' => 'Activar/desactivar secciones',
        ];

        foreach ($permisos as $key => $nombre) {
            DB::table('permiso')->updateOrInsert(
                ['key' => $key],
                ['nombre' => $nombre, 'modulo_id' => $moduloId, 'activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

