<?php

namespace Database\Seeders\SeguridadYAccesos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Permisos del maestro Cohorte por Sede (módulo Programación Académica).
 * Registra de forma idempotente cohorte_sede.ver / crear / editar / eliminar.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\CohorteSedePermisosSeeder"
 */
class CohorteSedePermisosSeeder extends Seeder
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
            'cohorte_sede.ver'      => 'Ver cohortes por sede',
            'cohorte_sede.crear'    => 'Crear cohortes por sede',
            'cohorte_sede.editar'   => 'Editar cohortes por sede',
            'cohorte_sede.eliminar' => 'Activar/desactivar cohortes por sede',
        ];

        foreach ($permisos as $key => $nombre) {
            DB::table('permiso')->updateOrInsert(
                ['key' => $key],
                ['nombre' => $nombre, 'modulo_id' => $moduloId, 'activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

