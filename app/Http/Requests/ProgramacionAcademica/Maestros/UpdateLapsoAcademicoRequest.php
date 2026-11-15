<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

/**
 * FormRequest: Validación para la actualización de Lapsos Académicos.
 *
 * Actualización 2026-10-21:
 * Se auditaron y corrigieron las reglas de validación para evitar rebotes silenciosos.
 *
 * Actualización 2026-10-21 (Protección de Padre Inmutable y Límites de Fechas):
 * Se incorporó en `withValidator()` la restricción que impide alterar el año académico padre (`ano_id`)
 * y la validación de que las nuevas fechas de inicio y fin del lapso se encuentren dentro de los límites
 * del año académico padre.
 */
class UpdateLapsoAcademicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ano_id'          => 'nullable|integer',
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
            'nombre_la.required'          => 'El nombre del lapso académico es obligatorio.',
            'fecha_inicio_la.required'    => 'La fecha de inicio del lapso es obligatoria.',
            'fecha_fin_la.required'       => 'La fecha de culminación del lapso es obligatoria.',
            'fecha_fin_la.after_or_equal' => 'La fecha de culminación debe ser posterior o igual a la fecha de inicio.',
        ];
    }

    /**
     * Valida la inmutabilidad del padre y que las fechas del lapso respeten el rango del año académico.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $param = $this->route('lapso_academico') ?? $this->route('lapsoAcademico') ?? $this->route('id');
            $id = is_object($param) ? ($param->id_la ?? $param->id) : $param;

            $lapso = $id ? DB::table('lapso_academico')->where('id_la', $id)->first() : null;

            if ($lapso) {
                // 1) Si se envió ano_id, verificar que no intente cambiar el padre
                $nuevoAnoId = $this->input('ano_id');
                if ($nuevoAnoId && (int) $nuevoAnoId !== (int) $lapso->ano_id) {
                    $v->errors()->add(
                        'ano_id',
                        'El año académico padre no puede ser modificado durante la edición del lapso.'
                    );
                }

                // 2) Validar fechas dentro del rango del año padre
                $ano = DB::table('ano_academico')->where('id_ano', $lapso->ano_id)->first();
                $inicio = $this->input('fecha_inicio_la');
                $fin = $this->input('fecha_fin_la');

                if ($ano && ($inicio || $fin)) {
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
