<?php

namespace Database\Seeders\SeguridadYAccesos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Permisos del maestro Condición (módulo Organización Institucional).
 * Registra de forma idempotente condicion.ver / crear / editar / eliminar.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\CondicionPermisosSeeder"
 */
class CondicionPermisosSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('modulo') || !Schema::hasTable('permiso')) {
            $this->command->warn('Tablas "modulo" o "permiso" no existen en la BD actual. Omitiendo inserción de permisos.');
            return;
        }

        $now = now();

        DB::table('modulo')->updateOrInsert(
            ['key' => 'organizacion_institucional'],
            ['nombre' => 'Organización Institucional', 'activo' => true, 'created_at' => $now, 'updated_at' => $now]
        );
        $moduloId = DB::table('modulo')->where('key', 'organizacion_institucional')->value('id_modulo');

        $permisos = [
            'condicion.ver'      => 'Ver condiciones',
            'condicion.crear'    => 'Crear condiciones',
            'condicion.editar'   => 'Editar condiciones',
            'condicion.eliminar' => 'Activar/desactivar condiciones',
        ];

        foreach ($permisos as $key => $nombre) {
            DB::table('permiso')->updateOrInsert(
                ['key' => $key],
                ['nombre' => $nombre, 'modulo_id' => $moduloId, 'activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

