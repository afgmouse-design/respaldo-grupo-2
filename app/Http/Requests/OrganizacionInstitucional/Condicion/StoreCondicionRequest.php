<?php

namespace App\Http\Requests\OrganizacionInstitucional\Condicion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: validación de servidor para crear una Condición.
 * El nombre es único dentro de su tipo de condición (coincide con unique(tco_id, nombre_cdn)).
 */
class StoreCondicionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_cdn'      => [
                'required', 'string', 'min:2', 'max:100',
                Rule::unique('condicion', 'nombre_cdn')
                    // Actualización 2026-10-21: tco_id ahora es opcional; si no viene, la unicidad se evalúa contra tco_id NULL.
                    ->where(fn ($q) => ($this->filled('tco_id') ? $q->where('tco_id', $this->input('tco_id')) : $q->whereNull('tco_id'))->whereNull('deleted_at')),
            ],
            'descripcion_cdn' => ['nullable', 'string', 'max:1000'],
            // Actualización 2026-10-21: antes 'required'; se quitó el select de Tipo de Condición del formulario
            // y la regla provocaba error de validación al omitirlo.
            'tco_id'          => ['nullable', 'integer', Rule::exists('tipo_condicion', 'id_tco')],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_cdn.required' => 'El nombre es obligatorio.',
            'nombre_cdn.unique'   => 'Ya existe una condición con ese nombre para el tipo seleccionado.',
            'tco_id.required'     => 'Debes seleccionar un tipo de condición.',
            'tco_id.exists'       => 'El tipo de condición seleccionado no existe.',
        ];
    }
}

