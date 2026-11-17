<?php

namespace App\Http\Requests\ProgramacionAcademica\CargaAcademica;

use App\Http\Requests\ProgramacionAcademica\CargaAcademica\Concerns\ValidatesCargaAcademicaRequest;
use App\Models\ProgramacionAcademica\CargaAcademica;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form Request: Validación de creación de asignación Sección - Unidad - Docente (`carga_academica`).
 *
 * Propósito: Garantiza la integridad de campos requeridos, unicidad `(sec_id, unc_id)`,
 * docente activo y cumplimiento de tope horario de la dedicación docente en el lapso.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial para el CRUD de Sección - Unidad - Docente.
 */
class StoreCargaAcademicaRequest extends FormRequest
{
    use ValidatesCargaAcademicaRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->reglasCargaAcademica();
    }

    public function withValidator(Validator $validator): void
    {
        $this->aplicarValidacionDominio($validator, null, CargaAcademica::STATUS_ACTIVO);
    }
}
