<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos de Oferta Sede-Programa (`sede_programa` / alias `sede_pnf`).
 *
 * Propósito: Registra `sede_programa.*` y alias `sede_pnf.*` en `permiso` vinculados al
 * módulo `Programación Académica` y la vista `programacion.sede_programa`.
 *
 * Historial:
 *  - 2026-11-14: Migrado de `sede_pnf` hacia `sede_programa` preservando alias en policies.
 */
class SedePnfPermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Programación Académica',
            ['icono_mod' => 'Calendar', 'orden_mod' => 4],
            [
                'sede_programa.ver'      => 'Ver ofertas Sede-Programa',
                'sede_programa.crear'    => 'Crear ofertas Sede-Programa',
                'sede_programa.editar'   => 'Editar ofertas Sede-Programa',
                'sede_programa.eliminar' => 'Activar/desactivar ofertas Sede-Programa',
                'sede_pnf.ver'           => 'Ver ofertas Sede PNF (alias)',
                'sede_pnf.crear'         => 'Crear ofertas Sede PNF (alias)',
                'sede_pnf.editar'        => 'Editar ofertas Sede PNF (alias)',
                'sede_pnf.eliminar'      => 'Activar/desactivar ofertas Sede PNF (alias)',
            ],
            'programacion.sede_programa'
        );
    }
}
