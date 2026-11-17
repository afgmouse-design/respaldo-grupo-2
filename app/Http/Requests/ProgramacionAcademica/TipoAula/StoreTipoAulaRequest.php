<?php

/**
 * Propósito: Valida la creación de un Tipo de Aula (`tipo_aula`).
 * Historial: Actualizado para respetar SoftDeletes en las reglas de unicidad.
 */

namespace App\Http\Requests\ProgramacionAcademica\TipoAula;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTipoAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_tia'      => ['required', 'string', 'min:2', 'max:150', Rule::unique('tipo_aula', 'nombre_tia')->whereNull('deleted_at')],
            'codigo_tia'      => ['nullable', 'string', 'max:30', Rule::unique('tipo_aula', 'codigo_tia')->whereNull('deleted_at')],
            'descripcion_tia' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_tia.required' => 'El nombre es obligatorio.',
            'nombre_tia.unique'   => 'Ya existe un tipo de aula con ese nombre.',
            'codigo_tia.unique'   => 'Ya existe un tipo de aula con ese código.',
        ];
    }
}
