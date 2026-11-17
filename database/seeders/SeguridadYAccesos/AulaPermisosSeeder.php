<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos del maestro Aula (`aula`).
 *
 * Propósito: Registra de forma idempotente `aula.ver/crear/editar/eliminar` en `permiso`
 * vinculados al módulo `Programación Académica` y la vista `programacion.aula`.
 *
 * Historial:
 *  - 2026-11-14: Migrado sobre `modulo(id_mod)`, `vista(id_vis)` y `permiso(clave_pmi)` vía `SincronizaPermisosModulo`.
 *
 * Ejecución: php artisan db:seed --class="Database\Seeders\SeguridadYAccesos\AulaPermisosSeeder"
 */
class AulaPermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Programación Académica',
            ['icono_mod' => 'Calendar', 'orden_mod' => 4],
            [
                'aula.ver'      => 'Ver aulas',
                'aula.crear'    => 'Crear aulas',
                'aula.editar'   => 'Editar aulas',
                'aula.eliminar' => 'Activar/desactivar aulas',
            ],
            'programacion.aula'
        );
    }
}
