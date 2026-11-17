<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos del maestro Cohorte (`cohorte`).
 *
 * Propósito: Registra de forma idempotente `cohorte.ver/crear/editar/eliminar` en `permiso`
 * vinculados al módulo `Programación Académica` y la vista `programacion.cohorte`.
 *
 * Historial:
 *  - 2026-11-14: Migrado a `sogac_mio.sql` vía `SincronizaPermisosModulo`.
 */
class CohortePermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Programación Académica',
            ['icono_mod' => 'Calendar', 'orden_mod' => 4],
            [
                'cohorte.ver'      => 'Ver cohortes',
                'cohorte.crear'    => 'Crear cohortes',
                'cohorte.editar'   => 'Editar cohortes',
                'cohorte.eliminar' => 'Activar/desactivar cohortes',
            ],
            'programacion.cohorte'
        );
    }
}
