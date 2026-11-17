<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Academico\TipoLapso as BaseTipoLapso;

/**
 * Alias de compatibilidad para el modelo TipoLapso dentro de ProgramacionAcademica\Maestros.
 *
 * Propósito: Reutilizar el modelo canónico `App\Models\Academico\TipoLapso` sin duplicar lógica.
 *
 * Historial:
 *  - 2026-11-14: Reemplazada la implementación duplicada para heredar del modelo oficial `Academico\TipoLapso`.
 */
class TipoLapso extends BaseTipoLapso
{
}
