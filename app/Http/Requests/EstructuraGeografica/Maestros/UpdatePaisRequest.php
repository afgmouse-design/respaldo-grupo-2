<?php

namespace App\Http\Requests\EstructuraGeografica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para actualización de País (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Validar la modificación de un país existente, permitiendo mantener el mismo
 * nombre actual mediante la exclusión de su llave primaria `id_pai` (AGENTS.md §6).
 */
class UpdatePaisRequest extends FormRequest
{
    /**
     * Determina si el usuario autenticado está autorizado para ejecutar la petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara los datos antes de la validación estandarizando a Title Case y removiendo números.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('nombre_pai')) {
            $this->merge([
                'nombre_pai' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $this->nombre_pai)), MB_CASE_TITLE, "UTF-8"),
            ]);
        }
    }

    /**
     * Reglas de validación aplicadas a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Resuelve el ID del modelo ya sea por inyección de modelo o parámetro de ruta
        $paisId = $this->route('id_pai') ?? $this->route('pais');
        if (is_object($paisId)) {
            $paisId = $paisId->id_pai;
        }

        return [
            'nombre_pai' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\']+$/',
                Rule::unique('pais', 'nombre_pai')->ignore($paisId, 'id_pai'),
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Mensajes personalizados de error para la validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_pai.required' => 'El nombre del país es obligatorio.',
            'nombre_pai.string'   => 'El nombre del país debe ser una cadena de texto.',
            'nombre_pai.min'      => 'El nombre del país debe contener al menos 2 caracteres.',
            'nombre_pai.max'      => 'El nombre del país no puede exceder los 255 caracteres.',
            'nombre_pai.regex'    => 'El nombre del país solo debe contener letras, espacios, guiones o apóstrofes (sin números).',
            'nombre_pai.unique'   => 'Ya existe otro país registrado con ese nombre.',
            'status.boolean'      => 'El estado debe ser verdadero o falso.',
        ];
    }
}

