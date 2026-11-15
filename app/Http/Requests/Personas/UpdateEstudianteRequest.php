<?php

namespace App\Http\Requests\Personas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstudianteRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $perCedula = $this->route('per_cedula') ?? $this->per_cedula;

        return [
            'per_cedula' => [
                'required',
                'string',
                'max:15',
                Rule::unique('persona', 'per_cedula')->ignore($perCedula, 'per_cedula'),
            ],
            'per_documento' => 'nullable|string|max:20',
            'per_tipo_documento' => 'nullable|string|max:1',
            'per_documento_alternativo' => 'nullable|string|max:50',
            'per_nacionalidad' => 'nullable|string|max:2',
            'per_primer_nombre' => 'required|string|max:100',
            'per_segundo_nombre' => 'nullable|string|max:100',
            'per_primer_apellido' => 'required|string|max:100',
            'per_segundo_apellido' => 'nullable|string|max:100',
            'per_genero' => 'nullable|string|max:1',
            'per_sexo' => 'nullable|string|max:1',
            'per_edo_civil' => 'nullable|string|max:1',
            'per_fecha_nacimiento' => 'required|date',
            'per_email_local' => 'nullable|string|max:100',
            'per_email_domain' => 'nullable|string|max:100',
            'per_email_personal' => 'nullable|string|max:150',
            'per_telefono_movil_codigo' => 'nullable|string|max:4',
            'per_telefono_movil_numero' => 'nullable|string|max:15',
            'per_telefono_fijo_codigo' => 'nullable|string|max:4',
            'per_telefono_fijo_numero' => 'nullable|string|max:15',
            'per_telefono_emergencia' => 'nullable|string|max:20',
            'per_direccion_habitacion' => 'nullable|string',
            'per_pais_nacimiento' => 'nullable|string|max:100',
            'per_estado_nacimiento' => 'nullable|string|max:100',
            'per_estado_residencia' => 'nullable|string|max:100',
            'per_municipio_residencia' => 'nullable|string|max:100',
            'per_parroquia_residencia' => 'nullable|string|max:100',
            'per_cod_etnia' => 'nullable|string|max:10',
            'per_estatus' => 'nullable|string|max:20',
            'est_bachiller_ano_egreso' => 'nullable|string|max:4',
            'est_bachiller_dependencia' => 'nullable|string|max:50',
            'est_bachiller_estado' => 'nullable|string|max:100',
            'est_bachiller_municipio' => 'nullable|string|max:100',
            'est_bachiller_parroquia' => 'nullable|string|max:100',
            'est_bachiller_localidad' => 'nullable|string|max:100',
            'est_cod_seb' => 'nullable|string|max:50',
            'est_cod_reb' => 'nullable|string|max:50',
            'id_programa' => 'nullable|string|max:50',
            'id_sede' => 'nullable|string|max:50',
            'rol_en_programa' => 'nullable|string|max:30',
            'est_condicion' => 'nullable|string|max:30',
            'est_ira' => 'nullable|numeric',
            'est_creditos_aprobados' => 'nullable|integer',
            'est_culminacion_aca' => 'nullable|boolean',
            'per_foto_archivo' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'per_cedula.required' => 'La cédula o documento de identidad es obligatorio.',
            'per_cedula.unique' => 'Ya existe otro estudiante registrado con esta cédula.',
            'per_primer_nombre.required' => 'El primer nombre es obligatorio.',
            'per_primer_apellido.required' => 'El primer apellido es obligatorio.',
            'per_fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
        ];
    }
}
