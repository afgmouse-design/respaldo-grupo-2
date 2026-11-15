<?php

namespace App\Http\Requests\ProgramacionAcademica\Aula;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para crear un Aula.
 * El código es único dentro de la sede (coincide con unique(sed_id, codigo_aula_aul)).
 */
class StoreAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_aula_aul'      => [
                'required', 'string', 'max:50',
                Rule::unique('aula', 'codigo_aula_aul')
                    ->where(fn ($q) => $q->where('sed_id', $this->input('sed_id'))->whereNull('deleted_at')),
            ],
            'capacidad_maxima_aul' => ['required', 'integer', 'min:1', 'max:100000'],
            'tipo_espacio_aul'     => ['nullable', 'string', 'max:100'],
            'sed_id'               => ['required', 'integer', Rule::exists('sede', 'id_sed')],
            'tia_id'               => ['required', 'integer', Rule::exists('tipo_aula', 'id_tia')],
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
            'tia_id.required'               => 'Debes seleccionar un tipo de aula.',
        ];
    }
}

