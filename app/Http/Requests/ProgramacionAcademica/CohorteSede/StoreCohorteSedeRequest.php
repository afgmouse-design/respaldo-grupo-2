<?php

namespace App\Http\Requests\ProgramacionAcademica\CohorteSede;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para crear una Cohorte por Sede.
 *
 * Actualización 2026-10-21 (Restauración de relación sep_id):
 * Se restauró la validación para `sep_id` referenciando `sede_pnf.id_sep` (en lugar de `sed_id`),
 * asegurando la unicidad de la asignación `[coh_id, sep_id]` que exige el esquema de base de datos.
 */
class StoreCohorteSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_cos' => ['required', 'string', 'min:2', 'max:255'],
            'coh_id'     => ['required', 'integer', Rule::exists('cohorte', 'id_coh')],
            'sep_id'     => [
                'required', 'integer', Rule::exists('sede_pnf', 'id_sep'),
                Rule::unique('cohorte_sede', 'sep_id')
                    ->where(fn ($q) => $q->where('coh_id', $this->input('coh_id'))->whereNull('deleted_at')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_cos.required' => 'El nombre descriptivo de la cohorte-sede es obligatorio.',
            'coh_id.required'     => 'Debes seleccionar una cohorte.',
            'coh_id.exists'       => 'La cohorte seleccionada no existe.',
            'sep_id.required'     => 'Debes seleccionar la oferta de Sede-PNF a la que pertenece.',
            'sep_id.exists'       => 'La oferta de Sede-PNF seleccionada no existe.',
            'sep_id.unique'       => 'Esta oferta de Sede-PNF ya fue asignada a la cohorte seleccionada.',
        ];
    }
}
