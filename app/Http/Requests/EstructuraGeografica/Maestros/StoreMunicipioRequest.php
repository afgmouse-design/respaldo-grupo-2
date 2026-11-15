<?php

namespace App\Http\Requests\EstructuraGeografica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para creación de Municipio (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Validar que el nuevo municipio esté vinculado a un estado existente válido (`esd_id`)
 * y que su nombre sea único dentro de dicho estado territorial.
 */
class StoreMunicipioRequest extends FormRequest
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
        if ($this->has('nombre_mun')) {
            $this->merge([
                'nombre_mun' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $this->nombre_mun)), MB_CASE_TITLE, "UTF-8"),
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
            'esd_id' => [
                'required',
                'integer',
                'exists:estado,id_esd',
            ],
            'nombre_mun' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\']+$/',
                Rule::unique('municipio', 'nombre_mun')->where(function ($query) {
                    return $query->where('esd_id', $this->input('esd_id'));
                }),
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
            'esd_id.required'     => 'Debe seleccionar el estado o provincia correspondiente.',
            'esd_id.integer'      => 'El identificador del estado debe ser un número entero.',
            'esd_id.exists'       => 'El estado seleccionado no existe en el sistema.',
            'nombre_mun.required' => 'El nombre del municipio es obligatorio.',
            'nombre_mun.string'   => 'El nombre del municipio debe ser una cadena de texto.',
            'nombre_mun.min'      => 'El nombre del municipio debe contener al menos 2 caracteres.',
            'nombre_mun.max'      => 'El nombre del municipio no puede exceder los 255 caracteres.',
            'nombre_mun.regex'    => 'El nombre del municipio solo debe contener letras, espacios, guiones o apóstrofes (sin números).',
            'nombre_mun.unique'   => 'Ya existe un municipio registrado con este nombre en el estado seleccionado.',
        ];
    }
}

