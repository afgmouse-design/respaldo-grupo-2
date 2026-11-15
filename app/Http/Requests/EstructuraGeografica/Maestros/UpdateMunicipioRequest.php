<?php

namespace App\Http\Requests\EstructuraGeografica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para actualización de Municipio (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Validar la actualización de un municipio existente, asegurando coherencia
 * referencial con su estado padre y permitiendo mantener el nombre actual mediante ignore de `id_mun`.
 */
class UpdateMunicipioRequest extends FormRequest
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
        $municipioId = $this->route('id_mun') ?? $this->route('municipio');
        if (is_object($municipioId)) {
            $municipioId = $municipioId->id_mun;
        }

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
                Rule::unique('municipio', 'nombre_mun')
                    ->where(function ($query) {
                        return $query->where('esd_id', $this->input('esd_id'));
                    })
                    ->ignore($municipioId, 'id_mun'),
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
            'esd_id.required'     => 'Debe seleccionar el estado o provincia correspondiente.',
            'esd_id.integer'      => 'El identificador del estado debe ser un número entero.',
            'esd_id.exists'       => 'El estado seleccionado no existe en el sistema.',
            'nombre_mun.required' => 'El nombre del municipio es obligatorio.',
            'nombre_mun.string'   => 'El nombre del municipio debe ser una cadena de texto.',
            'nombre_mun.min'      => 'El nombre del municipio debe contener al menos 2 caracteres.',
            'nombre_mun.max'      => 'El nombre del municipio no puede exceder los 255 caracteres.',
            'nombre_mun.regex'    => 'El nombre del municipio solo debe contener letras, espacios, guiones o apóstrofes (sin números).',
            'nombre_mun.unique'   => 'Ya existe otro municipio registrado con este nombre en el estado seleccionado.',
            'status.boolean'      => 'El estado debe ser verdadero o falso.',
        ];
    }
}

