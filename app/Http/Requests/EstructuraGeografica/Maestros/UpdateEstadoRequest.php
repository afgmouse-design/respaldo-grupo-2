<?php

namespace App\Http\Requests\EstructuraGeografica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para actualización de Estado (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Validar la actualización de un estado existente, asegurando coherencia
 * referencial con su país padre y permitiendo mantener el nombre actual mediante ignore de `id_esd`.
 */
class UpdateEstadoRequest extends FormRequest
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
        if ($this->has('nombre_esd')) {
            $this->merge([
                'nombre_esd' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $this->nombre_esd)), MB_CASE_TITLE, "UTF-8"),
            ]);
        }

        if ($this->filled('iso_esd')) {
            $this->merge([
                'iso_esd' => mb_strtoupper(trim((string) $this->iso_esd), 'UTF-8'),
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
        $estadoId = $this->route('id_esd') ?? $this->route('estado');
        if (is_object($estadoId)) {
            $estadoId = $estadoId->id_esd;
        }

        return [
            'pai_id' => [
                'required',
                'integer',
                Rule::exists('pais', 'id_pai')->whereNull('deleted_at'),
            ],
            'nombre_esd' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\']+$/',
                Rule::unique('estado', 'nombre_esd')
                    ->where(function ($query) {
                        return $query->where('pai_id', $this->input('pai_id'))
                            ->whereNull('deleted_at');
                    })
                    ->ignore($estadoId, 'id_esd'),
            ],
            'iso_esd' => [
                'nullable',
                'string',
                'max:10',
            ],
            'status_id' => [
                'sometimes',
                'integer',
                'exists:status,id_sta',
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
            'pai_id.required'     => 'Debe seleccionar el país al que pertenece el estado.',
            'pai_id.integer'      => 'El identificador del país debe ser un número entero.',
            'pai_id.exists'       => 'El país seleccionado no existe en el sistema.',
            'nombre_esd.required' => 'El nombre del estado es obligatorio.',
            'nombre_esd.string'   => 'El nombre del estado debe ser una cadena de texto.',
            'nombre_esd.min'      => 'El nombre del estado debe contener al menos 2 caracteres.',
            'nombre_esd.max'      => 'El nombre del estado no puede exceder los 255 caracteres.',
            'nombre_esd.regex'    => 'El nombre del estado solo debe contener letras, espacios, guiones o apóstrofes (sin números).',
            'nombre_esd.unique'   => 'Ya existe otro estado registrado con este nombre en el país seleccionado.',
            'iso_esd.max'         => 'El código ISO del estado no puede exceder los 10 caracteres.',
            'status_id.integer'   => 'El estatus seleccionado debe ser un identificador entero válido.',
            'status_id.exists'    => 'El estatus seleccionado no existe en el catálogo del sistema.',
        ];
    }
}

