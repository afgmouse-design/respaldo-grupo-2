<?php

namespace App\Http\Requests\ProgramacionAcademica\CargaAcademica;

use App\Http\Requests\ProgramacionAcademica\CargaAcademica\Concerns\ValidatesCargaAcademicaRequest;
use App\Models\ProgramacionAcademica\CargaAcademica;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form Request: Validación de actualización de asignación Sección - Unidad - Docente (`carga_academica`).
 *
 * Propósito: Valida la modificación de una asignación existente excluyendo su propio `id_caa`
 * en los controles de unicidad `(sec_id, unc_id)` y sumatoria horaria del docente en el lapso.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial para el CRUD de Sección - Unidad - Docente.
 */
class UpdateCargaAcademicaRequest extends FormRequest
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
        $param = $this->route('seccion_unidad_docente') ?? $this->route('carga_academica') ?? $this->route('id');
        $id = is_object($param) ? (int) ($param->id_caa ?? $param->getKey()) : (int) $param;

        $registro = $id > 0 ? CargaAcademica::find($id) : null;
        $statusObjetivo = $registro ? (int) $registro->status_id : CargaAcademica::STATUS_ACTIVO;

        $this->aplicarValidacionDominio($validator, $id > 0 ? $id : null, $statusObjetivo);
    }
}
