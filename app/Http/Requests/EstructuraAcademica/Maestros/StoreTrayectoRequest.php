<?php

namespace App\Http\Requests\EstructuraAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: Validación para registrar Trayecto (`trayecto`).
 *
 * Historial:
 *  - 2026-11-14: Migrado de `tpd_id -> tipo_periodo.id_tpd` hacia `tla_id -> tipo_lapso.id_tla`
 *    según `sogac_mio.sql`, manteniendo soporte transitorio de alias `tpd_id`.
 */
class StoreTrayectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('tla_id') && $this->filled('tpd_id')) {
            $this->merge(['tla_id' => $this->input('tpd_id')]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre_tra'       => ['required', 'string', 'max:100'],
            'codigo_tra'       => ['nullable', 'string', 'max:20'],
            'numero_orden_tra' => ['required', 'integer', 'min:0', 'max:50'],
            'tla_id'           => ['required', 'integer', Rule::exists('tipo_lapso', 'id_tla')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_tra.required'       => 'El nombre del trayecto es obligatorio.',
            'nombre_tra.max'            => 'El nombre no puede exceder los 100 caracteres.',
            'numero_orden_tra.required' => 'El número de orden es obligatorio.',
            'numero_orden_tra.integer'  => 'El número de orden debe ser un número entero.',
            'tla_id.required'           => 'Debe seleccionar un tipo de lapso académico.',
            'tla_id.exists'             => 'El tipo de lapso académico seleccionado no es válido.',
        ];
    }
}
