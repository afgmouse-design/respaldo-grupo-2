<?php

namespace Database\Seeders\Personas\Maestros;

use App\Models\Personas\Maestros\Perfil;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
$perfiles = [
            [
                'codigo' => 'ESTUDIANTE',
                'nombre' => 'Estudiante',
                'descripcion' => 'Perfil de estudiante universitario. Puede inscribirse en asignaturas, ver notas, solicitar constancias, etc.',
                'status' => true,
            ],
            [
                'codigo' => 'PERSONAL_ACADEMICO',
                'nombre' => 'Personal Académico (Docente)',
                'descripcion' => 'Perfil de docente o investigador. Puede dictar clases, cargar notas, revisar inscripciones, etc.',
                'status' => true,
            ],
            [
                'codigo' => 'PERSONAL_ADMINISTRATIVO',
                'nombre' => 'Personal Administrativo',
                'descripcion' => 'Perfil de autoridades y personal de gestión (Rector, Decanos, Coordinadores, Jefes de Departamento, etc.).',
                'status' => true,
            ],
            [
                'codigo' => 'PERSONAL_OBRERO',
                'nombre' => 'Personal Obrero',
                'descripcion' => 'Perfil de personal de mantenimiento, servicios generales, transporte, etc.',
                'status' => true,
            ],
        ];

        foreach ($perfiles as $perfil) {
            // Evita duplicados si el seeder se ejecuta varias veces
            Perfil::updateOrCreate(
                ['codigo' => $perfil['codigo']],
                [
                    'nombre'      => $perfil['nombre'],
                    'descripcion' => $perfil['descripcion'],
                    'status'      => $perfil['status'],
                ]
            );
        }
    }
}
