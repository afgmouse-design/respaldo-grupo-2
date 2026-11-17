<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros;

use App\Http\Requests\ProgramacionAcademica\Maestros\Concerns\ValidatesLapsoAcademicoFechas;
use App\Models\Academico\LapsoAcademico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest: Validación para la actualización de Lapsos Académicos (`lapso_academico`).
 *
 * Propósito: Valida modificaciones sobre `lapso_academico(id_laa)` garantizando unicidad
 * de `codigo_laa`, inmutabilidad del año académico padre (`ana_id`) y consistencia de fechas.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `id_laa`, `ana_id`, `tla_id` y `status_id` de `sogac_mio.sql`.
 */
class UpdateLapsoAcademicoRequest extends FormRequest
{
    use ValidatesLapsoAcademicoFechas;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarEntradaLapso(false);
    }

    public function rules(): array
    {
        $param = $this->route('lapso_academico') ?? $this->route('lapsoAcademico') ?? $this->route('id');
        $id = is_object($param) ? ($param->id_laa ?? $param->id) : $param;

        return [
            'ana_id'             => 'nullable|integer|exists:ano_academico,id_ana',
            'tla_id'             => 'sometimes|integer|exists:tipo_lapso,id_tla',
            'uni_id'             => 'nullable|integer|exists:universidad,id_uni',
            'codigo_laa'         => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('lapso_academico', 'codigo_laa')
                    ->ignore($id, 'id_laa')
                    ->whereNull('deleted_at'),
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
            'tla_id.exists'               => 'El tipo de lapso seleccionado no existe en el sistema.',
            'codigo_laa.unique'           => 'Ya existe otro lapso académico registrado con ese código.',
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
            $param = $this->route('lapso_academico') ?? $this->route('lapsoAcademico') ?? $this->route('id');
            $id = is_object($param) ? ($param->id_laa ?? $param->id) : $param;
            $lapso = $id ? LapsoAcademico::obtenerPorId((int) $id) : null;

            if (! $lapso) {
                return;
            }

            $nuevoAnaId = $this->input('ana_id');
            if ($nuevoAnaId && (int) $nuevoAnaId !== (int) $lapso->ana_id) {
                $v->errors()->add(
                    'ana_id',
                    'El año académico padre no puede ser modificado durante la edición del lapso.'
                );
            }

            $this->validarRangoEnAnoAcademico($v, $lapso->ana_id ? (int) $lapso->ana_id : null);
        });
    }
}
