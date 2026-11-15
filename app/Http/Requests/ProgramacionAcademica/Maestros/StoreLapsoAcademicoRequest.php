<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

/**
 * FormRequest: Validación para el registro de Lapsos Académicos.
 *
 * Actualización 2026-10-21:
 * Se auditaron y corrigieron las reglas de validación para evitar rebotes silenciosos.
 * Se eliminaron dependencias foráneas innecesarias ('exists:tipo_lapsos') y se garantiza
 * compatibilidad tanto con 'nombre_la' / 'ano_id' como con sus alias.
 *
 * Actualización 2026-10-21 (Validación de Límites Temporales con el Año Padre):
 * Se incorporó en `withValidator()` la comprobación estricta de límites de fechas respecto
 * al Año Académico padre seleccionado (`fecha_inicio_la` >= `fecha_inicio_ano` y `fecha_fin_la` <= `fecha_fin_ano`).
 */
class StoreLapsoAcademicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ano_id'          => 'required|integer|exists:ano_academico,id_ano',
            'nombre_la'       => 'required|string|max:100',
            'fecha_inicio_la' => 'required|date',
            'fecha_fin_la'    => 'required|date|after_or_equal:fecha_inicio_la',
            'estado_la'       => 'nullable',
            'status_id'       => 'nullable|integer',
            'status'          => 'nullable',
            // Campos alternativos tolerados
            'lap_nombre'          => 'nullable|string|max:100',
            'lap_fecha_inicio'    => 'nullable|date',
            'lap_fecha_fin'       => 'nullable|date',
            'lap_tipo'            => 'nullable',
            'lap_cod_universidad' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'ano_id.required'             => 'Debe seleccionar un año académico padre.',
            'ano_id.exists'               => 'El año académico seleccionado no existe en el sistema.',
            'nombre_la.required'          => 'El nombre del lapso académico es obligatorio.',
            'fecha_inicio_la.required'    => 'La fecha de inicio del lapso es obligatoria.',
            'fecha_fin_la.required'       => 'La fecha de culminación del lapso es obligatoria.',
            'fecha_fin_la.after_or_equal' => 'La fecha de culminación debe ser posterior o igual a la fecha de inicio.',
        ];
    }

    /**
     * Valida que las fechas del lapso se encuentren dentro del rango del año académico padre.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $anoId = $this->input('ano_id');
            $inicio = $this->input('fecha_inicio_la');
            $fin = $this->input('fecha_fin_la');

            if ($anoId && ($inicio || $fin)) {
                $ano = DB::table('ano_academico')->where('id_ano', $anoId)->first();

                if ($ano) {
                    if (!empty($ano->fecha_inicio_ano) && !empty($inicio) && $inicio < $ano->fecha_inicio_ano) {
                        $v->errors()->add(
                            'fecha_inicio_la',
                            "La fecha de inicio del lapso ({$inicio}) no puede ser anterior al inicio del año académico padre ({$ano->fecha_inicio_ano})."
                        );
                    }

                    if (!empty($ano->fecha_fin_ano) && !empty($inicio) && $inicio > $ano->fecha_fin_ano) {
                        $v->errors()->add(
                            'fecha_inicio_la',
                            "La fecha de inicio del lapso ({$inicio}) no puede superar la culminación del año académico padre ({$ano->fecha_fin_ano})."
                        );
                    }

                    if (!empty($ano->fecha_fin_ano) && !empty($fin) && $fin > $ano->fecha_fin_ano) {
                        $v->errors()->add(
                            'fecha_fin_la',
                            "La fecha de culminación del lapso ({$fin}) no puede ser posterior a la culminación del año académico padre ({$ano->fecha_fin_ano})."
                        );
                    }

                    if (!empty($ano->fecha_inicio_ano) && !empty($fin) && $fin < $ano->fecha_inicio_ano) {
                        $v->errors()->add(
                            'fecha_fin_la',
                            "La fecha de culminación del lapso ({$fin}) no puede ser anterior al inicio del año académico padre ({$ano->fecha_inicio_ano})."
                        );
                    }
                }
            }
        });
    }

    /**
     * Prepara y normaliza los datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (!$this->has('nombre_la') && $this->has('lap_nombre')) {
            $merge['nombre_la'] = $this->input('lap_nombre');
        }
        if (!$this->has('fecha_inicio_la') && $this->has('lap_fecha_inicio')) {
            $merge['fecha_inicio_la'] = $this->input('lap_fecha_inicio');
        }
        if (!$this->has('fecha_fin_la') && $this->has('lap_fecha_fin')) {
            $merge['fecha_fin_la'] = $this->input('lap_fecha_fin');
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}
