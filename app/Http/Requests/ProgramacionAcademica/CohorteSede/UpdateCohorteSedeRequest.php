<?php

/**
 * Propósito: Valida la actualización de una asignación Cohorte-Sede (`cohorte_sede`).
 * Historial: Actualizado para referenciar `sede_programa.id_spr` mediante `sep_id` y preservar inmutabilidad de `coh_id`.
 */

namespace App\Http\Requests\ProgramacionAcademica\CohorteSede;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateCohorteSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Acepta `spr_id` como alias opcional normalizándolo hacia `cohorte_sede.sep_id`.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('sep_id') && $this->filled('spr_id')) {
            $this->merge(['sep_id' => $this->input('spr_id')]);
        }
    }

    public function rules(): array
    {
        $param = $this->route('cohorte_sede');
        $id = is_object($param) ? ($param->id_cos ?? $param->getKey()) : $param;

        return [
            'nombre_cos' => ['required', 'string', 'min:2', 'max:255'],
            'coh_id'     => ['nullable', 'integer', Rule::exists('cohorte', 'id_coh')->whereNull('deleted_at')],
            'sep_id'     => [
                'required',
                'integer',
                Rule::exists('sede_programa', 'id_spr')->whereNull('deleted_at'),
                Rule::unique('cohorte_sede', 'sep_id')
                    ->ignore($id, 'id_cos')
                    ->where(fn ($q) => $q->where('coh_id', $this->input('coh_id'))->whereNull('deleted_at')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_cos.required' => 'El nombre descriptivo de la cohorte-sede es obligatorio.',
            'coh_id.exists'       => 'La cohorte seleccionada no existe.',
            'sep_id.required'     => 'Debes seleccionar la oferta Sede-Programa a la que pertenece.',
            'sep_id.exists'       => 'La oferta Sede-Programa seleccionada no existe.',
            'sep_id.unique'       => 'Esta oferta Sede-Programa ya fue asignada a la cohorte seleccionada.',
        ];
    }

    /**
     * Impide modificar la cohorte padre durante la edición.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $param = $this->route('cohorte_sede');
            $id = is_object($param) ? ($param->id_cos ?? $param->getKey()) : $param;
            $cs = $id ? DB::table('cohorte_sede')->where('id_cos', $id)->first() : null;

            if ($cs) {
                $nuevaCohorteId = $this->input('coh_id');
                if ($nuevaCohorteId && (int) $nuevaCohorteId !== (int) $cs->coh_id) {
                    $v->errors()->add(
                        'coh_id',
                        'La cohorte padre no puede ser modificada durante la edición de la asignación.'
                    );
                }
            }
        });
    }
}
