<?php

/**
 * Propósito: Valida el registro de un bloque en `horario`.
 * Historial: Actualizado para aceptar `tipo_sesion_hor`, verificar `whereNull('deleted_at')` y validar `after:hora_inicio_hor`.
 */

namespace App\Http\Requests\ProgramacionAcademica\Horario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dia_semana_hor'  => ['required', 'string', Rule::in(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'])],
            'hora_inicio_hor' => ['required', 'string'],
            'hora_fin_hor'    => ['required', 'string', 'after:hora_inicio_hor'],
            'tipo_sesion_hor' => ['nullable', 'string', 'max:50'],
            'caa_id'          => ['required', 'integer', Rule::exists('carga_academica', 'id_caa')->whereNull('deleted_at')],
            'aul_id'          => ['required', 'integer', Rule::exists('aula', 'id_aul')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'dia_semana_hor.required'  => 'El día de la semana es obligatorio.',
            'dia_semana_hor.in'        => 'El día seleccionado no es válido.',
            'hora_inicio_hor.required' => 'La hora de inicio es obligatoria.',
            'hora_fin_hor.required'    => 'La hora de fin es obligatoria.',
            'hora_fin_hor.after'       => 'La hora de culminación debe ser posterior a la hora de inicio.',
            'caa_id.required'          => 'Debe seleccionar una carga académica.',
            'caa_id.exists'            => 'La carga académica seleccionada no existe.',
            'aul_id.required'          => 'Debe seleccionar un aula física.',
            'aul_id.exists'            => 'El aula seleccionada no existe.',
        ];
    }
}
