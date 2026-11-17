<?php

/**
 * Propósito: Valida la actualización de un Tipo de Aula (`tipo_aula`).
 * Historial: Actualizado para respetar SoftDeletes y soportar route parameter objeto o entero.
 */

namespace App\Http\Requests\ProgramacionAcademica\TipoAula;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $param = $this->route('tipo_aula');
        $id = is_object($param) ? ($param->id_tia ?? $param->getKey()) : $param;

        return [
            'nombre_tia'      => ['required', 'string', 'min:2', 'max:150', Rule::unique('tipo_aula', 'nombre_tia')->ignore($id, 'id_tia')->whereNull('deleted_at')],
            'codigo_tia'      => ['nullable', 'string', 'max:30', Rule::unique('tipo_aula', 'codigo_tia')->ignore($id, 'id_tia')->whereNull('deleted_at')],
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
