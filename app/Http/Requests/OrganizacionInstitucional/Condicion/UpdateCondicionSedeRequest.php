<?php

namespace App\Http\Requests\OrganizacionInstitucional\Condicion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para actualización de Condición de Sede (`condicion_sede`).
 *
 * Propósito: Validar modificaciones de nombre, descripción o estatus lógico sobre un registro
 * de `condicion_sede`, ignorando el identificador `id_cse` en edición.
 *
 * Historial:
 *  - 2026-11-14: Creación en reemplazo de `UpdateCondicionRequest`, alineado con `condicion_sede`.
 */
class UpdateCondicionSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('condicion') ?? $this->route('id_cse');
        if (is_object($id)) {
            $id = $id->id_cse;
        }

        return [
            'nombre_cse' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('condicion_sede', 'nombre_cse')
                    ->ignore($id, 'id_cse')
                    ->whereNull('deleted_at'),
            ],
            'descripcion_cse' => ['nullable', 'string', 'max:255'],
            'status_id'       => ['sometimes', 'integer', 'exists:status,id_sta'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_cse.required' => 'El nombre de la condición de sede es obligatorio.',
            'nombre_cse.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'nombre_cse.unique'   => 'Ya existe otra condición de sede registrada con ese nombre.',
            'descripcion_cse.max' => 'La descripción no puede exceder los 255 caracteres.',
            'status_id.exists'    => 'El estatus seleccionado no existe en el catálogo del sistema.',
        ];
    }
}
