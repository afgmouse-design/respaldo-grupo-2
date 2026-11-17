<?php

namespace App\Http\Requests\OrganizacionInstitucional\Condicion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para creación de Condición de Sede (`condicion_sede`).
 *
 * Propósito: Validar el nombre único y descripción opcional de cada condición operativa
 * aplicable a la universidad matriz o a sus sedes territoriales.
 *
 * Historial:
 *  - 2026-11-14: Creación en reemplazo de `StoreCondicionRequest`, alineado con `condicion_sede`
 *    (`nombre_cse`, `descripcion_cse`) del esquema oficial `sogac_mio.sql`.
 */
class StoreCondicionSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_cse' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('condicion_sede', 'nombre_cse')->whereNull('deleted_at'),
            ],
            'descripcion_cse' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_cse.required' => 'El nombre de la condición de sede es obligatorio.',
            'nombre_cse.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'nombre_cse.unique'   => 'Ya existe una condición de sede registrada con ese nombre.',
            'descripcion_cse.max' => 'La descripción no puede exceder los 255 caracteres.',
        ];
    }
}
