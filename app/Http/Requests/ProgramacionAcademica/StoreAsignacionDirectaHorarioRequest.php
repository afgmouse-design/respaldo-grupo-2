<?php

namespace App\Http\Requests\ProgramacionAcademica;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request: StoreAsignacionDirectaHorarioRequest
 * 
 * Propósito: Validación estricta para la asignación directa de horarios por bloque.
 * Garantiza que una clase nazca con Sección, Unidad Curricular, Docente, Aula y Bloque temporal.
 */
class StoreAsignacionDirectaHorarioRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la asignación integrada.
     */
    public function rules(): array
    {
        return [
            'sec_id'      => ['required', 'integer', 'exists:seccion,id_sec'],
            'mcd_id'      => ['required', 'integer', 'exists:malla_curricular_detalle,id_mcd'],
            'docente_id'  => ['required', 'integer', 'exists:users,id'],
            'aul_id'      => ['required', 'integer', 'exists:aula,id_aul'],
            'dia_semana'  => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin'    => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo_sesion' => ['required', 'string', 'in:Teoría,Práctica,Laboratorio,Taller,Tutoría'],
        ];
    }

    /**
     * Mensajes descriptivos de error.
     */
    public function messages(): array
    {
        return [
            'sec_id.required'      => 'Debe seleccionar una sección académica válida.',
            'sec_id.exists'        => 'La sección seleccionada no existe en el sistema.',
            'mcd_id.required'      => 'Debe especificar la unidad curricular de la malla.',
            'mcd_id.exists'        => 'La materia seleccionada no pertenece a la malla curricular.',
            'docente_id.required'  => 'Restricción Obligatoria: Es indispensable vincular al docente responsable por cédula.',
            'docente_id.exists'    => 'El docente seleccionado no está registrado en el sistema.',
            'aul_id.required'      => 'Debe seleccionar el aula, laboratorio o taller para la sesión.',
            'aul_id.exists'        => 'El aula seleccionada no existe.',
            'dia_semana.required'  => 'Debe seleccionar el día de la semana de la sesión.',
            'hora_inicio.required' => 'Indique la hora de inicio de la clase.',
            'hora_fin.required'    => 'Indique la hora de culminación de la clase.',
            'hora_fin.after'       => 'La hora de culminación debe ser posterior a la hora de inicio.',
            'tipo_sesion.required' => 'Especifique si la clase es de Teoría, Práctica, Laboratorio o Taller.',
        ];
    }
}

