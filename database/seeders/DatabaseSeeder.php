<?php

namespace Database\Seeders;

use Database\Seeders\EstructuraAcademica\CatalogosAcademicosSeeder;
use Database\Seeders\EstructuraGeografica\GeografiaSeeder;
use Database\Seeders\OrganizacionInstitucional\CondicionSeeder;
use Database\Seeders\Personas\DocenteSeeder;
use Database\Seeders\Personas\Maestros\PerfilSeeder;
use Database\Seeders\SeguridadYAccesos\AulaPermisosSeeder;
use Database\Seeders\SeguridadYAccesos\CohortePermisosSeeder;
use Database\Seeders\SeguridadYAccesos\CohorteSedePermisosSeeder;
use Database\Seeders\SeguridadYAccesos\CondicionPermisosSeeder;
use Database\Seeders\SeguridadYAccesos\CargaAcademicaPermisosSeeder;
use Database\Seeders\SeguridadYAccesos\HorarioPermisosSeeder;
use Database\Seeders\SeguridadYAccesos\NavigationSeeder;
use Database\Seeders\SeguridadYAccesos\PermisosBaseSeeder;
use Database\Seeders\SeguridadYAccesos\SeccionPermisosSeeder;
use Database\Seeders\SeguridadYAccesos\SedePnfPermisosSeeder;
use Database\Seeders\SeguridadYAccesos\StatusSeeder;
use Database\Seeders\SeguridadYAccesos\TipoAulaPermisosSeeder;
use Database\Seeders\SeguridadYAccesos\Usuario\UserSeeder;
use Illuminate\Database\Seeder;

/**
 * Seeder Principal del Sistema SOGAC.
 *
 * Propósito: Orquestar en orden referencial la carga idempotente del catálogo `status`,
 * datos geográficos, organización institucional, catálogos académicos, perfiles, menú de navegación,
 * permisos RBAC y usuario Super Administrador inicial.
 *
 * Historial:
 *  - 2026-11-14: Actualizado para ejecutar `StatusSeeder`, catálogos, `NavigationSeeder`,
 *    seeders de permisos sobre `sogac_mio.sql` y `UserSeeder`.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StatusSeeder::class,
            GeografiaSeeder::class,
            CondicionSeeder::class,
            CatalogosAcademicosSeeder::class,
            PerfilSeeder::class,
            DocenteSeeder::class,
            NavigationSeeder::class,
            AulaPermisosSeeder::class,
            CohortePermisosSeeder::class,
            CohorteSedePermisosSeeder::class,
            CondicionPermisosSeeder::class,
            CargaAcademicaPermisosSeeder::class,
            HorarioPermisosSeeder::class,
            SeccionPermisosSeeder::class,
            SedePnfPermisosSeeder::class,
            TipoAulaPermisosSeeder::class,
            PermisosBaseSeeder::class,
            UserSeeder::class,
        ]);
    }
}
