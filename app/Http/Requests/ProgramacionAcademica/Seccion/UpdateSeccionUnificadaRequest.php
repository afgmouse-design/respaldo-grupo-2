<?php

namespace App\Http\Requests\ProgramacionAcademica\Seccion;

use App\Http\Requests\ProgramacionAcademica\Seccion\Concerns\NormalizesSeccionRequest;
use App\Http\Requests\ProgramacionAcademica\Seccion\Concerns\ValidatesDetalleCargasSeccion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request: Actualización transaccional unificada de Sección + detalle de Carga Académica (`carga_academica`).
 *
 * Historial:
 *  - 2026-11-16: Creación reutilizando `NormalizesSeccionRequest` y `ValidatesDetalleCargasSeccion`.
 */
class UpdateSeccionUnificadaRequest extends FormRequest
{
    use NormalizesSeccionRequest {
        prepareForValidation as private prepararCabeceraSeccion;
        messages as private mensajesCabeceraSeccion;
    }
    use ValidatesDetalleCargasSeccion;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepararCabeceraSeccion();
        $this->prepararDetalleCargas();
    }

    public function rules(): array
    {
        $param = $this->route('seccion') ?? $this->route('secciones_unificada') ?? $this->route('id');
        $id = is_object($param) ? (int) ($param->id_sec ?? $param->getKey()) : ($param ? (int) $param : null);

        return array_merge($this->reglasSeccion($id), $this->reglasDetalleCargas());
    }

    public function messages(): array
    {
        return array_merge($this->mensajesCabeceraSeccion(), $this->mensajesDetalleCargas());
    }
}
