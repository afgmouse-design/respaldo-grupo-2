<?php

namespace App\Http\Requests\ProgramacionAcademica\Horario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación de servidor para actualizar un Horario.
 * Grupo 3: Ángel Gonzales - Oferta y Horarios.
 */
class UpdateHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dia_semana_hor'  => ['required', 'string', Rule::in(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'])],
            'hora_inicio_hor' => ['required', 'string'],
            'hora_fin_hor'    => ['required', 'string'],
            'caa_id'          => ['required', 'integer', Rule::exists('carga_academica', 'id_caa')],
            'aul_id'          => ['required', 'integer', Rule::exists('aula', 'id_aul')],
        ];
    }

    public function messages(): array
    {
        return [
            'dia_semana_hor.required'  => 'El día de la semana es obligatorio.',
            'dia_semana_hor.in'        => 'El día seleccionado no es válido.',
            'hora_inicio_hor.required' => 'La hora de inicio es obligatoria.',
            'hora_fin_hor.required'    => 'La hora de fin es obligatoria.',
            'caa_id.required'          => 'Debe seleccionar una carga académica.',
            'caa_id.exists'            => 'La carga académica seleccionada no existe.',
            'aul_id.required'          => 'Debe seleccionar un aula física.',
            'aul_id.exists'            => 'El aula seleccionada no existe.',
        ];
    }
}

