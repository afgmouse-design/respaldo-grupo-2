<?php

namespace App\Models\ProgramacionAcademica;

use App\Models\ProgramacionAcademica\Maestros\Horario as MaestroHorario;

/**
 * Modelo Horario (Alias para retrocompatibilidad).
 *
 * Actualización 2026-11-01 (Estandarización SOGAC Grupo 3 Ángel Gonzales):
 * Se delega la lógica central al maestro oficial en namespace `Maestros\Horario`.
 */
class Horario extends MaestroHorario
{
}


