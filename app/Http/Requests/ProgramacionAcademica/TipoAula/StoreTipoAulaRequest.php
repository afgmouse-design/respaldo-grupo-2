<?php

namespace App\Http\Requests\ProgramacionAcademica\TipoAula;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request: validación de servidor para crear un Tipo de Aula.
 * La autorización real se aplica en el controlador mediante la Policy.
 */
class StoreTipoAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_tia'      => ['required', 'string', 'min:2', 'max:150', 'unique:tipo_aula,nombre_tia'],
            'codigo_tia'      => ['nullable', 'string', 'max:30', 'unique:tipo_aula,codigo_tia'],
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

