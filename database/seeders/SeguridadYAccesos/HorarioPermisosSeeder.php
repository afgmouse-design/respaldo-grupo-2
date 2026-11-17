<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos del maestro Horario (`horario`).
 *
 * Propósito: Registra `horario.ver/crear/editar/eliminar` sobre `Programación Académica`.
 *
 * Historial:
 *  - 2026-11-14: Migrado a `sogac_mio.sql` vía `SincronizaPermisosModulo`.
 */
class HorarioPermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Programación Académica',
            ['icono_mod' => 'Calendar', 'orden_mod' => 4],
            [
                'horario.ver'      => 'Ver horarios académicos',
                'horario.crear'    => 'Crear horarios académicos',
                'horario.editar'   => 'Editar horarios académicos',
                'horario.eliminar' => 'Activar/desactivar horarios académicos',
            ],
            'programacion.horario'
        );
    }
}
