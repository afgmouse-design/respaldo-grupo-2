<?php

/**
 * Propósito: Valida la asignación directa integrada de Carga Académica + Horario (`carga_academica` + `horario`).
 * Historial: Actualizado para exigir `pep_id` (`persona_perfil.id_pep` en lugar de `users.id`), aceptar `unc_id` o `mcd_id` y `dia_semana` numérico o textual.
 */

namespace App\Http\Requests\ProgramacionAcademica;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAsignacionDirectaHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza alias `docente_pep_id` / `tipo_sesion_hor` hacia las claves canónicas de asignación.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        if ($this->filled('caa_id') && (! $this->filled('sec_id') || ! $this->filled('pep_id'))) {
            $carga = \App\Models\ProgramacionAcademica\CargaAcademica::where('id_caa', (int) $this->input('caa_id'))
                ->whereNull('deleted_at')
                ->first();
            if ($carga) {
                $merge['sec_id'] = $this->input('sec_id', $carga->sec_id);
                $merge['unc_id'] = $this->input('unc_id', $carga->unc_id);
                $merge['pep_id'] = $this->input('pep_id', $carga->pep_id);
            }
        }
        if (!$this->filled('pep_id') && $this->filled('docente_pep_id')) {
            $merge['pep_id'] = $this->input('docente_pep_id');
        }
        if (!$this->filled('tipo_sesion') && $this->filled('tipo_sesion_hor')) {
            $merge['tipo_sesion'] = $this->input('tipo_sesion_hor');
        }
        if (!$this->filled('horas_semanales_caa') && $this->filled('horas_semanales')) {
            $merge['horas_semanales_caa'] = $this->input('horas_semanales');
        }
        if (!$this->filled('tipo_contratacion_caa') && $this->filled('tipo_contratacion')) {
            $merge['tipo_contratacion_caa'] = $this->input('tipo_contratacion');
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'caa_id'                => ['nullable', 'integer', Rule::exists('carga_academica', 'id_caa')->whereNull('deleted_at')],
            'sec_id'                => ['required', 'integer', Rule::exists('seccion', 'id_sec')->whereNull('deleted_at')],
            'unc_id'                => ['required_without:mcd_id', 'nullable', 'integer', Rule::exists('unidad_curricular', 'id_unc')->whereNull('deleted_at')],
            'mcd_id'                => ['required_without:unc_id', 'nullable', 'integer', Rule::exists('malla_curricular_detalle', 'id_mcd')->whereNull('deleted_at')],
            'pep_id'                => ['required', 'integer', Rule::exists('persona_perfil', 'id_pep')->whereNull('deleted_at')],
            'aul_id'                => ['required', 'integer', Rule::exists('aula', 'id_aul')->whereNull('deleted_at')],
            'dia_semana'            => ['required'],
            'hora_inicio'           => ['required', 'date_format:H:i'],
            'hora_fin'              => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo_sesion'           => ['required', 'string', 'max:50', Rule::in(['Teoría', 'Teorica', 'Práctica', 'Practica', 'Laboratorio', 'Practica/Lab', 'Taller', 'Tutoría'])],
            'horas_semanales_caa'   => ['nullable', 'integer', 'min:1', 'max:40'],
            'tipo_contratacion_caa' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'sec_id.required'           => 'Debe seleccionar una sección académica válida.',
            'sec_id.exists'             => 'La sección seleccionada no existe en el sistema.',
            'unc_id.required_without'   => 'Debe especificar la unidad curricular o su detalle en la malla.',
            'mcd_id.required_without'   => 'Debe especificar la unidad curricular de la malla.',
            'pep_id.required'           => 'Es indispensable seleccionar el docente (perfil docente válido).',
            'pep_id.exists'             => 'El perfil docente seleccionado no existe o está inactivo.',
            'aul_id.required'           => 'Debe seleccionar el aula, laboratorio o taller para la sesión.',
            'aul_id.exists'             => 'El aula seleccionada no existe.',
            'dia_semana.required'       => 'Debe seleccionar el día de la semana de la sesión.',
            'hora_inicio.required'      => 'Indique la hora de inicio de la clase.',
            'hora_fin.required'         => 'Indique la hora de culminación de la clase.',
            'hora_fin.after'            => 'La hora de culminación debe ser posterior a la hora de inicio.',
            'tipo_sesion.required'      => 'Especifique el tipo de sesión de clase.',
        ];
    }
}
