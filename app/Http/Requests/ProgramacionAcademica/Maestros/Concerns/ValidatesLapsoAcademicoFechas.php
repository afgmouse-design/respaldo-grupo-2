<?php

namespace App\Http\Requests\ProgramacionAcademica\Maestros\Concerns;

use App\Models\Academico\AnoAcademico;
use App\Models\Academico\LapsoAcademico;
use App\Models\Academico\TipoLapso;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

/**
 * Trait: ValidatesLapsoAcademicoFechas.
 *
 * Propósito: Centraliza la normalización de payload (soportando temporalmente alias legacy
 * mientras el frontend migra por completo) y valida que las fechas de un lapso académico
 * se encuentren dentro del rango vigente de su Año Académico padre (`ano_academico.id_ana`).
 *
 * Historial:
 *  - 2026-11-14: Creación para evitar duplicación entre StoreLapsoAcademicoRequest
 *    y UpdateLapsoAcademicoRequest tras alinear las columnas a `sogac_mio.sql`.
 *  - 2026-11-16: Corrección PHP 8.3 reemplazando acceso directo a constante del trait
 *    `UsesStatusCatalog` por `TipoLapso::STATUS_ACTIVO` y `LapsoAcademico::STATUS_*`.
 */
trait ValidatesLapsoAcademicoFechas
{
    /**
     * Normaliza alias de entrada hacia los nombres oficiales de `lapso_academico`.
     */
    protected function normalizarEntradaLapso(bool $esCreacion = false): void
    {
        $merge = [];

        if (! $this->filled('ana_id') && $this->filled('ano_id')) {
            $merge['ana_id'] = $this->input('ano_id');
        }

        if (! $this->filled('tla_id') && $this->filled('tpd_id')) {
            $merge['tla_id'] = $this->input('tpd_id');
        }

        if ($esCreacion && ! $this->filled('tla_id') && empty($merge['tla_id'])) {
            $tlaDefault = DB::table('tipo_lapso')
                ->where('status_id', TipoLapso::STATUS_ACTIVO)
                ->whereNull('deleted_at')
                ->orderBy('id_tla')
                ->value('id_tla');
            if ($tlaDefault !== null) {
                $merge['tla_id'] = $tlaDefault;
            }
        }

        $nombre = $this->input('nombre_laa') ?? $this->input('nombre_la') ?? $this->input('lap_nombre');
        if (! $this->filled('nombre_laa') && $nombre !== null && $nombre !== '') {
            $merge['nombre_laa'] = $nombre;
        }

        $inicio = $this->input('fecha_inicio_laa') ?? $this->input('fecha_inicio_la') ?? $this->input('lap_fecha_inicio');
        if (! $this->filled('fecha_inicio_laa') && $inicio !== null && $inicio !== '') {
            $merge['fecha_inicio_laa'] = $inicio;
        }

        $fin = $this->input('fecha_fin_laa') ?? $this->input('fecha_fin_la') ?? $this->input('lap_fecha_fin');
        if (! $this->filled('fecha_fin_laa') && $fin !== null && $fin !== '') {
            $merge['fecha_fin_laa'] = $fin;
        }

        if (! $this->filled('status_id')) {
            $statusInput = $this->input('estado_la', $this->input('status'));
            if ($statusInput !== null && $statusInput !== '') {
                $merge['status_id'] = filter_var($statusInput, FILTER_VALIDATE_BOOLEAN)
                    ? LapsoAcademico::STATUS_ACTIVO
                    : LapsoAcademico::STATUS_INACTIVO;
            }
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Comprueba que `fecha_inicio_laa` y `fecha_fin_laa` respeten los límites de `ano_academico`.
     */
    protected function validarRangoEnAnoAcademico(Validator $validator, ?int $anaId): void
    {
        $inicio = $this->input('fecha_inicio_laa');
        $fin = $this->input('fecha_fin_laa');

        if (! $anaId || (! $inicio && ! $fin)) {
            return;
        }

        $ano = AnoAcademico::obtenerPorId($anaId);
        if (! $ano) {
            return;
        }

        if (! empty($ano->fecha_inicio_ana) && ! empty($inicio) && $inicio < $ano->fecha_inicio_ana) {
            $validator->errors()->add(
                'fecha_inicio_laa',
                "La fecha de inicio del lapso ({$inicio}) no puede ser anterior al inicio del año académico padre ({$ano->fecha_inicio_ana})."
            );
        }

        if (! empty($ano->fecha_fin_ana) && ! empty($inicio) && $inicio > $ano->fecha_fin_ana) {
            $validator->errors()->add(
                'fecha_inicio_laa',
                "La fecha de inicio del lapso ({$inicio}) no puede superar la culminación del año académico padre ({$ano->fecha_fin_ana})."
            );
        }

        if (! empty($ano->fecha_fin_ana) && ! empty($fin) && $fin > $ano->fecha_fin_ana) {
            $validator->errors()->add(
                'fecha_fin_laa',
                "La fecha de culminación del lapso ({$fin}) no puede ser posterior a la culminación del año académico padre ({$ano->fecha_fin_ana})."
            );
        }

        if (! empty($ano->fecha_inicio_ana) && ! empty($fin) && $fin < $ano->fecha_inicio_ana) {
            $validator->errors()->add(
                'fecha_fin_laa',
                "La fecha de culminación del lapso ({$fin}) no puede ser anterior al inicio del año académico padre ({$ano->fecha_inicio_ana})."
            );
        }
    }
}
