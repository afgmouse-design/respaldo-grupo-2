<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos del maestro Sección (`seccion`).
 *
 * Propósito: Registra `seccion.ver/crear/editar/eliminar` sobre `Programación Académica`.
 *
 * Historial:
 *  - 2026-11-14: Migrado a `sogac_mio.sql` vía `SincronizaPermisosModulo`.
 */
class SeccionPermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Programación Académica',
            ['icono_mod' => 'Calendar', 'orden_mod' => 4],
            [
                'seccion.ver'      => 'Ver secciones',
                'seccion.crear'    => 'Crear secciones',
                'seccion.editar'   => 'Editar secciones',
                'seccion.eliminar' => 'Activar/desactivar secciones',
            ],
            'programacion.seccion'
        );
    }
}
