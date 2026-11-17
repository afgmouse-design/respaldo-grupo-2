<?php

namespace App\Http\Requests\EstructuraGeografica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para actualización de Parroquia (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Validar la actualización de una parroquia existente, asegurando coherencia
 * referencial con su municipio padre y permitiendo mantener el nombre actual mediante ignore de `id_par`.
 */
class UpdateParroquiaRequest extends FormRequest
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
        if ($this->has('nombre_par')) {
            $this->merge([
                'nombre_par' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $this->nombre_par)), MB_CASE_TITLE, "UTF-8"),
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
        $parroquiaId = $this->route('id_par') ?? $this->route('parroquia');
        if (is_object($parroquiaId)) {
            $parroquiaId = $parroquiaId->id_par;
        }

        return [
            'mun_id' => [
                'required',
                'integer',
                Rule::exists('municipio', 'id_mun')->whereNull('deleted_at'),
            ],
            'nombre_par' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\']+$/',
                Rule::unique('parroquia', 'nombre_par')
                    ->where(function ($query) {
                        return $query->where('mun_id', $this->input('mun_id'))
                            ->whereNull('deleted_at');
                    })
                    ->ignore($parroquiaId, 'id_par'),
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
            'mun_id.required'     => 'Debe seleccionar el municipio correspondiente.',
            'mun_id.integer'      => 'El identificador del municipio debe ser un número entero.',
            'mun_id.exists'       => 'El municipio seleccionado no existe en el sistema.',
            'nombre_par.required' => 'El nombre de la parroquia es obligatorio.',
            'nombre_par.string'   => 'El nombre de la parroquia debe ser una cadena de texto.',
            'nombre_par.min'      => 'El nombre de la parroquia debe contener al menos 2 caracteres.',
            'nombre_par.max'      => 'El nombre de la parroquia no puede exceder los 255 caracteres.',
            'nombre_par.regex'    => 'El nombre de la parroquia solo debe contener letras, espacios, guiones o apóstrofes (sin números).',
            'nombre_par.unique'   => 'Ya existe otra parroquia registrada con este nombre en el municipio seleccionado.',
            'status_id.integer'   => 'El estatus seleccionado debe ser un identificador entero válido.',
            'status_id.exists'    => 'El estatus seleccionado no existe en el catálogo del sistema.',
        ];
    }
}

