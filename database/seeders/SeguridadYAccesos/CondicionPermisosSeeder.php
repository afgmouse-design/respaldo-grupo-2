<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos del maestro Condición de Sede (`condicion_sede`).
 *
 * Propósito: Registra `condicion_sede.*` y alias heredados `condicion.*` vinculados a
 * `Organización Institucional` y `organizacion.condicion_sede`.
 *
 * Historial:
 *  - 2026-11-14: Migrado desde el esquema legacy de `permiso` hacia `sogac_mio.sql`.
 */
class CondicionPermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Organización Institucional',
            ['icono_mod' => 'Landmark', 'orden_mod' => 2],
            [
                'condicion_sede.ver'      => 'Ver condiciones de sede',
                'condicion_sede.crear'    => 'Crear condiciones de sede',
                'condicion_sede.editar'   => 'Editar condiciones de sede',
                'condicion_sede.eliminar' => 'Activar/desactivar condiciones de sede',
                'condicion.ver'           => 'Ver condiciones (alias)',
                'condicion.crear'         => 'Crear condiciones (alias)',
                'condicion.editar'        => 'Editar condiciones (alias)',
                'condicion.eliminar'      => 'Activar/desactivar condiciones (alias)',
            ],
            'organizacion.condicion_sede'
        );
    }
}
