<?php

/**
 * Propósito: Valida la creación de registros en la tabla `semestre` conforme al esquema sogac_mio.
 * Historial: Reescrito para alinear claves y columnas con `semestre` (`tra_id`, `codigo_sem`, `nombre_sem`, `numero_sem`, `status_id`).
 */

namespace App\Http\Requests\EstructuraAcademica\Maestros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemestreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza alias heredados hacia las columnas oficiales de `semestre`.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'codigo_sem' => $this->input('codigo_sem', $this->input('codigo')),
            'nombre_sem' => $this->input('nombre_sem', $this->input('nombre')),
            'numero_sem' => $this->input('numero_sem', $this->input('numero')),
        ]);
    }

    public function rules(): array
    {
        return [
            'tra_id' => ['required', 'integer', Rule::exists('trayecto', 'id_tra')->whereNull('deleted_at')],
            'codigo_sem' => [
                'required',
                'string',
                'max:20',
                Rule::unique('semestre', 'codigo_sem')->whereNull('deleted_at'),
            ],
            'nombre_sem' => [
                'required',
                'string',
                'min:2',
                'max:50',
                Rule::unique('semestre', 'nombre_sem')->whereNull('deleted_at'),
            ],
            'numero_sem' => ['required', 'integer', 'min:1', 'max:99'],
            'status_id' => ['nullable', 'integer', 'exists:status,id_sta'],
        ];
    }

    public function messages(): array
    {
        return [
            'tra_id.required' => 'El trayecto asociado es obligatorio.',
            'tra_id.exists' => 'El trayecto seleccionado no existe o está eliminado.',
            'codigo_sem.required' => 'El código del semestre es obligatorio.',
            'codigo_sem.max' => 'El código del semestre no puede exceder los 20 caracteres.',
            'codigo_sem.unique' => 'Ya existe un semestre registrado con este código.',
            'nombre_sem.required' => 'El nombre del semestre es obligatorio.',
            'nombre_sem.max' => 'El nombre del semestre no puede exceder los 50 caracteres.',
            'nombre_sem.unique' => 'Ya existe un semestre registrado con este nombre.',
            'numero_sem.required' => 'El número ordinal del semestre es obligatorio.',
            'numero_sem.integer' => 'El número del semestre debe ser un entero positivo.',
        ];
    }
}
