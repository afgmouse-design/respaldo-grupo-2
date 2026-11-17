<?php

/**
 * Propósito: Valida la creación de una asignación Cohorte-Sede (`cohorte_sede`).
 * Historial: Actualizado para referenciar `sede_programa.id_spr` mediante la FK `sep_id` de `sogac_mio.sql`.
 */

namespace App\Http\Requests\ProgramacionAcademica\CohorteSede;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCohorteSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Acepta `spr_id` como alias opcional normalizándolo hacia la columna oficial `cohorte_sede.sep_id`.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('sep_id') && $this->filled('spr_id')) {
            $this->merge(['sep_id' => $this->input('spr_id')]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre_cos' => ['required', 'string', 'min:2', 'max:255'],
            'coh_id'     => ['required', 'integer', Rule::exists('cohorte', 'id_coh')->whereNull('deleted_at')],
            'sep_id'     => [
                'required',
                'integer',
                Rule::exists('sede_programa', 'id_spr')->whereNull('deleted_at'),
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
            'sep_id.required'     => 'Debes seleccionar la oferta Sede-Programa a la que pertenece.',
            'sep_id.exists'       => 'La oferta Sede-Programa seleccionada no existe.',
            'sep_id.unique'       => 'Esta oferta Sede-Programa ya fue asignada a la cohorte seleccionada.',
        ];
    }
}
