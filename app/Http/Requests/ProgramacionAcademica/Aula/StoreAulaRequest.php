<?php

/**
 * Propósito: Valida la creación de un Aula física (`aula`).
 * Historial: Actualizado para verificar SoftDeletes en `aula`, `sede` y `tipo_aula` y respetar `max:255`.
 */

namespace App\Http\Requests\ProgramacionAcademica\Aula;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_aula_aul' => [
                'required',
                'string',
                'max:255',
                Rule::unique('aula', 'codigo_aula_aul')
                    ->where(fn ($q) => $q->where('sed_id', $this->input('sed_id'))->whereNull('deleted_at')),
            ],
            'capacidad_maxima_aul' => ['required', 'integer', 'min:1', 'max:100000'],
            'tipo_espacio_aul'     => ['nullable', 'string', 'max:255'],
            'sed_id'               => ['required', 'integer', Rule::exists('sede', 'id_sed')->whereNull('deleted_at')],
            'tia_id'               => ['required', 'integer', Rule::exists('tipo_aula', 'id_tia')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_aula_aul.required'      => 'El código del aula es obligatorio.',
            'codigo_aula_aul.unique'        => 'Ya existe un aula con ese código en la sede seleccionada.',
            'capacidad_maxima_aul.required' => 'La capacidad máxima es obligatoria.',
            'capacidad_maxima_aul.min'      => 'La capacidad debe ser al menos 1.',
            'sed_id.required'               => 'Debes seleccionar una sede.',
            'sed_id.exists'                 => 'La sede seleccionada no existe.',
            'tia_id.required'               => 'Debes seleccionar un tipo de aula.',
            'tia_id.exists'                 => 'El tipo de aula seleccionado no existe.',
        ];
    }
}
