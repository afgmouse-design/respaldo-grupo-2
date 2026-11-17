<?php

namespace Database\Seeders\Personas\Maestros;

use App\Models\Personas\Perfil;
use Illuminate\Database\Seeder;

/**
 * Seeder: Catálogo de Perfiles Institucionales (`perfil`).
 *
 * Propósito: Poblar los perfiles base de `sogac_mio.sql` (`ESTUDIANTE`, `DOCENTE`,
 * `ADMINISTRATIVO`, `AUTORIDAD`, `OBRERO`) utilizando las columnas oficiales
 * `codigo_prf`, `nombre_prf`, `descripcion_prf` y `status_id = 1`.
 *
 * Historial:
 *  - 2026-11-14: Corregido namespace del modelo (`App\Models\Personas\Perfil`) y migradas las
 *    columnas `codigo/nombre/status` a `codigo_prf/nombre_prf/descripcion_prf/status_id`.
 */
class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        $perfiles = [
            [
                'codigo_prf'      => 'ESTUDIANTE',
                'nombre_prf'      => 'Estudiante',
                'descripcion_prf' => 'Perfil de estudiante universitario habilitado para inscripciones, horarios y consultas académicas.',
            ],
            [
                'codigo_prf'      => 'DOCENTE',
                'nombre_prf'      => 'Personal Académico (Docente)',
                'descripcion_prf' => 'Perfil de docente o investigador asignable en carga_academica y calificaciones.',
            ],
            [
                'codigo_prf'      => 'ADMINISTRATIVO',
                'nombre_prf'      => 'Personal Administrativo',
                'descripcion_prf' => 'Perfil de personal administrativo y control de estudio.',
            ],
            [
                'codigo_prf'      => 'AUTORIDAD',
                'nombre_prf'      => 'Autoridad Universitaria',
                'descripcion_prf' => 'Perfil de rector, vicerrectores, directores de sede y coordinadores académicos.',
            ],
            [
                'codigo_prf'      => 'OBRERO',
                'nombre_prf'      => 'Personal Obrero',
                'descripcion_prf' => 'Perfil de personal de mantenimiento y servicios generales.',
            ],
        ];

        foreach ($perfiles as $perfil) {
            Perfil::withTrashed()->updateOrCreate(
                ['codigo_prf' => $perfil['codigo_prf']],
                [
                    'nombre_prf'      => $perfil['nombre_prf'],
                    'descripcion_prf' => $perfil['descripcion_prf'],
                    'status_id'       => 1,
                    'deleted_at'      => null,
                ]
            );
        }
    }
}
