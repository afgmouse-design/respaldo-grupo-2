<?php

namespace App\Http\Requests\OrganizacionInstitucional\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para actualización de Universidad (Módulo 2 - Organización Institucional).
 *
 * Propósito: Valida y sanitiza los datos para la edición de la institución matriz,
 * permitiendo conservar el nombre o RIF actual ignorando el registro en curso.
 */
class UpdateUniversidadRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara y sanitiza los datos antes de ejecutar la validación.
     */
    protected function prepareForValidation(): void
    {
        $sanitized = [];

        if ($this->has('nombre_uni')) {
            $sanitized['nombre_uni'] = mb_convert_case(
                trim(preg_replace('/[0-9]/', '', (string) $this->nombre_uni)),
                MB_CASE_TITLE,
                'UTF-8'
            );
        }

        if ($this->filled('siglas_uni')) {
            $sanitized['siglas_uni'] = mb_strtoupper(trim((string) $this->siglas_uni), 'UTF-8');
        }

        if ($this->filled('rif_uni')) {
            $sanitized['rif_uni'] = mb_strtoupper(trim((string) $this->rif_uni), 'UTF-8');
        }

        if (!empty($sanitized)) {
            $this->merge($sanitized);
        }
    }

    /**
     * Reglas de validación aplicadas a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $universidadId = $this->route('id_uni') ?? $this->route('universidad');
        if (is_object($universidadId)) {
            $universidadId = $universidadId->id_uni;
        }

        return [
            'nombre_uni' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\'\.]+$/',
                Rule::unique('universidad', 'nombre_uni')->ignore($universidadId, 'id_uni'),
            ],
            'codigo_ministerio_uni' => [
                'nullable',
                'string',
                'max:30',
            ],
            'siglas_uni' => [
                'nullable',
                'string',
                'max:20',
            ],
            'rif_uni' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('universidad', 'rif_uni')->ignore($universidadId, 'id_uni'),
            ],
            'gaceta_uni' => [
                'nullable',
                'string',
                'max:255',
            ],
            'fecha_gaceta_uni' => [
                'nullable',
                'date',
            ],
            'decreto_uni' => [
                'nullable',
                'string',
                'max:255',
            ],
            'fecha_decreto_uni' => [
                'nullable',
                'date',
            ],
            'telefono_uni' => [
                'nullable',
                'string',
                'max:50',
            ],
            'correo_uni' => [
                'nullable',
                'email',
                'max:255',
            ],
            'sitio_web_uni' => [
                'nullable',
                'url',
                'max:255',
            ],
            'logo_uni' => [
                'nullable',
                'string',
            ],
            'fecha_inicio_uni' => [
                'nullable',
                'date',
            ],
            'fecha_fin_uni' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio_uni',
            ],
            'loc_id' => [
                'nullable',
                'integer',
                'exists:localidad,id_loc',
            ],
            'dpi_id' => [
                'nullable',
                'integer',
            ],
            'cdn_id' => [
                'nullable',
                'integer',
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
            'nombre_uni.required'          => 'El nombre legal de la universidad es obligatorio.',
            'nombre_uni.string'            => 'El nombre de la universidad debe ser una cadena de texto.',
            'nombre_uni.min'               => 'El nombre de la universidad debe tener al menos 3 caracteres.',
            'nombre_uni.max'               => 'El nombre de la universidad no puede exceder los 255 caracteres.',
            'nombre_uni.regex'             => 'El nombre de la universidad solo debe contener letras, espacios, guiones o puntos (sin números).',
            'nombre_uni.unique'            => 'Ya existe otra institución universitaria registrada con este nombre legal.',
            'codigo_ministerio_uni.max'    => 'El código ministerial no puede exceder los 30 caracteres.',
            'siglas_uni.max'               => 'Las siglas no pueden exceder los 20 caracteres.',
            'rif_uni.max'                  => 'El RIF institucional no puede exceder los 20 caracteres.',
            'rif_uni.unique'               => 'Ya existe otra institución registrada con este RIF.',
            'correo_uni.email'             => 'Debe ingresar un formato de correo electrónico válido.',
            'sitio_web_uni.url'            => 'Debe ingresar una URL válida para el sitio web.',
            'fecha_fin_uni.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'loc_id.exists'                => 'La localidad seleccionada no existe en el sistema.',
            'status.boolean'               => 'El estado debe ser verdadero o falso.',
        ];
    }
}

