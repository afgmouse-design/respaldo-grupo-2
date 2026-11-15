<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoLapsoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tap_codigo' => 'required|integer|unique:tipo_lapsos,tap_codigo',
            'tap_nombre' => 'required|string|min:2|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'tap_codigo.required' => 'El código es requerido',
            'tap_codigo.integer' => 'El código debe ser numérico',
            'tap_codigo.unique' => 'El código ya está registrado',
            'tap_nombre.required' => 'El nombre es requerido',
            'tap_nombre.min' => 'El nombre debe tener al menos 2 caracteres',
            'tap_nombre.max' => 'El nombre no puede exceder 255 caracteres',
        ];
    }
}