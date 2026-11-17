<?php

/**
 * Propósito: Valida la creación de una Sección (`seccion`).
 * Historial: Refactorizado sobre `NormalizesSeccionRequest` para alinear FKs oficiales de `sogac_mio.sql`.
 */

namespace App\Http\Requests\ProgramacionAcademica\Seccion;

use App\Http\Requests\ProgramacionAcademica\Seccion\Concerns\NormalizesSeccionRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreSeccionRequest extends FormRequest
{
    use NormalizesSeccionRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->reglasSeccion();
    }
}
