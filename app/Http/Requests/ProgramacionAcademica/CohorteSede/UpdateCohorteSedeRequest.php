<?php

namespace App\Http\Requests\ProgramacionAcademica\CohorteSede;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para actualizar una Cohorte por Sede.
 *
 * Actualización 2026-10-21 (Restauración de relación sep_id):
 * Se restauró la validación para `sep_id` referenciando `sede_pnf.id_sep` (en lugar de `sed_id`),
 * con exclusión de id (`ignore($id, 'id_cos')`) para permitir actualizar el registro sin falsos positivos de unicidad.
 *
 * Actualización 2026-10-21 (Protección de Padre Inmutable):
 * Se incorporó en `withValidator()` la comprobación para impedir alterar la cohorte padre (`coh_id`).
 */
class UpdateCohorteSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('cohorte_sede');

        return [
            'nombre_cos' => ['required', 'string', 'min:2', 'max:255'],
            'coh_id'     => ['nullable', 'integer', Rule::exists('cohorte', 'id_coh')],
            'sep_id'     => [
                'required', 'integer', Rule::exists('sede_pnf', 'id_sep'),
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
            'coh_id.required'     => 'Debes seleccionar una cohorte.',
            'coh_id.exists'       => 'La cohorte seleccionada no existe.',
            'sep_id.required'     => 'Debes seleccionar la oferta de Sede-PNF a la que pertenece.',
            'sep_id.exists'       => 'La oferta de Sede-PNF seleccionada no existe.',
            'sep_id.unique'       => 'Esta oferta de Sede-PNF ya fue asignada a la cohorte seleccionada.',
        ];
    }

    /**
     * Valida que no se intente alterar la cohorte padre durante la edición.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $id = $this->route('cohorte_sede');
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
