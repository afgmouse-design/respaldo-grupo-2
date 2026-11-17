<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest: Validación para la actualización de Tipos de Lapso Académico (`tipo_lapso`).
 *
 * Propósito: Validar cambios sobre `tipo_lapso(id_tla)` ignorando el registro actual en
 * la verificación de unicidad de `codigo_tla`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `id_tla`, `codigo_tla`, `nombre_tla`, `duracion_semanas_tla`,
 *    `nomenclatura_tla`, `descripcion_tla` y `status_id`.
 */
class UpdateTipoLapsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $param = $this->route('tipo_lapso') ?? $this->route('id_tla');
        $tipoLapsoId = is_object($param) ? ($param->id_tla ?? $param->id) : $param;

        return [
            'codigo_tla' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tipo_lapso', 'codigo_tla')
                    ->ignore($tipoLapsoId, 'id_tla')
                    ->whereNull('deleted_at'),
            ],
            'nombre_tla'           => 'required|string|min:2|max:100',
            'duracion_semanas_tla' => 'required|integer|min:1|max:104',
            'nomenclatura_tla'     => 'nullable|string|max:20',
            'descripcion_tla'      => 'nullable|string|max:255',
            'status_id'            => 'sometimes|integer|exists:status,id_sta',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_tla.required'           => 'El código del tipo de lapso es obligatorio.',
            'codigo_tla.max'                => 'El código no puede exceder los 20 caracteres.',
            'codigo_tla.unique'             => 'El código ya está registrado en otro tipo de lapso.',
            'nombre_tla.required'           => 'El nombre del tipo de lapso es obligatorio.',
            'nombre_tla.min'                => 'El nombre debe tener al menos 2 caracteres.',
            'nombre_tla.max'                => 'El nombre no puede exceder los 100 caracteres.',
            'duracion_semanas_tla.required' => 'La duración en semanas es obligatoria.',
            'duracion_semanas_tla.integer'  => 'La duración debe expresarse en semanas enteras.',
            'nomenclatura_tla.max'          => 'La nomenclatura no puede exceder los 20 caracteres.',
            'descripcion_tla.max'           => 'La descripción no puede exceder los 255 caracteres.',
            'status_id.exists'              => 'El estatus seleccionado no existe en el catálogo del sistema.',
        ];
    }
}
