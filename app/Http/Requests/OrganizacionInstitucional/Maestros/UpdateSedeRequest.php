<?php

namespace App\Http\Requests\OrganizacionInstitucional\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para actualización de Sede Universitaria.
 *
 * Propósito: Valida y sanitiza las modificaciones sobre una sede existente,
 * asegurando la unicidad contextual del nombre dentro de la misma universidad.
 *
 * Actualización (Desacople de Selección de Universidad):
 * Motivo: Dado que el sistema opera con una única universidad matriz institucional,
 * se asigna automáticamente el identificador de la universidad activa si no viene
 * presente en el payload, evitando solicitarla manualmente al usuario.
 */
class UpdateSedeRequest extends FormRequest
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

        if (! $this->filled('uni_id')) {
            $sanitized['uni_id'] = \App\Models\OrganizacionInstitucional\Maestros\Universidad::obtenerRegistroMatriz()?->id_uni ?? 1;
        }

        if (! $this->filled('cse_id') && $this->filled('cdn_id')) {
            $sanitized['cse_id'] = $this->input('cdn_id');
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

        if (! empty($sanitized)) {
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
        $sedeId = $this->route('id_sed') ?? $this->route('sede');
        if (is_object($sedeId)) {
            $sedeId = $sedeId->id_sed;
        }

        return [
            'uni_id' => [
                'required',
                'integer',
                Rule::exists('universidad', 'id_uni')->whereNull('deleted_at'),
            ],
            'loc_id' => [
                'nullable',
                'integer',
                Rule::exists('localidad', 'id_loc')->whereNull('deleted_at'),
            ],
            'cse_id' => [
                'nullable',
                'integer',
                Rule::exists('condicion_sede', 'id_cse')->whereNull('deleted_at'),
            ],
            'nombre_sed' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\'\.]+$/',
                Rule::unique('sede', 'nombre_sed')
                    ->where(function ($query) {
                        return $query->where('uni_id', $this->input('uni_id'))->whereNull('deleted_at');
                    })
                    ->ignore($sedeId, 'id_sed'),
            ],
            'siglas_sed' => [
                'nullable',
                'string',
                'max:3',
            ],
            'codigo_ministerio_sed' => [
                'nullable',
                'string',
                'max:10',
            ],
            'direccion_sed' => [
                'nullable',
                'string',
                'max:255',
            ],
            'telefono_sed' => [
                'nullable',
                'string',
                'max:12',
            ],
            'fax_sed' => [
                'nullable',
                'string',
                'max:12',
            ],
            'correo_sed' => [
                'nullable',
                'email',
                'max:100',
            ],
            'gaceta_sed' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'fecha_gaceta_sed' => [
                'nullable',
                'date',
            ],
            'resolucion_sed' => [
                'nullable',
                'integer',
                'min:0',
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
            'uni_id.required'              => 'Debe vincular la sede a una universidad matriz.',
            'uni_id.exists'                => 'La universidad seleccionada no existe en el sistema.',
            'loc_id.exists'                => 'La localidad seleccionada no existe en el sistema.',
            'cse_id.exists'                => 'La condición de sede seleccionada no existe en el sistema.',
            'nombre_sed.required'          => 'El nombre oficial de la sede es obligatorio.',
            'nombre_sed.min'               => 'El nombre de la sede debe tener al menos 3 caracteres.',
            'nombre_sed.max'               => 'El nombre de la sede no puede exceder los 100 caracteres.',
            'nombre_sed.regex'             => 'El nombre de la sede solo debe contener letras, espacios, guiones o puntos (sin números).',
            'nombre_sed.unique'            => 'Ya existe otra sede registrada con este nombre en la universidad seleccionada.',
            'siglas_sed.max'               => 'Las siglas no pueden exceder los 3 caracteres.',
            'codigo_ministerio_sed.max'    => 'El código ministerial no puede exceder los 10 caracteres.',
            'direccion_sed.max'            => 'La dirección no puede exceder los 255 caracteres.',
            'telefono_sed.max'             => 'El teléfono no puede exceder los 12 caracteres.',
            'fax_sed.max'                  => 'El fax no puede exceder los 12 caracteres.',
            'correo_sed.max'               => 'El correo electrónico no puede exceder los 100 caracteres.',
            'correo_sed.email'             => 'Debe ingresar un formato de correo electrónico válido.',
            'gaceta_sed.integer'           => 'El número de gaceta debe ser un valor numérico entero.',
            'resolucion_sed.integer'       => 'El número de resolución debe ser un valor numérico entero.',
            'fecha_fin_sed.after_or_equal' => 'La fecha de cese de operaciones debe ser posterior o igual a la de inicio.',
            'status_id.integer'            => 'El estatus seleccionado debe ser un identificador entero válido.',
            'status_id.exists'             => 'El estatus seleccionado no existe en el catálogo del sistema.',
        ];
    }
}

