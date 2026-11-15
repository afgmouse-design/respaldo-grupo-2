<?php

namespace App\Http\Requests\EstructuraGeografica\Maestros;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request: Validación para creación de País (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Garantizar la integridad de los datos al registrar un nuevo país,
 * asegurando nombre obligatorio, longitud válida y unicidad global en la tabla `pais`.
 */
class StorePaisRequest extends FormRequest
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
        return [
            'nombre_pai' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\']+$/',
                'unique:pais,nombre_pai',
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
            'nombre_pai.unique'   => 'Ya existe un país registrado con ese nombre.',
        ];
    }
}

