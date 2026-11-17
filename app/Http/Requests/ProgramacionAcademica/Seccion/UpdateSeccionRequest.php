<?php

/**
 * Propósito: Valida la actualización de una Sección (`seccion`).
 * Historial: Refactorizado sobre `NormalizesSeccionRequest` para alinear FKs oficiales e ignorar `id_sec`.
 */

namespace App\Http\Requests\ProgramacionAcademica\Seccion;

use App\Http\Requests\ProgramacionAcademica\Seccion\Concerns\NormalizesSeccionRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSeccionRequest extends FormRequest
{
    use NormalizesSeccionRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $param = $this->route('seccion');
        $id = is_object($param) ? ($param->id_sec ?? $param->getKey()) : (int) $param;

        return $this->reglasSeccion($id);
    }
}
