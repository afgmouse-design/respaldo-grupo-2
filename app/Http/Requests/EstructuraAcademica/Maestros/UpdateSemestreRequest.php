<?php

namespace App\Http\Requests\EstructuraAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSemestreRequest extends FormRequest
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
        $semestresId = $this->route('semestre');

        return [
            'codigo' => [
                'required',
                'string',
                'min:3',
                'max:4',
            ],

            'nombre' => [
                'required',
                'string',
                'min:5',
                'max:50',
                Rule::unique('semestres', 'nombre')->ignore($semestresId)
            ],
            'numero' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],
            'cod_trayecto' => [
                'required',
                'string',
                'min:5',
                'max:20',
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es requerido',
            'codigo.min' => 'El codigo del semestre debe tener al menos 3 caracteres',
            'codigo.max' => 'El codigo del semestre no puede tener más de 4 caracteres',
            'nombre.required' => 'El nombre es requerido',
            'nombre.unique' => 'El nombre ya existe',
            'nombre.min' => 'El nombre debe tener al menos 5 caracteres',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'numero.required' => 'El número del semestre es requerido',
            'numero.integer' => 'El número debe ser un valor entero',
            'numero.min' => 'El número debe ser al menos 1',
            'numero.max' => 'El número debe tener máximo 2 dígitos',
            'cod_trayecto.required' => 'El código del trayecto es requerido',
            'cod_trayecto.min' => 'El código del trayecto debe tener al menos 5 caracteres',
            'cod_trayecto.max' => 'El código del trayecto no puede exceder 20 caracteres',

        ];
    }
}
