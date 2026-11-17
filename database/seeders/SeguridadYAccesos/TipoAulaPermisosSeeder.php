<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos del maestro Tipo de Aula (`tipo_aula`).
 *
 * Propósito: Registra `tipo_aula.ver/crear/editar/eliminar` en `permiso`.
 *
 * Historial:
 *  - 2026-11-14: Migrado a `sogac_mio.sql` vía `SincronizaPermisosModulo`.
 */
class TipoAulaPermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Programación Académica',
            ['icono_mod' => 'Calendar', 'orden_mod' => 4],
            [
                'tipo_aula.ver'      => 'Ver tipos de aula',
                'tipo_aula.crear'    => 'Crear tipos de aula',
                'tipo_aula.editar'   => 'Editar tipos de aula',
                'tipo_aula.eliminar' => 'Activar/desactivar tipos de aula',
            ],
            'programacion.tipo_aula'
        );
    }
}
