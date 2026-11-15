<?php

namespace App\Http\Requests\ProgramacionAcademica\TipoAula;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para actualizar un Tipo de Aula.
 * Las reglas unique ignoran el registro actual (->ignore($id, 'id_tia')).
 */
class UpdateTipoAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('tipo_aula');

        return [
            'nombre_tia'      => ['required', 'string', 'min:2', 'max:150', Rule::unique('tipo_aula', 'nombre_tia')->ignore($id, 'id_tia')],
            'codigo_tia'      => ['nullable', 'string', 'max:30', Rule::unique('tipo_aula', 'codigo_tia')->ignore($id, 'id_tia')],
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

