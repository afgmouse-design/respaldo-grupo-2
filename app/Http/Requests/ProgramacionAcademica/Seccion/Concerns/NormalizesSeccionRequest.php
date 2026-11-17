<?php

/**
 * Propósito: Normaliza alias heredados y centraliza las reglas de validación compartidas para `Seccion`.
 * Historial: Creado para eliminar duplicación entre `StoreSeccionRequest` y `UpdateSeccionRequest`, alineando FKs a `lapso_academico.id_laa`, `programa.id_prg`, `sede.id_sed`, `tipo_seccion.id_tis` y `turno.id_tur`.
 */

namespace App\Http\Requests\ProgramacionAcademica\Seccion\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Propósito: Normaliza alias heredados y centraliza las reglas de validación compartidas para `Seccion`.
 * Historial:
 *  - Creado para eliminar duplicación entre `StoreSeccionRequest` y `UpdateSeccionRequest`, alineando FKs a `lapso_academico.id_laa`, `programa.id_prg`, `sede.id_sed`, `tipo_seccion.id_tis` y `turno.id_tur`.
 *  - 2026-11-16: Refuerza la unicidad compuesta de `nombre_sec` por `(laa_id, sed_id)` ignorando registros
 *    con `deleted_at IS NOT NULL` de forma insensible a mayúsculas/minúsculas y espacios.
 */
trait NormalizesSeccionRequest
{
    /**
     * Normaliza `pnf_id` -> `prg_id` y recorta espacios de `nombre_sec` / `codigo_sec` antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->filled('prg_id') && $this->filled('pnf_id')) {
            $merge['prg_id'] = $this->input('pnf_id');
        }

        if ($this->has('nombre_sec') && is_string($this->input('nombre_sec'))) {
            $merge['nombre_sec'] = trim($this->input('nombre_sec'));
        }

        if ($this->has('codigo_sec') && is_string($this->input('codigo_sec'))) {
            $merge['codigo_sec'] = trim($this->input('codigo_sec'));
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Reglas compartidas de creación y actualización de Sección.
     */
    protected function reglasSeccion(?int $ignoreId = null): array
    {
        $uniqueNombre = Rule::unique('seccion', 'nombre_sec')
            ->where(fn ($q) => $q->where('laa_id', $this->input('laa_id'))
                ->where('sed_id', $this->input('sed_id'))
                ->whereNull('deleted_at'));

        if ($ignoreId !== null) {
            $uniqueNombre->ignore($ignoreId, 'id_sec');
        }

        return [
            'codigo_sec'      => ['nullable', 'string', 'max:50'],
            'nombre_sec'      => ['required', 'string', 'min:2', 'max:255', $uniqueNombre],
            'tis_id'          => ['nullable', 'integer', Rule::exists('tipo_seccion', 'id_tis')->whereNull('deleted_at')],
            'tur_id'          => ['nullable', 'integer', Rule::exists('turno', 'id_tur')->whereNull('deleted_at')],
            'tipo_seccion'    => ['required_without:tis_id', 'nullable', 'string', Rule::in(['Regular', 'PER', 'Repitiente', 'Dirigido', 'Intensivo'])],
            'nivel_sec'       => ['nullable', 'integer', 'min:0', 'max:10'],
            'semestre_sec'    => ['required', 'integer', 'min:1', 'max:12'],
            'trayecto_sec'    => ['required', 'integer', 'min:0', 'max:6'],
            'turno_sec'       => ['required_without:tur_id', 'nullable', 'string', Rule::in(['Mañana', 'Tarde', 'Noche', 'Matutino', 'Vespertino', 'Nocturno'])],
            'cupo_maximo_sec' => ['required', 'integer', 'min:1', 'max:200'],
            'laa_id'          => ['required', 'integer', Rule::exists('lapso_academico', 'id_laa')->whereNull('deleted_at')],
            'prg_id'          => ['required', 'integer', Rule::exists('programa', 'id_prg')->whereNull('deleted_at')],
            'sed_id'          => ['required', 'integer', Rule::exists('sede', 'id_sed')->whereNull('deleted_at')],
            'cos_id'          => ['nullable', 'integer', Rule::exists('cohorte_sede', 'id_cos')->whereNull('deleted_at')],
            'mac_id'          => ['nullable', 'integer', Rule::exists('malla_curricular', 'id_mac')->whereNull('deleted_at')],
        ];
    }

    /**
     * Valida unicidad compuesta insensible a mayúsculas `(nombre_sec, laa_id, sed_id)` ignorando `deleted_at IS NOT NULL`.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->has('nombre_sec') || $v->errors()->has('laa_id') || $v->errors()->has('sed_id')) {
                return;
            }

            $nombre = trim((string) $this->input('nombre_sec', ''));
            $laaId = (int) $this->input('laa_id', 0);
            $sedId = (int) $this->input('sed_id', 0);

            if ($nombre === '' || $laaId <= 0 || $sedId <= 0) {
                return;
            }

            $param = $this->route('seccion');
            $ignoreId = is_object($param)
                ? (int) ($param->id_sec ?? $param->getKey())
                : ($param ? (int) $param : null);

            $duplicada = DB::table('seccion')
                ->where('laa_id', $laaId)
                ->where('sed_id', $sedId)
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(TRIM(nombre_sec)) = LOWER(?)', [$nombre])
                ->when($ignoreId !== null && $ignoreId > 0, fn ($q) => $q->where('id_sec', '!=', $ignoreId))
                ->exists();

            if ($duplicada) {
                $v->errors()->add('nombre_sec', 'Ya existe una sección activa con este nombre para el lapso y sede seleccionados.');
            }
        });
    }

    /**
     * Mensajes descriptivos compartidos para Sección.
     */
    public function messages(): array
    {
        return [
            'nombre_sec.required'         => 'El nombre de la sección es obligatorio.',
            'nombre_sec.unique'           => 'Ya existe una sección con este nombre para el lapso y sede seleccionados.',
            'tipo_seccion.required_without' => 'El tipo de sección es obligatorio.',
            'semestre_sec.required'       => 'El semestre es obligatorio.',
            'trayecto_sec.required'       => 'El trayecto es obligatorio.',
            'turno_sec.required_without'  => 'El turno es obligatorio.',
            'cupo_maximo_sec.required'    => 'El cupo máximo es obligatorio.',
            'laa_id.required'             => 'Debes seleccionar un lapso académico.',
            'laa_id.exists'               => 'El lapso académico seleccionado no existe.',
            'prg_id.required'             => 'Debes seleccionar un programa académico.',
            'prg_id.exists'               => 'El programa académico seleccionado no existe.',
            'sed_id.required'             => 'Debes seleccionar una sede.',
            'sed_id.exists'               => 'La sede seleccionada no existe.',
        ];
    }
}
