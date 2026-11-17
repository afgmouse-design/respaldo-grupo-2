<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos del maestro Cohorte por Sede (`cohorte_sede`).
 *
 * Propósito: Registra `cohorte_sede.ver/crear/editar/eliminar` sobre `modulo/vista/permiso`.
 *
 * Historial:
 *  - 2026-11-14: Migrado a `sogac_mio.sql` vía `SincronizaPermisosModulo`.
 */
class CohorteSedePermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Programación Académica',
            ['icono_mod' => 'Calendar', 'orden_mod' => 4],
            [
                'cohorte_sede.ver'      => 'Ver cohortes por sede',
                'cohorte_sede.crear'    => 'Crear cohortes por sede',
                'cohorte_sede.editar'   => 'Editar cohortes por sede',
                'cohorte_sede.eliminar' => 'Activar/desactivar cohortes por sede',
            ],
            'programacion.cohorte_sede'
        );
    }
}
