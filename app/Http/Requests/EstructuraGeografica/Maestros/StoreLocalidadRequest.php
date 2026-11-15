<?php

namespace App\Http\Requests\EstructuraGeografica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para creación de Localidad (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Validar que la nueva localidad esté vinculada a una parroquia existente válida (`par_id`)
 * y que su nombre sea único dentro de dicha parroquia.
 */
class StoreLocalidadRequest extends FormRequest
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
        if ($this->has('nombre_loc')) {
            $this->merge([
                'nombre_loc' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $this->nombre_loc)), MB_CASE_TITLE, "UTF-8"),
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
            'par_id' => [
                'required',
                'integer',
                'exists:parroquia,id_par',
            ],
            'nombre_loc' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\']+$/',
                Rule::unique('localidad', 'nombre_loc')->where(function ($query) {
                    return $query->where('par_id', $this->input('par_id'));
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
            'par_id.required'     => 'Debe seleccionar la parroquia correspondiente.',
            'par_id.integer'      => 'El identificador de la parroquia debe ser un número entero.',
            'par_id.exists'       => 'La parroquia seleccionada no existe en el sistema.',
            'nombre_loc.required' => 'El nombre de la localidad es obligatorio.',
            'nombre_loc.string'   => 'El nombre de la localidad debe ser una cadena de texto.',
            'nombre_loc.min'      => 'El nombre de la localidad debe contener al menos 2 caracteres.',
            'nombre_loc.max'      => 'El nombre de la localidad no puede exceder los 255 caracteres.',
            'nombre_loc.regex'    => 'El nombre de la localidad solo debe contener letras, espacios, guiones o apóstrofes (sin números).',
            'nombre_loc.unique'   => 'Ya existe una localidad registrada con este nombre en la parroquia seleccionada.',
        ];
    }
}

