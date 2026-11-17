<?php

/**
 * Propósito: Valida la creación de una oferta Sede-Programa (`sede_programa`).
 * Historial: Creado para sustituir `StoreSedePnfRequest`, alineando `prg_id -> programa.id_prg`, `codigo_ministerio_spr` y `cantidad_asignados_spr` con `sogac_mio.sql`.
 */

namespace App\Http\Requests\ProgramacionAcademica\SedePrograma;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSedeProgramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza alias heredado `pnf_id` hacia `prg_id`.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('prg_id') && $this->filled('pnf_id')) {
            $this->merge(['prg_id' => $this->input('pnf_id')]);
        }
    }

    public function rules(): array
    {
        return [
            'sed_id' => ['required', 'integer', Rule::exists('sede', 'id_sed')->whereNull('deleted_at')],
            'prg_id' => [
                'required',
                'integer',
                Rule::exists('programa', 'id_prg')->whereNull('deleted_at'),
                Rule::unique('sede_programa', 'prg_id')
                    ->where(fn ($q) => $q->where('sed_id', $this->input('sed_id'))->whereNull('deleted_at')),
            ],
            'codigo_ministerio_spr'  => ['nullable', 'string', 'max:5'],
            'cantidad_asignados_spr' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'sed_id.required' => 'Debes seleccionar una sede.',
            'sed_id.exists'   => 'La sede seleccionada no existe o fue eliminada.',
            'prg_id.required' => 'Debes seleccionar un programa académico.',
            'prg_id.exists'   => 'El programa seleccionado no existe o fue eliminado.',
            'prg_id.unique'   => 'Este programa ya está registrado en la sede seleccionada.',
        ];
    }
}
