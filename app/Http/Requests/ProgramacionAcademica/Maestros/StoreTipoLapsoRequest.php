<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest: Validación para la creación de Tipos de Lapso Académico (`tipo_lapso`).
 *
 * Propósito: Validar el código único (`codigo_tla`), nombre (`nombre_tla`), duración en
 * semanas y las columnas aditivas `nomenclatura_tla` y `descripcion_tla`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a la tabla oficial `tipo_lapso(id_tla)` de `sogac_mio.sql`,
 *    reemplazando los campos legacy `tap_codigo` y `tap_nombre`.
 */
class StoreTipoLapsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_tla' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tipo_lapso', 'codigo_tla')->whereNull('deleted_at'),
            ],
            'nombre_tla'           => 'required|string|min:2|max:100',
            'duracion_semanas_tla' => 'required|integer|min:1|max:104',
            'nomenclatura_tla'     => 'nullable|string|max:20',
            'descripcion_tla'      => 'nullable|string|max:255',
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
        ];
    }
}
