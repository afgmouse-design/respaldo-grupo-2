<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros;

use App\Http\Requests\ProgramacionAcademica\Maestros\Concerns\ValidatesLapsoAcademicoFechas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest: Validación para el registro de Lapsos Académicos (`lapso_academico`).
 *
 * Propósito: Valida los campos oficiales de `lapso_academico` (`tla_id`, `ana_id`,
 * `codigo_laa`, `nombre_laa`, fechas y estatus) garantizando coherencia referencial
 * y límites temporales dentro del Año Académico padre.
 *
 * Historial:
 *  - 2026-11-14: Refactorizado hacia `lapso_academico(id_laa)` de `sogac_mio.sql`,
 *    extrayendo normalización y límites de fechas a `ValidatesLapsoAcademicoFechas`.
 */
class StoreLapsoAcademicoRequest extends FormRequest
{
    use ValidatesLapsoAcademicoFechas;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarEntradaLapso(true);
    }

    public function rules(): array
    {
        return [
            'ana_id'             => 'required|integer|exists:ano_academico,id_ana',
            'tla_id'             => 'required|integer|exists:tipo_lapso,id_tla',
            'uni_id'             => 'nullable|integer|exists:universidad,id_uni',
            'codigo_laa'         => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('lapso_academico', 'codigo_laa')->whereNull('deleted_at'),
            ],
            'nombre_laa'         => 'required|string|max:100',
            'fecha_inicio_laa'   => 'required|date',
            'fecha_fin_laa'      => 'required|date|after_or_equal:fecha_inicio_laa',
            'condicion_laa'      => 'nullable|string|size:1',
            'cerrado_laa'        => 'nullable|string|size:1',
            'habilitar_nota_laa' => 'nullable|string|size:1',
            'fase_operativa_laa' => 'nullable|string|max:30',
            'status_id'          => 'nullable|integer|exists:status,id_sta',
        ];
    }

    public function messages(): array
    {
        return [
            'ana_id.required'             => 'Debe seleccionar un año académico padre.',
            'ana_id.exists'               => 'El año académico seleccionado no existe en el sistema.',
            'tla_id.required'             => 'Debe seleccionar un tipo de lapso académico.',
            'tla_id.exists'               => 'El tipo de lapso seleccionado no existe en el sistema.',
            'codigo_laa.unique'           => 'Ya existe un lapso académico registrado con ese código.',
            'codigo_laa.max'              => 'El código del lapso no puede exceder los 20 caracteres.',
            'nombre_laa.required'         => 'El nombre del lapso académico es obligatorio.',
            'nombre_laa.max'              => 'El nombre del lapso no puede exceder los 100 caracteres.',
            'fecha_inicio_laa.required'   => 'La fecha de inicio del lapso es obligatoria.',
            'fecha_fin_laa.required'      => 'La fecha de culminación del lapso es obligatoria.',
            'fecha_fin_laa.after_or_equal'=> 'La fecha de culminación debe ser posterior o igual a la fecha de inicio.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $anaId = $this->input('ana_id');
            $this->validarRangoEnAnoAcademico($v, $anaId ? (int) $anaId : null);
        });
    }
}
