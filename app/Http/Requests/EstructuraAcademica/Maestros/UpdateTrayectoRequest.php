<?php

namespace App\Http\Requests\EstructuraAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request: Validación para actualizar Trayecto.
 */
class UpdateTrayectoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'nombre_tra'       => ['required', 'string', 'max:100'],
            'codigo_tra'       => ['nullable', 'string', 'max:20'],
            'numero_orden_tra' => ['required', 'integer', 'min:0', 'max:50'],
            'tpd_id'           => ['required', 'integer', 'exists:tipo_periodo,id_tpd'],
            'status'           => ['nullable', 'boolean'],
        ];
    }

    /**
     * Mensajes personalizados.
     */
    public function messages(): array
    {
        return [
            'nombre_tra.required'       => 'El nombre del trayecto es obligatorio.',
            'nombre_tra.max'            => 'El nombre no puede exceder los 100 caracteres.',
            'numero_orden_tra.required' => 'El número de orden es obligatorio.',
            'numero_orden_tra.integer'  => 'El número de orden debe ser un número entero.',
            'tpd_id.required'           => 'Debe seleccionar una modalidad académica (Tipo de Período).',
            'tpd_id.exists'             => 'La modalidad académica seleccionada no es válida.',
        ];
    }
}

