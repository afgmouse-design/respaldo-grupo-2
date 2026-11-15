<?php

namespace App\Http\Requests\ProgramacionAcademica\Cohorte;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para actualizar una Cohorte.
 *
 * Actualización 2026-10-21 (Protección de Padre Inmutable y Límites de Fechas):
 * Se incorporó en `withValidator()` la comprobación para impedir alterar el lapso padre (`laa_id`)
 * y la verificación de que `fecha_inicio_coh` respete el período del lapso académico padre.
 */
class UpdateCohorteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('cohorte');

        return [
            'nombre_coh'       => [
                'required', 'string', 'min:2', 'max:255',
                Rule::unique('cohorte', 'nombre_coh')
                    ->ignore($id, 'id_coh')
                    ->where(fn ($q) => $q->where('laa_id', $this->input('laa_id'))->whereNull('deleted_at')),
            ],
            'fecha_inicio_coh' => ['required', 'date'],
            'laa_id'           => ['nullable', 'integer'],
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
        ];
    }

    /**
     * Valida la inmutabilidad del padre y el rango de fecha respecto al lapso.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $id = $this->route('cohorte');
            $cohorte = $id ? DB::table('cohorte')->where('id_coh', $id)->first() : null;

            if ($cohorte) {
                // 1) Si se envió laa_id, verificar que no intente cambiar el padre
                $nuevoLapsoId = $this->input('laa_id');
                if ($nuevoLapsoId && (int) $nuevoLapsoId !== (int) $cohorte->laa_id) {
                    $v->errors()->add(
                        'laa_id',
                        'El lapso académico padre no puede ser modificado durante la edición de la cohorte.'
                    );
                }

                // 2) Validar fechas dentro del rango del lapso padre
                $lapso = DB::table('lapso_academico')->where('id_la', $cohorte->laa_id)->first();
                $inicio = $this->input('fecha_inicio_coh');

                if ($lapso && $inicio) {
                    if (!empty($lapso->fecha_inicio_la) && $inicio < $lapso->fecha_inicio_la) {
                        $v->errors()->add(
                            'fecha_inicio_coh',
                            "La fecha de inicio de la cohorte ({$inicio}) no puede ser anterior al inicio del lapso académico padre ({$lapso->fecha_inicio_la})."
                        );
                    }

                    if (!empty($lapso->fecha_fin_la) && $inicio > $lapso->fecha_fin_la) {
                        $v->errors()->add(
                            'fecha_inicio_coh',
                            "La fecha de inicio de la cohorte ({$inicio}) no puede ser posterior a la culminación del lapso académico padre ({$lapso->fecha_fin_la})."
                        );
                    }
                }
            }
        });
    }
}

