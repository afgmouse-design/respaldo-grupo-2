<?php

namespace Database\Seeders\OrganizacionInstitucional;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: CondicionSeeder (Módulo 2 - Organización Institucional).
 *
 * Propósito: Poblar los tipos de condición (ámbitos) y las condiciones funcionales
 * institucionales requeridas para sedes, dependencias y espacios académicos de la UPTP.
 */
class CondicionSeeder extends Seeder
{
    /**
     * Ejecuta el poblado de datos iniciales.
     */
    public function run(): void
    {
        $now = now();

        // 1. Ámbitos / Tipos de Condición
        $tipos = [
            [
                'nombre_tco'      => 'Sede Universitaria',
                'descripcion_tco' => 'Ámbito de categorización funcional para sedes, campus y extensiones de la institución.',
                'status'          => true,
            ],
            [
                'nombre_tco'      => 'Ambiente / Aula',
                'descripcion_tco' => 'Ámbito de categorización de espacios físicos de aprendizaje y laboratorios.',
                'status'          => true,
            ],
            [
                'nombre_tco'      => 'Régimen de Inmueble',
                'descripcion_tco' => 'Condición jurídica o de tenencia de las instalaciones físicas (propiedad, comodato, etc.).',
                'status'          => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_condicion')->updateOrInsert(
                ['nombre_tco' => $tipo['nombre_tco']],
                [
                    'descripcion_tco' => $tipo['descripcion_tco'],
                    'status'          => $tipo['status'],
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]
            );
        }

        $idSede = DB::table('tipo_condicion')->where('nombre_tco', 'Sede Universitaria')->value('id_tco');
        $idAula = DB::table('tipo_condicion')->where('nombre_tco', 'Ambiente / Aula')->value('id_tco');
        $idReg  = DB::table('tipo_condicion')->where('nombre_tco', 'Régimen de Inmueble')->value('id_tco');

        // 2. Condiciones específicas vinculadas
        $condiciones = [
            // Ámbito: Sede Universitaria
            [
                'nombre_cdn'      => 'Sede Principal',
                'descripcion_cdn' => 'Sede rectoral o campus central de operaciones administrativas y académicas.',
                'tco_id'          => $idSede,
                'status'          => true,
            ],
            [
                'nombre_cdn'      => 'Núcleo',
                'descripcion_cdn' => 'Núcleo académico y administrativo dependiente de la universidad en una localidad.',
                'tco_id'          => $idSede,
                'status'          => true,
            ],
            [
                'nombre_cdn'      => 'Extensión',
                'descripcion_cdn' => 'Extensión académica universitaria operativa en un municipio o parroquia.',
                'tco_id'          => $idSede,
                'status'          => true,
            ],
            [
                'nombre_cdn'      => 'Ambiente Municipalizado',
                'descripcion_cdn' => 'Espacio territorial para programas formativos descentralizados.',
                'tco_id'          => $idSede,
                'status'          => true,
            ],

            // Ámbito: Régimen de Inmueble
            [
                'nombre_cdn'      => 'Propia',
                'descripcion_cdn' => 'Instalación o inmueble perteneciente al patrimonio de la institución.',
                'tco_id'          => $idReg,
                'status'          => true,
            ],
            [
                'nombre_cdn'      => 'Comodato',
                'descripcion_cdn' => 'Inmueble cedido por convenio interinstitucional de uso académico sin costo.',
                'tco_id'          => $idReg,
                'status'          => true,
            ],
            [
                'nombre_cdn'      => 'Arrendada',
                'descripcion_cdn' => 'Inmueble en régimen de arrendamiento para actividades institucionales.',
                'tco_id'          => $idReg,
                'status'          => true,
            ],

            // Ámbito: Ambiente / Aula
            [
                'nombre_cdn'      => 'Operativa',
                'descripcion_cdn' => 'Espacio en condiciones óptimas para impartir actividades presenciales.',
                'tco_id'          => $idAula,
                'status'          => true,
            ],
            [
                'nombre_cdn'      => 'En Mantenimiento',
                'descripcion_cdn' => 'Espacio físico temporalmente no disponible por acondicionamiento.',
                'tco_id'          => $idAula,
                'status'          => false,
            ],
        ];

        foreach ($condiciones as $cond) {
            DB::table('condicion')->updateOrInsert(
                [
                    'nombre_cdn' => $cond['nombre_cdn'],
                    'tco_id'     => $cond['tco_id'],
                ],
                [
                    'descripcion_cdn' => $cond['descripcion_cdn'],
                    'status'          => $cond['status'],
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]
            );
        }
    }
}

