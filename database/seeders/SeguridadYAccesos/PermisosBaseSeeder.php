<?php

namespace Database\Seeders\SeguridadYAccesos;

use Database\Seeders\SeguridadYAccesos\Concerns\SincronizaPermisosModulo;
use Illuminate\Database\Seeder;

/**
 * Seeder: Permisos Base de Módulos Transversales.
 *
 * Propósito: Registra los permisos requeridos por las Policies de Organización Institucional
 * (`universidad`, `sede`, `organizacion`), Estructura Académica (`programa`, `pnf`,
 * `escala_medicion`, `trayecto`) y Personas (`estudiante`, `registro_personas`, `perfil`),
 * asignándolos al rol `Super Administrador`.
 *
 * Historial:
 *  - 2026-11-14: Creación inicial alineada a `permiso(clave_pmi, mod_id, vis_id, status_id)`.
 */
class PermisosBaseSeeder extends Seeder
{
    use SincronizaPermisosModulo;

    public function run(): void
    {
        $this->sincronizarPermisosDeModulo(
            'Organización Institucional',
            ['icono_mod' => 'Landmark', 'orden_mod' => 2],
            [
                'universidad.ver'      => ['nombre' => 'Ver universidades', 'clave_vista' => 'organizacion.universidad'],
                'universidad.crear'    => ['nombre' => 'Crear universidades', 'clave_vista' => 'organizacion.universidad'],
                'universidad.editar'   => ['nombre' => 'Editar universidades', 'clave_vista' => 'organizacion.universidad'],
                'universidad.eliminar' => ['nombre' => 'Activar/desactivar universidades', 'clave_vista' => 'organizacion.universidad'],
                'sede.ver'             => ['nombre' => 'Ver sedes', 'clave_vista' => 'organizacion.sede'],
                'sede.crear'           => ['nombre' => 'Crear sedes', 'clave_vista' => 'organizacion.sede'],
                'sede.editar'          => ['nombre' => 'Editar sedes', 'clave_vista' => 'organizacion.sede'],
                'sede.eliminar'        => ['nombre' => 'Activar/desactivar sedes', 'clave_vista' => 'organizacion.sede'],
                'organizacion.ver'     => 'Ver organización institucional',
                'organizacion.crear'   => 'Crear organización institucional',
                'organizacion.editar'  => 'Editar organización institucional',
                'organizacion.eliminar' => 'Activar/desactivar organización institucional',
            ]
        );

        $this->sincronizarPermisosDeModulo(
            'Estructura Académica',
            ['icono_mod' => 'BookOpen', 'orden_mod' => 3],
            [
                'programa.ver'             => ['nombre' => 'Ver programas / PNF', 'clave_vista' => 'academico.programa'],
                'programa.crear'           => ['nombre' => 'Crear programas / PNF', 'clave_vista' => 'academico.programa'],
                'programa.editar'          => ['nombre' => 'Editar programas / PNF', 'clave_vista' => 'academico.programa'],
                'programa.eliminar'        => ['nombre' => 'Activar/desactivar programas / PNF', 'clave_vista' => 'academico.programa'],
                'pnf.ver'                  => ['nombre' => 'Ver PNF (alias)', 'clave_vista' => 'academico.programa'],
                'pnf.crear'                => ['nombre' => 'Crear PNF (alias)', 'clave_vista' => 'academico.programa'],
                'pnf.editar'               => ['nombre' => 'Editar PNF (alias)', 'clave_vista' => 'academico.programa'],
                'pnf.eliminar'             => ['nombre' => 'Activar/desactivar PNF (alias)', 'clave_vista' => 'academico.programa'],
                'escala_medicion.ver'      => ['nombre' => 'Ver escalas de medición', 'clave_vista' => 'academico.escala_medicion'],
                'escala_medicion.crear'    => ['nombre' => 'Crear escalas de medición', 'clave_vista' => 'academico.escala_medicion'],
                'escala_medicion.editar'   => ['nombre' => 'Editar escalas de medición', 'clave_vista' => 'academico.escala_medicion'],
                'escala_medicion.eliminar' => ['nombre' => 'Activar/desactivar escalas de medición', 'clave_vista' => 'academico.escala_medicion'],
                'trayecto.ver'             => ['nombre' => 'Ver trayectos', 'clave_vista' => 'academico.trayecto'],
                'trayecto.crear'           => ['nombre' => 'Crear trayectos', 'clave_vista' => 'academico.trayecto'],
                'trayecto.editar'          => ['nombre' => 'Editar trayectos', 'clave_vista' => 'academico.trayecto'],
                'trayecto.eliminar'        => ['nombre' => 'Activar/desactivar trayectos', 'clave_vista' => 'academico.trayecto'],
            ]
        );

        $this->sincronizarPermisosDeModulo(
            'Seguridad y Personas',
            ['es_sistema_mod' => true, 'icono_mod' => 'Shield', 'orden_mod' => 5],
            [
                'perfil.ver'                => 'Ver catálogo de perfiles',
                'perfil.crear'              => 'Crear perfiles',
                'perfil.editar'             => 'Editar perfiles',
                'perfil.eliminar'           => 'Activar/desactivar perfiles',
                'estudiante.ver'            => 'Ver estudiantes',
                'estudiante.crear'          => 'Registrar estudiantes',
                'estudiante.editar'         => 'Editar estudiantes',
                'estudiante.eliminar'       => 'Activar/desactivar estudiantes',
                'registro_personas.ver'     => 'Ver registro general de personas',
                'registro_personas.crear'   => 'Registrar personas',
                'registro_personas.editar'  => 'Editar personas',
                'registro_personas.eliminar' => 'Activar/desactivar personas',
            ]
        );
    }
}
