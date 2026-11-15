<?php

namespace Database\Seeders\SeguridadYAccesos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Permisos del maestro Tipo de Aula.
 *
 * Propósito: registra (de forma idempotente, con updateOrInsert) el módulo
 * `programacion_academica` y los permisos tipo_aula.ver / crear / editar / eliminar
 * en las tablas `modulo` y `permiso`, para que cualquier miembro del equipo los replique.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\TipoAulaPermisosSeeder"
 */
class TipoAulaPermisosSeeder extends Seeder
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
            'tipo_aula.ver'      => 'Ver tipos de aula',
            'tipo_aula.crear'    => 'Crear tipos de aula',
            'tipo_aula.editar'   => 'Editar tipos de aula',
            'tipo_aula.eliminar' => 'Activar/desactivar tipos de aula',
        ];

        foreach ($permisos as $key => $nombre) {
            DB::table('permiso')->updateOrInsert(
                ['key' => $key],
                ['nombre' => $nombre, 'modulo_id' => $moduloId, 'activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

