<?php

namespace App\Http\Requests\EstructuraAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para actualizar Escala de Medición.
 */
class UpdateEscalaMedicionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        $id = $this->route('escala_medicion')
            ?? $this->route('id')
            ?? $this->route('id_esm')
            ?? $this->input('id_esm');

        return [
            'nombre_esm' => [
                'required',
                'string',
                'max:255',
                Rule::unique('escala_medicion', 'nombre_esm')->ignore($id, 'id_esm'),
            ],
            'nota_aprobatoria_esm' => ['required', 'numeric', 'min:0'],
            'nota_minima_esm'      => ['nullable', 'numeric', 'min:0', 'lte:nota_aprobatoria_esm'],
            'nota_maxima_esm'      => ['nullable', 'numeric', 'gte:nota_aprobatoria_esm'],
            'status'               => ['nullable', 'boolean'],
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'nombre_esm.required'           => 'El nombre de la escala de medición es obligatorio.',
            'nombre_esm.unique'             => 'Ya existe una escala de medición con este nombre.',
            'nota_aprobatoria_esm.required' => 'La nota mínima aprobatoria es obligatoria.',
            'nota_aprobatoria_esm.numeric'  => 'La nota aprobatoria debe ser un valor numérico.',
            'nota_minima_esm.lte'           => 'La nota mínima debe ser menor o igual a la nota aprobatoria.',
            'nota_maxima_esm.gte'           => 'La nota máxima debe ser mayor o igual a la nota aprobatoria.',
        ];
    }
}

