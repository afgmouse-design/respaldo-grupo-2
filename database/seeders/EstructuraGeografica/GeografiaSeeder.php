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
 * sus respectivas parroquias y localidades representativas sobre `status_id = 1`.
 *
 * Historial:
 *  - 2026-11-14: Reemplazado `status => true` por `status_id => 1` e incluido `iso_esd = 'VE-P'`
 *    conforme a `sogac_mio.sql`.
 */
class GeografiaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $pais = Pais::withTrashed()->updateOrCreate(
                ['nombre_pai' => 'Venezuela'],
                ['status_id' => 1, 'deleted_at' => null]
            );

            $estadoPortuguesa = Estado::withTrashed()->updateOrCreate(
                [
                    'pai_id'     => $pais->id_pai,
                    'nombre_esd' => 'Portuguesa',
                ],
                [
                    'iso_esd'    => 'VE-P',
                    'status_id'  => 1,
                    'deleted_at' => null,
                ]
            );

            $geografiaData = [
                [
                    'municipio'  => 'Páez',
                    'parroquias' => [
                        [
                            'parroquia'   => 'Acarigua',
                            'localidades' => [
                                'Casco Central',
                                'Barrio Bella Vista',
                                'Urbanización Los Cortijos',
                                'Sector Goajira',
                                'Reja De Guanare',
                                'Urbanización Veinticuatro De Julio',
                                'Zona Industrial De Acarigua',
                            ],
                        ],
                        [
                            'parroquia'   => 'Payara',
                            'localidades' => ['Payara Centro', 'Caserío San José', 'Sector Las Matas'],
                        ],
                        [
                            'parroquia'   => 'Pimpinela',
                            'localidades' => ['Pimpinela Centro', 'Caserío Las Majaguas', 'Sector El Mamón'],
                        ],
                        [
                            'parroquia'   => 'Ramón Peraza',
                            'localidades' => ['Mijagüito', 'Sector Sabanetica', 'Caserío La Tapa'],
                        ],
                    ],
                ],
                [
                    'municipio'  => 'Araure',
                    'parroquias' => [
                        [
                            'parroquia'   => 'Araure',
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
                            'parroquia'   => 'Río Acarigua',
                            'localidades' => ['Río Acarigua Centro', 'Caserío Guache', 'Sector El Cambur'],
                        ],
                    ],
                ],
            ];

            foreach ($geografiaData as $munData) {
                $municipio = Municipio::withTrashed()->updateOrCreate(
                    [
                        'esd_id'     => $estadoPortuguesa->id_esd,
                        'nombre_mun' => $munData['municipio'],
                    ],
                    ['status_id' => 1, 'deleted_at' => null]
                );

                foreach ($munData['parroquias'] as $parrData) {
                    $parroquia = Parroquia::withTrashed()->updateOrCreate(
                        [
                            'mun_id'     => $municipio->id_mun,
                            'nombre_par' => $parrData['parroquia'],
                        ],
                        ['status_id' => 1, 'deleted_at' => null]
                    );

                    foreach ($parrData['localidades'] as $nombreLocalidad) {
                        $nombreNormalizado = (new Localidad(['nombre_loc' => $nombreLocalidad]))->nombre_loc;

                        Localidad::withTrashed()->updateOrCreate(
                            [
                                'par_id'     => $parroquia->id_par,
                                'nombre_loc' => $nombreNormalizado,
                            ],
                            ['status_id' => 1, 'deleted_at' => null]
                        );
                    }
                }
            }
        });

        $this->command?->info('✓ Estructura Geográfica poblada (Venezuela -> Portuguesa -> Páez/Araure).');
    }
}
