<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos del módulo Sección - Unidad - Docente (`carga_academica`).
 *
 * Propósito: Registra `carga_academica.ver/crear/editar/eliminar` vinculados a la vista
 * `programacion.carga_academica` dentro del módulo Programación Académica.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial del seeder de permisos para SUD (`carga_academica`).
 */
class CargaAcademicaPermisosSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Programación Académica',
            ['icono_mod' => 'Calendar', 'orden_mod' => 4],
            [
                'carga_academica.ver'      => 'Ver asignaciones sección - unidad - docente',
                'carga_academica.crear'    => 'Crear asignaciones sección - unidad - docente',
                'carga_academica.editar'   => 'Editar asignaciones sección - unidad - docente',
                'carga_academica.eliminar' => 'Activar/desactivar o eliminar asignaciones sección - unidad - docente',
            ],
            'programacion.carga_academica'
        );
    }
}
