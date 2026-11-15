<?php

namespace App\Http\Requests\ProgramacionAcademica\Cohorte;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para crear una Cohorte.
 *
 * Actualización 2026-10-21 (Validación de Límites Temporales con el Lapso Padre):
 * Se incorporó en `withValidator()` la verificación de que `fecha_inicio_coh`
 * no sea anterior a `fecha_inicio_la` ni posterior a `fecha_fin_la` del lapso académico seleccionado.
 */
class StoreCohorteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_coh'       => [
                'required', 'string', 'min:2', 'max:255',
                Rule::unique('cohorte', 'nombre_coh')
                    ->where(fn ($q) => $q->where('laa_id', $this->input('laa_id'))->whereNull('deleted_at')),
            ],
            'fecha_inicio_coh' => ['required', 'date'],
            'laa_id'           => ['required', 'integer', Rule::exists('lapso_academico', 'id_la')],
            'status'           => ['nullable'],
            'status_id'        => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_coh.required'       => 'El nombre de la cohorte es obligatorio.',
            'nombre_coh.unique'         => 'Ya existe una cohorte con ese nombre en el lapso académico seleccionado.',
            'fecha_inicio_coh.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio_coh.date'     => 'Ingresa una fecha de inicio válida.',
            'laa_id.required'           => 'Debes seleccionar un lapso académico.',
            'laa_id.exists'             => 'El lapso académico seleccionado no existe.',
        ];
    }

    /**
     * Valida que la fecha de la cohorte se encuentre dentro de los límites del lapso académico padre.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $laaId = $this->input('laa_id');
            $inicio = $this->input('fecha_inicio_coh');

            if ($laaId && $inicio) {
                $lapso = DB::table('lapso_academico')->where('id_la', $laaId)->first();

                if ($lapso) {
                    if (!empty($lapso->fecha_inicio_la) && $inicio < $lapso->fecha_inicio_la) {
                        $v->errors()->add(
                            'fecha_inicio_coh',
                            "La fecha de inicio de la cohorte ({$inicio}) no puede ser anterior a la fecha de inicio del lapso académico padre ({$lapso->fecha_inicio_la})."
                        );
                    }

                    if (!empty($lapso->fecha_fin_la) && $inicio > $lapso->fecha_fin_la) {
                        $v->errors()->add(
                            'fecha_inicio_coh',
                            "La fecha de inicio de la cohorte ({$inicio}) no puede ser posterior a la fecha de culminación del lapso académico padre ({$lapso->fecha_fin_la})."
                        );
                    }
                }
            }
        });
    }
}

