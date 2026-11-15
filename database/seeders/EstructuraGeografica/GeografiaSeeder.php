<?php

namespace Database\Seeders\EstructuraGeografica;

use App\Models\EstructuraGeografica\Maestros\Estado;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\EstructuraGeografica\Maestros\Municipio;
use App\Models\EstructuraGeografica\Maestros\Pais;
use App\Models\EstructuraGeografica\Maestros\Parroquia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: Módulo 1 - Estructura Geográfica (SOGAC - UPTP).
 *
 * Propósito: Población inicial y jerárquica de la estructura político-territorial
 * con datos reales de Venezuela, Estado Portuguesa (Municipios Páez y Araure),
 * sus respectivas parroquias y localidades representativas.
 *
 * Cumplimiento técnico:
 *  - Uso de modelos Eloquent oficiales.
 *  - Control de idempotencia y prevención de duplicados con firstOrCreate().
 *  - Preservación de claves primarias y foráneas (id_pai, id_esd, id_mun, id_par, id_loc).
 *  - Estatus lógico por defecto activo (status = true).
 */
class GeografiaSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de datos geográficos.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // ==========================================
            // 1. NIVEL PAÍS
            // ==========================================
            $pais = Pais::firstOrCreate(
                ['nombre_pai' => 'Venezuela'],
                ['status' => true]
            );

            // ==========================================
            // 2. NIVEL ESTADO
            // ==========================================
            $estadoPortuguesa = Estado::firstOrCreate(
                [
                    'pai_id'     => $pais->id_pai,
                    'nombre_esd' => 'Portuguesa',
                ],
                ['status' => true]
            );

            // Estructura jerárquica de Municipios, Parroquias y Localidades
            $geografiaData = [
                // ------------------------------------------
                // MUNICIPIO PÁEZ
                // ------------------------------------------
                [
                    'municipio' => 'Páez',
                    'parroquias' => [
                        [
                            'parroquia' => 'Acarigua',
                            'localidades' => [
                                'Casco Central',
                                'Barrio Bella Vista',
                                'Urbanización Los Cortijos',
                                'Sector Goajira',
                                'Reja de Guanare',
                                'Urbanización 24 de Julio',
                                'Zona Industrial de Acarigua',
                            ],
                        ],
                        [
                            'parroquia' => 'Payara',
                            'localidades' => [
                                'Payara Centro',
                                'Caserío San José',
                                'Sector Las Matas',
                            ],
                        ],
                        [
                            'parroquia' => 'Pimpinela',
                            'localidades' => [
                                'Pimpinela Centro',
                                'Caserío Las Majaguas',
                                'Sector El Mamón',
                            ],
                        ],
                        [
                            'parroquia' => 'Ramón Peraza',
                            'localidades' => [
                                'Mijagüito',
                                'Sector Sabanetica',
                                'Caserío La Tapa',
                            ],
                        ],
                    ],
                ],

                // ------------------------------------------
                // MUNICIPIO ARAURE
                // ------------------------------------------
                [
                    'municipio' => 'Araure',
                    'parroquias' => [
                        [
                            'parroquia' => 'Araure',
                            'localidades' => [
                                'Casco Colonial de Araure',
                                'Urbanización Villa Araure',
                                'Urbanización Baraure',
                                'Urbanización La Tricentenaria',
                                'Sector San Vicente',
                                'Urbanización Las Mesetas',
                                'Sector Quebrada de Araure',
                            ],
                        ],
                        [
                            'parroquia' => 'Río Acarigua',
                            'localidades' => [
                                'Río Acarigua Centro',
                                'Caserío Guache',
                                'Sector El Cambur',
                            ],
                        ],
                    ],
                ],
            ];

            // ==========================================
            // 3. PROCESAMIENTO JERÁRQUICO
            // ==========================================
            foreach ($geografiaData as $munData) {
                // 3.1 MUNICIPIO
                $municipio = Municipio::firstOrCreate(
                    [
                        'esd_id'     => $estadoPortuguesa->id_esd,
                        'nombre_mun' => $munData['municipio'],
                    ],
                    ['status' => true]
                );

                foreach ($munData['parroquias'] as $parrData) {
                    // 3.2 PARROQUIA
                    $parroquia = Parroquia::firstOrCreate(
                        [
                            'mun_id'     => $municipio->id_mun,
                            'nombre_par' => $parrData['parroquia'],
                        ],
                        ['status' => true]
                    );

                    foreach ($parrData['localidades'] as $nombreLocalidad) {
                        // 3.3 LOCALIDAD
                        Localidad::firstOrCreate(
                            [
                                'par_id'     => $parroquia->id_par,
                                'nombre_loc' => $nombreLocalidad,
                            ],
                            ['status' => true]
                        );
                    }
                }
            }
        });

        $this->command?->info('✓ Módulo de Estructura Geográfica poblado exitosamente (Venezuela -> Portuguesa -> Páez/Araure).');
    }
}

