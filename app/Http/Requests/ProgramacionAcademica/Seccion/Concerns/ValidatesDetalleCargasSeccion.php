<?php

namespace App\Http\Requests\ProgramacionAcademica\Seccion\Concerns;

use Illuminate\Validation\Rule;

/**
 * Trait: Normaliza y valida el arreglo de detalle `cargas` (unidades curriculares y docentes)
 * para las solicitudes atómicas de `SeccionUnificadaController`.
 *
 * Propósito: Evitar duplicación entre `StoreSeccionUnificadaRequest` y `UpdateSeccionUnificadaRequest`.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial para guardar cabecera `seccion` + detalle `carga_academica`.
 */
trait ValidatesDetalleCargasSeccion
{
    /**
     * Normaliza alias `detalles` / `unidades_docentes` hacia `cargas` antes de validar.
     */
    protected function prepararDetalleCargas(): void
    {
        if (! $this->has('cargas')) {
            if ($this->has('detalles')) {
                $this->merge(['cargas' => $this->input('detalles')]);
            } elseif ($this->has('unidades_docentes')) {
                $this->merge(['cargas' => $this->input('unidades_docentes')]);
            }
        }
    }

    /**
     * Reglas de validación estructural del arreglo de detalle de carga académica.
     *
     * @return array<string, mixed>
     */
    protected function reglasDetalleCargas(): array
    {
        return [
            'cargas'                         => ['nullable', 'array'],
            'cargas.*.unc_id'                => ['required', 'integer', 'distinct', Rule::exists('unidad_curricular', 'id_unc')->whereNull('deleted_at')],
            'cargas.*.pep_id'                => ['required', 'integer', Rule::exists('persona_perfil', 'id_pep')->whereNull('deleted_at')],
            'cargas.*.horas_semanales_caa'   => ['nullable', 'integer', 'min:1', 'max:40'],
            'cargas.*.tipo_contratacion_caa' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Mensajes descriptivos en español para errores sobre filas de detalle.
     *
     * @return array<string, string>
     */
    protected function mensajesDetalleCargas(): array
    {
        return [
            'cargas.array'                   => 'El detalle de unidades y docentes debe enviarse como una lista válida.',
            'cargas.*.unc_id.required'       => 'Cada fila del detalle debe indicar una unidad curricular.',
            'cargas.*.unc_id.distinct'       => 'No puede repetir la misma unidad curricular dentro de la misma sección.',
            'cargas.*.unc_id.exists'         => 'Una de las unidades curriculares seleccionadas no existe.',
            'cargas.*.pep_id.required'       => 'Cada fila del detalle debe indicar un docente.',
            'cargas.*.pep_id.exists'         => 'Uno de los perfiles docentes seleccionados no existe.',
            'cargas.*.horas_semanales_caa.min' => 'Las horas semanales asignadas por materia deben ser al menos 1.',
            'cargas.*.horas_semanales_caa.max' => 'Las horas semanales asignadas por materia no pueden superar 40.',
        ];
    }
}
