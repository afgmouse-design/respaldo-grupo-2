<?php

namespace App\Http\Requests\ProgramacionAcademica\CargaAcademica\Concerns;

use App\Models\ProgramacionAcademica\CargaAcademica;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Trait: Reglas y validaciones compartidas para asignaciones Sección - Unidad - Docente (`carga_academica`).
 *
 * Propósito: Centraliza las reglas de esquema, normalización de alias y validación de dominio
 * (unicidad `(sec_id, unc_id)`, docente activo y tope de horas semanales según dedicación en el lapso).
 *
 * Historial:
 *  - 2026-11-16: Creación inicial para reutilización entre Store y Update de CargaAcademica (SUD).
 */
trait ValidatesCargaAcademicaRequest
{
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->filled('pep_id') && $this->filled('docente_pep_id')) {
            $merge['pep_id'] = $this->input('docente_pep_id');
        }

        if (! $this->filled('tipo_contratacion_caa') && $this->filled('tipo_contratacion')) {
            $merge['tipo_contratacion_caa'] = $this->input('tipo_contratacion');
        }

        if (! $this->filled('horas_semanales_caa') && $this->filled('horas_semanales')) {
            $merge['horas_semanales_caa'] = $this->input('horas_semanales');
        }

        if ($this->filled('tipo_contratacion_caa')) {
            $merge['tipo_contratacion_caa'] = trim((string) $this->input('tipo_contratacion_caa'));
        } else {
            $merge['tipo_contratacion_caa'] = 'Titular';
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Reglas estructurales base de la asignación SUD.
     *
     * @return array<string, mixed>
     */
    protected function reglasCargaAcademica(): array
    {
        return [
            'sec_id'                => ['required', 'integer', Rule::exists('seccion', 'id_sec')->whereNull('deleted_at')],
            'unc_id'                => ['required', 'integer', Rule::exists('unidad_curricular', 'id_unc')->whereNull('deleted_at')],
            'pep_id'                => ['required', 'integer', Rule::exists('persona_perfil', 'id_pep')->whereNull('deleted_at')],
            'tipo_contratacion_caa' => ['required', 'string', 'max:50'],
            'horas_semanales_caa'   => ['required', 'integer', 'min:1', 'max:40'],
        ];
    }

    /**
     * Mensajes de validación en español orientados al usuario.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sec_id.required'                => 'Debe seleccionar una sección.',
            'sec_id.exists'                  => 'La sección seleccionada no existe o fue eliminada.',
            'unc_id.required'                => 'Debe seleccionar una unidad curricular.',
            'unc_id.exists'                  => 'La unidad curricular seleccionada no existe o fue eliminada.',
            'pep_id.required'                => 'Debe seleccionar un docente.',
            'pep_id.exists'                  => 'El perfil docente seleccionado no existe o fue eliminado.',
            'tipo_contratacion_caa.required' => 'El tipo de contratación es obligatorio.',
            'tipo_contratacion_caa.max'      => 'El tipo de contratación no debe superar los 50 caracteres.',
            'horas_semanales_caa.required'   => 'Las horas semanales son obligatorias.',
            'horas_semanales_caa.integer'    => 'Las horas semanales deben ser un número entero.',
            'horas_semanales_caa.min'        => 'Debe asignar al menos 1 hora semanal.',
            'horas_semanales_caa.max'        => 'Las horas semanales por unidad curricular no pueden exceder de 40.',
        ];
    }

    /**
     * Ejecuta las validaciones de dominio en `CargaAcademica::validarReglasAsignacion`.
     */
    protected function aplicarValidacionDominio(Validator $validator, ?int $ignoreCaaId = null, int $statusObjetivo = CargaAcademica::STATUS_ACTIVO): void
    {
        $validator->after(function (Validator $v) use ($ignoreCaaId, $statusObjetivo) {
            if ($v->errors()->any()) {
                return;
            }

            $resultado = CargaAcademica::validarReglasAsignacion(
                (int) $this->input('sec_id'),
                (int) $this->input('unc_id'),
                (int) $this->input('pep_id'),
                (int) $this->input('horas_semanales_caa'),
                $ignoreCaaId,
                $statusObjetivo
            );

            foreach ($resultado['errors'] as $campo => $mensaje) {
                $v->errors()->add($campo, $mensaje);
            }
        });
    }
}
