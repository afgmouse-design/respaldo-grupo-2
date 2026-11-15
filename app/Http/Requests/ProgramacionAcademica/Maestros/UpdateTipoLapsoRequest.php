<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoLapsoRequest extends FormRequest
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
        $tipoLapsoId = $this->route('tipo_lapso')->id ?? null;

        return [
            'tap_codigo' => 'required|integer|unique:tipo_lapsos,tap_codigo,' . $tipoLapsoId,
            'tap_nombre' => 'required|string|min:2|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'tap_codigo.required' => 'El código es requerido',
            'tap_codigo.integer' => 'El código debe ser numérico',
            'tap_codigo.unique' => 'El código ya está registrado en otro tipo de lapso',
            'tap_nombre.required' => 'El nombre es requerido',
            'tap_nombre.min' => 'El nombre debe tener al menos 2 caracteres',
            'tap_nombre.max' => 'El nombre no puede exceder 255 caracteres',
        ];
    }
}