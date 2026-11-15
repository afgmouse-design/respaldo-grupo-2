<?php

namespace Database\Seeders\SeguridadYAccesos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Permisos del maestro Sede PNF (oferta académica por sede, módulo Programación Académica).
 * Registra de forma idempotente sede_pnf.ver / crear / editar / eliminar.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\SedePnfPermisosSeeder"
 */
class SedePnfPermisosSeeder extends Seeder
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
            'sede_pnf.ver'      => 'Ver ofertas Sede PNF',
            'sede_pnf.crear'    => 'Crear ofertas Sede PNF',
            'sede_pnf.editar'   => 'Editar ofertas Sede PNF',
            'sede_pnf.eliminar' => 'Activar/desactivar ofertas Sede PNF',
        ];

        foreach ($permisos as $key => $nombre) {
            DB::table('permiso')->updateOrInsert(
                ['key' => $key],
                ['nombre' => $nombre, 'modulo_id' => $moduloId, 'activo' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

