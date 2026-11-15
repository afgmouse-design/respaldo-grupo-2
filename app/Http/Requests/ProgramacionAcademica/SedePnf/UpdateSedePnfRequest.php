<?php

namespace App\Http\Requests\ProgramacionAcademica\SedePnf;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para actualizar una oferta Sede PNF.
 * Igual que Store, pero la regla unique ignora el propio registro (id_sep) para permitir conservar valores.
 */
class UpdateSedePnfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('sede_pnf');

        return [
            'sed_id' => ['required', 'integer', Rule::exists('sede', 'id_sed')],
            'pnf_id' => [
                'required', 'integer', Rule::exists('pnf', 'id_pnf'),
                Rule::unique('sede_pnf', 'pnf_id')
                    ->ignore($id, 'id_sep')
                    ->where(fn ($q) => $q->where('sed_id', $this->input('sed_id'))->whereNull('deleted_at')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sed_id.required' => 'Debes seleccionar una sede.',
            'sed_id.exists'   => 'La sede seleccionada no existe.',
            'pnf_id.required' => 'Debes seleccionar un PNF.',
            'pnf_id.exists'   => 'El PNF seleccionado no existe.',
            'pnf_id.unique'   => 'Este PNF ya está registrado en la sede seleccionada.',
        ];
    }
}

