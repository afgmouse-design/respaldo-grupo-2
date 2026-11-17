<?php

namespace Database\Seeders\OrganizacionInstitucional;

use App\Models\OrganizacionInstitucional\Maestros\CondicionSede;
use App\Models\OrganizacionInstitucional\Maestros\DependenciaInstitucion;
use App\Models\OrganizacionInstitucional\Maestros\TipoCondicion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: Catálogos de Organización Institucional (`condicion_sede`, `tipo_condicion`, `dependencia_institucion`).
 *
 * Propósito: Poblar de forma idempotente las condiciones operativas de sede/universidad,
 * los tipos de condición institucional y las dependencias administrativas oficiales en `sogac_mio.sql`.
 *
 * Historial:
 *  - 2026-11-14: Reemplazada la tabla legacy `condicion` por `condicion_sede(id_cse)`, `tipo_condicion`
 *    (`tco_codigo`/`tco_nombre`/`tco_estatus`) y agregado catálogo `dependencia_institucion(id_dpi)`.
 */
class CondicionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (['Pública', 'Privada', 'Nacional / MPTU'] as $nombreDpi) {
                DependenciaInstitucion::withTrashed()->updateOrCreate(
                    ['nombre_dpi' => $nombreDpi],
                    ['status_id' => 1, 'deleted_at' => null]
                );
            }

            $tiposCondicion = [
                [
                    'tco_codigo'      => 'SEDE',
                    'tco_nombre'      => 'Sede Universitaria',
                    'tco_descripcion' => 'Ámbito de categorización funcional para sedes, campus y extensiones.',
                ],
                [
                    'tco_codigo'      => 'AULA',
                    'tco_nombre'      => 'Ambiente / Aula',
                    'tco_descripcion' => 'Ámbito de categorización de espacios físicos de aprendizaje y laboratorios.',
                ],
                [
                    'tco_codigo'      => 'INMUEBLE',
                    'tco_nombre'      => 'Régimen de Inmueble',
                    'tco_descripcion' => 'Condición jurídica o de tenencia de las instalaciones físicas.',
                ],
            ];

            foreach ($tiposCondicion as $tipo) {
                TipoCondicion::withTrashed()->updateOrCreate(
                    ['tco_codigo' => $tipo['tco_codigo']],
                    [
                        'tco_nombre'          => $tipo['tco_nombre'],
                        'tco_descripcion'     => $tipo['tco_descripcion'],
                        'tco_cod_universidad' => 1,
                        'tco_estatus'         => 'A',
                        'deleted_at'          => null,
                    ]
                );
            }

            $condicionesSede = [
                [
                    'nombre_cse'      => 'Sede Principal',
                    'descripcion_cse' => 'Sede rectoral o campus central de operaciones administrativas y académicas.',
                ],
                [
                    'nombre_cse'      => 'Núcleo',
                    'descripcion_cse' => 'Núcleo académico y administrativo dependiente de la universidad en una localidad.',
                ],
                [
                    'nombre_cse'      => 'Extensión',
                    'descripcion_cse' => 'Extensión académica universitaria operativa en un municipio o parroquia.',
                ],
                [
                    'nombre_cse'      => 'Ambiente Municipalizado',
                    'descripcion_cse' => 'Espacio territorial para programas formativos descentralizados.',
                ],
                [
                    'nombre_cse'      => 'Propia',
                    'descripcion_cse' => 'Instalación perteneciente al patrimonio inmobiliario de la universidad.',
                ],
                [
                    'nombre_cse'      => 'Comodato',
                    'descripcion_cse' => 'Inmueble cedido por convenio interinstitucional de uso académico.',
                ],
                [
                    'nombre_cse'      => 'Arrendada',
                    'descripcion_cse' => 'Inmueble en régimen de arrendamiento institucional.',
                ],
            ];

            foreach ($condicionesSede as $cond) {
                CondicionSede::withTrashed()->updateOrCreate(
                    ['nombre_cse' => $cond['nombre_cse']],
                    [
                        'descripcion_cse' => $cond['descripcion_cse'],
                        'status_id'       => 1,
                        'deleted_at'      => null,
                    ]
                );
            }
        });
    }
}
