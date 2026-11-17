<?php

namespace Database\Seeders\SeguridadYAccesos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder: Catálogo Maestro de Estados (`status`).
 *
 * Propósito: Poblar de forma idempotente los estados base requeridos por todas las llaves
 * foráneas `status_id` del esquema oficial `sogac_mio.sql` (`1 = Activo`, `2 = Inactivo`,
 * `3 = Suspendido`). Debe ejecutarse antes que cualquier otro seeder.
 *
 * Historial:
 *  - 2026-11-14: Creación inicial alineada al catálogo `status(id_sta)` de `sogac_mio.sql`.
 */
class StatusSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('status')) {
            $this->command?->warn('La tabla "status" no existe en la BD actual. Omitiendo StatusSeeder.');
            return;
        }

        $now = now();

        $statuses = [
            [
                'id_sta'          => 1,
                'nombre_sta'      => 'Activo',
                'descripcion_sta' => 'Registro habilitado y operativo en el sistema.',
                'modulo_sta'      => 'GENERAL',
            ],
            [
                'id_sta'          => 2,
                'nombre_sta'      => 'Inactivo',
                'descripcion_sta' => 'Registro deshabilitado lógicamente sin eliminación física.',
                'modulo_sta'      => 'GENERAL',
            ],
            [
                'id_sta'          => 3,
                'nombre_sta'      => 'Suspendido',
                'descripcion_sta' => 'Registro restringido temporalmente por control administrativo o de seguridad.',
                'modulo_sta'      => 'SEGURIDAD',
            ],
        ];

        DB::transaction(function () use ($statuses, $now): void {
            foreach ($statuses as $status) {
                DB::table('status')->updateOrInsert(
                    ['id_sta' => $status['id_sta']],
                    [
                        'nombre_sta'      => $status['nombre_sta'],
                        'descripcion_sta' => $status['descripcion_sta'],
                        'modulo_sta'      => $status['modulo_sta'],
                        'deleted_at'      => null,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]
                );
            }

            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SELECT setval(pg_get_serial_sequence('status', 'id_sta'), COALESCE((SELECT MAX(id_sta) FROM status), 1))");
            }
        });
    }
}
