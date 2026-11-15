<?php

namespace App\Http\Requests\ProgramacionAcademica\Seccion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para crear una Sección.
 */
class StoreSeccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_sec'      => ['nullable', 'string', 'max:50'],
            'nombre_sec'      => [
                'required', 'string', 'min:2', 'max:255',
                Rule::unique('seccion', 'nombre_sec')
                    ->where(fn ($q) => $q->where('laa_id', $this->input('laa_id'))
                                         ->where('sed_id', $this->input('sed_id'))
                                         ->whereNull('deleted_at')),
            ],
            'tipo_seccion'    => ['required', 'string', Rule::in(['Regular', 'PER', 'Repitiente', 'Dirigido'])],
            'nivel_sec'       => ['nullable', 'integer', 'min:0', 'max:10'],
            'semestre_sec'    => ['required', 'integer', 'min:1', 'max:12'],
            'trayecto_sec'    => ['required', 'integer', 'min:0', 'max:6'],
            'turno_sec'       => ['required', 'string', Rule::in(['Mañana', 'Tarde', 'Noche', 'Matutino', 'Vespertino', 'Nocturno'])],
            'cupo_maximo_sec' => ['required', 'integer', 'min:1', 'max:200'],
            'laa_id'          => ['required', 'integer', Rule::exists('lapso_academico', 'id_la')],
            'pnf_id'          => ['required', 'integer', Rule::exists('pnf', 'id_pnf')],
            'sed_id'          => ['required', 'integer', Rule::exists('sede', 'id_sed')],
            'cos_id'          => ['nullable', 'integer', Rule::exists('cohorte_sede', 'id_cos')],
            'mac_id'          => ['nullable', 'integer', Rule::exists('malla_curricular', 'id_mac')],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_sec.required'      => 'El nombre de la sección es obligatorio.',
            'nombre_sec.unique'        => 'Ya existe una sección con este nombre para el lapso y sede seleccionados.',
            'tipo_seccion.required'    => 'El tipo de sección es obligatorio (Regular, PER, Repitiente).',
            'semestre_sec.required'    => 'El semestre es obligatorio.',
            'trayecto_sec.required'    => 'El trayecto es obligatorio.',
            'turno_sec.required'       => 'El turno es obligatorio.',
            'cupo_maximo_sec.required' => 'El cupo máximo es obligatorio.',
            'laa_id.required'          => 'Debes seleccionar un lapso académico.',
            'pnf_id.required'          => 'Debes seleccionar un PNF.',
            'sed_id.required'          => 'Debes seleccionar una sede.',
        ];
    }
}
