<?php

namespace App\Services\ProgramacionAcademica;

use App\Models\ProgramacionAcademica\CargaAcademica;
use App\Models\ProgramacionAcademica\Maestros\Horario;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Servicio de Dominio: Motor Anti-Choque Académico y de Infraestructura.
 *
 * Propósito: Prevenir colisiones temporales simultáneas en el mismo Lapso Académico para:
 *  1) Docente por rol (`carga_academica.pep_id` -> `persona_perfil.id_pep`).
 *  2) Ambiente físico / Aula (`horario.aul_id`, optimizado con `idx_horario_anti_choque`).
 *  3) Sección académica (`carga_academica.sec_id`, opcional).
 *
 * Historial:
 *  - 2026-11-14: Refactorización completa desde `seccion_horario`/`seccion_unidad_docente` hacia
 *    el esquema oficial `horario` + `carga_academica` + `persona_perfil` + `persona` de `sogac_mio.sql`.
 *  - 2026-11-16: Corrección de error fatal PHP 8.3 ("Cannot access trait constant ... directly") al
 *    referenciar `Horario::STATUS_ACTIVO`, `CargaAcademica::STATUS_ACTIVO` y `Seccion::STATUS_ACTIVO`
 *    sobre sus clases modelo en lugar del trait `UsesStatusCatalog` directamente.
 */
class MotorAntiChoqueService
{
    /**
     * Mapa canónico de días numéricos (1..6) hacia etiqueta de texto en `horario.dia_semana_hor`.
     *
     * @var array<string, string>
     */
    private const MAPA_DIAS = [
        '1'         => 'Lunes',
        'lunes'     => 'Lunes',
        '2'         => 'Martes',
        'martes'    => 'Martes',
        '3'         => 'Miércoles',
        'miercoles' => 'Miércoles',
        'miércoles' => 'Miércoles',
        '4'         => 'Jueves',
        'jueves'    => 'Jueves',
        '5'         => 'Viernes',
        'viernes'   => 'Viernes',
        '6'         => 'Sábado',
        'sabado'    => 'Sábado',
        'sábado'    => 'Sábado',
        '7'         => 'Domingo',
        'domingo'   => 'Domingo',
    ];

    /**
     * Normaliza un día (1..6 o texto) a su etiqueta canónica (`Lunes` .. `Sábado`).
     */
    public function normalizarDiaSemana(int|string $dia): string
    {
        $clave = mb_strtolower(trim((string) $dia));

        return self::MAPA_DIAS[$clave] ?? trim((string) $dia);
    }

    /**
     * Retorna las variantes aceptadas (texto canónico, sin tilde y numérico) para comparación en BD.
     *
     * @return array<int, string>
     */
    private function variantesDiaSemana(int|string $dia): array
    {
        $canonico = $this->normalizarDiaSemana($dia);
        $variantes = [$canonico, trim((string) $dia)];

        foreach (self::MAPA_DIAS as $k => $v) {
            if ($v === $canonico && is_numeric($k)) {
                $variantes[] = $k;
            }
        }

        return array_values(array_unique($variantes));
    }

    /**
     * Normaliza una hora (`H:MM` / `HH:MM` -> `HH:MM:00`) para comparar campos `TIME(0)`.
     */
    private function normalizarHora(string $hora): string
    {
        $limpia = trim($hora);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $limpia, $m)) {
            return sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], isset($m[3]) ? (int) $m[3] : 0);
        }

        return strlen($limpia) === 5 ? "{$limpia}:00" : $limpia;
    }

    /**
     * Valida de forma atómica si el bloque horario propuesto colisiona en Docente, Aula o Sección.
     *
     * Acepta tanto `validarDisponibilidad(array $data, ?int $excludeHorarioId = null)` como la firma
     * posicional heredada `(int $lapsoId, int $docentePepId, int $aulaId, int|string $diaSemana, string $horaInicio, string $horaFin, ?int $seccionId = null, ?int $excludeHorarioId = null)`.
     *
     * @param  array<string, mixed>|int  $data
     * @throws ValidationException
     *
     * Historial:
     *  - 2026-11-16: Firma principal unificada `validarDisponibilidad(array $data, ?int $excludeHorarioId = null)`,
     *    resolución automática de carga académica por `caa_id`, lock consultivo atómico en PostgreSQL y reporte
     *    conjunto de colisiones (Docente, Aula, Sección).
     */
    public function validarDisponibilidad(
        array|int $data,
        ?int $docentePepId = null,
        ?int $aulaId = null,
        int|string|null $diaSemana = null,
        ?string $horaInicio = null,
        ?string $horaFin = null,
        ?int $seccionId = null,
        ?int $excludeHorarioId = null
    ): void {
        if (is_array($data)) {
            $excludeId = $docentePepId ?? (isset($data['exclude_horario_id']) ? (int) $data['exclude_horario_id'] : null);
            $caaId = ! empty($data['caa_id']) ? (int) $data['caa_id'] : null;
            $carga = null;

            if ($caaId !== null && (! isset($data['laa_id'], $data['pep_id'], $data['sec_id']) && ! isset($data['lapso_id']))) {
                $carga = CargaAcademica::with('seccion')->where('id_caa', $caaId)->whereNull('deleted_at')->first();
            }

            $lapsoId = (int) ($data['laa_id'] ?? $data['lapso_id'] ?? $carga?->seccion?->laa_id ?? 0);
            $pepIdResolved = (int) ($data['pep_id'] ?? $data['docente_pep_id'] ?? $carga?->pep_id ?? 0);
            $aulaIdResolved = (int) ($data['aul_id'] ?? $data['aula_id'] ?? 0);
            $secIdResolved = isset($data['sec_id'])
                ? (int) $data['sec_id']
                : (isset($data['seccion_id']) ? (int) $data['seccion_id'] : ($carga?->sec_id ? (int) $carga->sec_id : null));
            $diaResolved = $data['dia_semana_hor'] ?? $data['dia_semana'] ?? '';
            $inicioResolved = (string) ($data['hora_inicio_hor'] ?? $data['hora_inicio'] ?? '');
            $finResolved = (string) ($data['hora_fin_hor'] ?? $data['hora_fin'] ?? '');
        } else {
            $lapsoId = $data;
            $pepIdResolved = (int) $docentePepId;
            $aulaIdResolved = (int) $aulaId;
            $secIdResolved = $seccionId;
            $diaResolved = (string) $diaSemana;
            $inicioResolved = (string) $horaInicio;
            $finResolved = (string) $horaFin;
            $excludeId = $excludeHorarioId;
        }

        if ($lapsoId <= 0 || $pepIdResolved <= 0 || $aulaIdResolved <= 0 || $diaResolved === '' || $inicioResolved === '' || $finResolved === '') {
            throw ValidationException::withMessages([
                'horario' => 'Faltan parámetros requeridos (lapso, docente, aula, día o franja horaria) para validar choques.',
            ]);
        }

        $variantesDia = $this->variantesDiaSemana($diaResolved);
        $inicio = $this->normalizarHora($inicioResolved);
        $fin = $this->normalizarHora($finResolved);

        if ($fin <= $inicio) {
            throw ValidationException::withMessages([
                'hora_fin_hor' => 'La hora de culminación debe ser posterior a la hora de inicio.',
            ]);
        }

        $ejecutarValidacion = function () use (
            $lapsoId,
            $pepIdResolved,
            $aulaIdResolved,
            $secIdResolved,
            $variantesDia,
            $inicio,
            $fin,
            $excludeId
        ): void {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('SELECT pg_advisory_xact_lock(?, ?)', [7001, $lapsoId]);
            }

            $baseQuery = fn () => DB::table('horario as h')
                ->join('carga_academica as caa', 'caa.id_caa', '=', 'h.caa_id')
                ->join('seccion as sec', 'sec.id_sec', '=', 'caa.sec_id')
                ->join('unidad_curricular as unc', 'unc.id_unc', '=', 'caa.unc_id')
                ->where('sec.laa_id', $lapsoId)
                ->where('h.status_id', Horario::STATUS_ACTIVO)
                ->where('caa.status_id', CargaAcademica::STATUS_ACTIVO)
                ->where('sec.status_id', Seccion::STATUS_ACTIVO)
                ->whereNull('h.deleted_at')
                ->whereNull('caa.deleted_at')
                ->whereNull('sec.deleted_at')
                ->whereIn('h.dia_semana_hor', $variantesDia)
                ->where('h.hora_inicio_hor', '<', $fin)
                ->where('h.hora_fin_hor', '>', $inicio)
                ->when($excludeId, fn ($q) => $q->where('h.id_hor', '!=', $excludeId));

            $errores = [];

            // 1. Control Docente (vía carga_academica.pep_id -> persona_perfil.id_pep -> persona)
            $choqueDocente = $baseQuery()
                ->join('persona_perfil as pep', 'pep.id_pep', '=', 'caa.pep_id')
                ->join('persona as per', 'per.id_per', '=', 'pep.per_id')
                ->where('caa.pep_id', $pepIdResolved)
                ->select(
                    'h.hora_inicio_hor',
                    'h.hora_fin_hor',
                    'sec.nombre_sec',
                    'unc.nombre_unc',
                    DB::raw("TRIM(per.nombres_per || ' ' || per.apellidos_per) as docente_nombre")
                )
                ->first();

            if ($choqueDocente) {
                $ini = substr((string) $choqueDocente->hora_inicio_hor, 0, 5);
                $finChoque = substr((string) $choqueDocente->hora_fin_hor, 0, 5);
                $errores['pep_id'] = "Alerta de Choque Docente: {$choqueDocente->docente_nombre} ya tiene clase asignada de {$ini} a {$finChoque} con '{$choqueDocente->nombre_unc}' en la sección {$choqueDocente->nombre_sec}.";
            }

            // 2. Control de Espacio físico / Aula (usa idx_horario_anti_choque)
            $choqueAula = $baseQuery()
                ->join('aula as aul', 'aul.id_aul', '=', 'h.aul_id')
                ->where('h.aul_id', $aulaIdResolved)
                ->select(
                    'h.hora_inicio_hor',
                    'h.hora_fin_hor',
                    'aul.codigo_aula_aul',
                    'sec.nombre_sec',
                    'unc.nombre_unc'
                )
                ->first();

            if ($choqueAula) {
                $ini = substr((string) $choqueAula->hora_inicio_hor, 0, 5);
                $finChoque = substr((string) $choqueAula->hora_fin_hor, 0, 5);
                $errores['aul_id'] = "Alerta de Choque de Espacio: El ambiente {$choqueAula->codigo_aula_aul} ya está ocupado por la sección {$choqueAula->nombre_sec} ({$choqueAula->nombre_unc}) de {$ini} a {$finChoque}.";
            }

            // 3. Control de Sección (para evitar dos materias simultáneas al mismo grupo)
            if ($secIdResolved !== null) {
                $choqueSeccion = $baseQuery()
                    ->where('caa.sec_id', $secIdResolved)
                    ->select('h.hora_inicio_hor', 'h.hora_fin_hor', 'sec.nombre_sec', 'unc.nombre_unc')
                    ->first();

                if ($choqueSeccion) {
                    $ini = substr((string) $choqueSeccion->hora_inicio_hor, 0, 5);
                    $finChoque = substr((string) $choqueSeccion->hora_fin_hor, 0, 5);
                    $errores['sec_id'] = "Alerta de Choque de Sección: La sección {$choqueSeccion->nombre_sec} ya cursa '{$choqueSeccion->nombre_unc}' en el bloque de {$ini} a {$finChoque}.";
                }
            }

            if (! empty($errores)) {
                throw ValidationException::withMessages($errores);
            }
        };

        if (DB::transactionLevel() > 0) {
            $ejecutarValidacion();
        } else {
            DB::transaction($ejecutarValidacion);
        }
    }
}
