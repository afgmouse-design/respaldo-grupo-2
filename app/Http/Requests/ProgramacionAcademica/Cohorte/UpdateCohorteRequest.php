<?php

/**
 * Propósito: Valida la actualización de una Cohorte asegurando inmutabilidad del lapso padre y rango de fechas.
 * Historial: Actualizado para consultar `lapso_academico.id_laa`, `fecha_inicio_laa`, `fecha_fin_laa` y `status_id`.
 */

namespace App\Http\Requests\ProgramacionAcademica\Cohorte;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateCohorteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $param = $this->route('cohorte');
        $id = is_object($param) ? ($param->id_coh ?? $param->getKey()) : $param;

        return [
            'nombre_coh' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('cohorte', 'nombre_coh')
                    ->ignore($id, 'id_coh')
                    ->where(fn ($q) => $q->where('laa_id', $this->input('laa_id'))->whereNull('deleted_at')),
            ],
            'fecha_inicio_coh' => ['required', 'date'],
            'laa_id'           => ['nullable', 'integer'],
            'status'           => ['nullable'],
            'status_id'        => ['nullable', 'integer', 'exists:status,id_sta'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_coh.required'       => 'El nombre de la cohorte es obligatorio.',
            'nombre_coh.unique'         => 'Ya existe una cohorte con ese nombre en el lapso académico seleccionado.',
            'fecha_inicio_coh.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio_coh.date'     => 'Ingresa una fecha de inicio válida.',
        ];
    }

    /**
     * Valida la inmutabilidad del lapso padre y la consistencia de fecha respecto a `lapso_academico`.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $param = $this->route('cohorte');
            $id = is_object($param) ? ($param->id_coh ?? $param->getKey()) : $param;
            $cohorte = $id ? DB::table('cohorte')->where('id_coh', $id)->first() : null;

            if ($cohorte) {
                $nuevoLapsoId = $this->input('laa_id');
                if ($nuevoLapsoId && (int) $nuevoLapsoId !== (int) $cohorte->laa_id) {
                    $v->errors()->add(
                        'laa_id',
                        'El lapso académico padre no puede ser modificado durante la edición de la cohorte.'
                    );
                }

                $lapso = DB::table('lapso_academico')->where('id_laa', $cohorte->laa_id)->whereNull('deleted_at')->first();
                $inicio = $this->input('fecha_inicio_coh');

                if ($lapso && $inicio) {
                    if (!empty($lapso->fecha_inicio_laa) && $inicio < $lapso->fecha_inicio_laa) {
                        $v->errors()->add(
                            'fecha_inicio_coh',
                            "La fecha de inicio de la cohorte ({$inicio}) no puede ser anterior al inicio del lapso académico padre ({$lapso->fecha_inicio_laa})."
                        );
                    }

                    if (!empty($lapso->fecha_fin_laa) && $inicio > $lapso->fecha_fin_laa) {
                        $v->errors()->add(
                            'fecha_inicio_coh',
                            "La fecha de inicio de la cohorte ({$inicio}) no puede ser posterior a la culminación del lapso académico padre ({$lapso->fecha_fin_laa})."
                        );
                    }
                }
            }
        });
    }
}
