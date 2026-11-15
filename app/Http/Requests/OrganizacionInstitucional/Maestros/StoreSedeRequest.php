<?php

namespace App\Http\Requests\OrganizacionInstitucional\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para creación de Sede Universitaria.
 *
 * Propósito: Valida y sanitiza los atributos requeridos para el registro de una
 * sede o núcleo territorial, asegurando coherencia referencial y unicidad.
 *
 * Actualización (Desacople de Selección de Universidad):
 * Motivo: Dado que el sistema opera con una única universidad matriz institucional,
 * se asigna automáticamente el identificador de la universidad activa si no viene
 * presente en el payload, evitando solicitarla manualmente al usuario.
 */
class StoreSedeRequest extends FormRequest
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

        if (!$this->filled('uni_id')) {
            $sanitized['uni_id'] = \App\Models\OrganizacionInstitucional\Maestros\Universidad::first()?->id_uni ?? 1;
        }

        if ($this->has('nombre_sed')) {
            $sanitized['nombre_sed'] = mb_convert_case(
                trim(preg_replace('/[0-9]/', '', (string) $this->nombre_sed)),
                MB_CASE_TITLE,
                'UTF-8'
            );
        }

        if ($this->filled('siglas_sed')) {
            $sanitized['siglas_sed'] = mb_strtoupper(
                preg_replace('/\s+/', '', (string) $this->siglas_sed),
                'UTF-8'
            );
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
        return [
            'uni_id' => [
                'required',
                'integer',
                'exists:universidad,id_uni',
            ],
            'loc_id' => [
                'required',
                'integer',
                'exists:localidad,id_loc',
            ],
            'cdn_id' => [
                'nullable',
                'integer',
                'exists:condicion,id_cdn',
            ],
            'nombre_sed' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\'\.]+$/',
                Rule::unique('sede', 'nombre_sed')->where(function ($query) {
                    return $query->where('uni_id', $this->input('uni_id'))->whereNull('deleted_at');
                }),
            ],
            'siglas_sed' => [
                'nullable',
                'string',
                'max:20',
            ],
            'codigo_ministerio_sed' => [
                'nullable',
                'string',
                'max:30',
            ],
            'direccion_sed' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'telefono_sed' => [
                'nullable',
                'string',
                'max:50',
            ],
            'correo_sed' => [
                'nullable',
                'email',
                'max:255',
            ],
            'gaceta_sed' => [
                'nullable',
                'string',
                'max:255',
            ],
            'fecha_gaceta_sed' => [
                'nullable',
                'date',
            ],
            'resolucion_sed' => [
                'nullable',
                'string',
                'max:255',
            ],
            'fecha_resolucion_sed' => [
                'nullable',
                'date',
            ],
            'fecha_inicio_sed' => [
                'nullable',
                'date',
            ],
            'fecha_fin_sed' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio_sed',
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
            'uni_id.required'              => 'Debe vincular la sede a una universidad matriz.',
            'uni_id.exists'                => 'La universidad seleccionada no existe en el sistema.',
            'loc_id.required'              => 'Debe seleccionar la localidad geográfica de la sede.',
            'loc_id.exists'                => 'La localidad seleccionada no existe en el sistema.',
            'cdn_id.exists'                => 'La condición seleccionada no existe en el sistema.',
            'nombre_sed.required'          => 'El nombre oficial de la sede es obligatorio.',
            'nombre_sed.min'               => 'El nombre de la sede debe tener al menos 3 caracteres.',
            'nombre_sed.max'               => 'El nombre de la sede no puede exceder los 255 caracteres.',
            'nombre_sed.regex'             => 'El nombre de la sede solo debe contener letras, espacios, guiones o puntos (sin números).',
            'nombre_sed.unique'            => 'Ya existe una sede registrada con este nombre en la universidad seleccionada.',
            'siglas_sed.max'               => 'Las siglas no pueden exceder los 20 caracteres.',
            'codigo_ministerio_sed.max'    => 'El código ministerial no puede exceder los 30 caracteres.',
            'correo_sed.email'             => 'Debe ingresar un formato de correo electrónico válido.',
            'fecha_fin_sed.after_or_equal' => 'La fecha de cese de operaciones debe ser posterior o igual a la de inicio.',
        ];
    }
}

