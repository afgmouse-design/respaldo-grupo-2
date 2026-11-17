<?php

namespace App\Models\ProgramacionAcademica;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use App\Models\Personas\PersonaPerfil;
use App\Models\ProgramacionAcademica\Maestros\Horario;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Carga Académica (tabla `carga_academica`).
 *
 * Propósito: Vincula una Sección (`sec_id`), una Unidad Curricular (`unc_id`) y un Docente por
 * rol en `persona_perfil` (`pep_id` bigint NOT NULL). Es el nodo padre de `horario`, inscripciones y notas.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`pep_id` hacia `persona_perfil.id_pep` en lugar de `docente_per_id`;
 *    incorporación de campos aditivos `tipo_contratacion_caa` y `horas_semanales_caa`, `status_id` y `SoftDeletes`).
 *  - 2026-11-16: Se agrega `HandlesSoftDeleteWithStatus` y los validadores de dominio compartidos
 *    (`horasAsignadasDocenteEnLapso`, `validarReglasAsignacion`) para garantizar unicidad por sección/unidad
 *    y tope máximo de horas semanales por dedicación docente dentro de cada lapso académico.
 */
class CargaAcademica extends Model
{
    use HandlesSoftDeleteWithStatus;
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'carga_academica';

    protected $primaryKey = 'id_caa';

    protected $moduloAuditoria = 'CargaAcademica';

    protected $fillable = [
        'sec_id',
        'unc_id',
        'pep_id',
        'tipo_contratacion_caa',
        'horas_semanales_caa',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_caa'              => 'integer',
        'sec_id'              => 'integer',
        'unc_id'              => 'integer',
        'pep_id'              => 'integer',
        'horas_semanales_caa' => 'integer',
        'status_id'           => 'integer',
    ];

    /**
     * Sección académica a la que pertenece la carga.
     */
    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'sec_id', 'id_sec');
    }

    /**
     * Unidad Curricular asignada.
     */
    public function unidadCurricular(): BelongsTo
    {
        return $this->belongsTo(UnidadCurricular::class, 'unc_id', 'id_unc');
    }

    /**
     * Perfil docente asignado (`pep_id` -> `persona_perfil.id_pep`).
     */
    public function docentePerfil(): BelongsTo
    {
        return $this->belongsTo(PersonaPerfil::class, 'pep_id', 'id_pep');
    }

    /**
     * Alias de conveniencia hacia el perfil docente (`persona_perfil`).
     */
    public function docente(): BelongsTo
    {
        return $this->docentePerfil();
    }

    /**
     * Bloques horarios asignados a esta carga académica.
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'caa_id', 'id_caa');
    }

    /**
     * Scope para cargas activas (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Suma las horas semanales activas ya asignadas a un docente dentro de un lapso académico específico.
     * Excluye la carga actual cuando se pasa `$ignoreCaaId` (edición / reactivación).
     */
    public static function horasAsignadasDocenteEnLapso(int $pepId, int $laaId, ?int $ignoreCaaId = null): int
    {
        return (int) self::query()
            ->join('seccion', 'seccion.id_sec', '=', 'carga_academica.sec_id')
            ->where('carga_academica.pep_id', $pepId)
            ->where('carga_academica.status_id', self::STATUS_ACTIVO)
            ->whereNull('carga_academica.deleted_at')
            ->where('seccion.laa_id', $laaId)
            ->whereNull('seccion.deleted_at')
            ->when($ignoreCaaId !== null, fn (Builder $query) => $query->where('carga_academica.id_caa', '!=', $ignoreCaaId))
            ->sum('carga_academica.horas_semanales_caa');
    }

    /**
     * Valida las reglas de negocio de una asignación Sección - Unidad Curricular - Docente (SUD):
     *  1. Sección existente y activa.
     *  2. Unidad Curricular existente y activa.
     *  3. Unicidad compuesta `(sec_id, unc_id)` entre registros no eliminados.
     *  4. Docente activo (perfil `DOCENTE` activo + persona activa + dedicación laboral válida).
     *  5. Respeto al tope máximo de horas académicas semanales de la dedicación docente en el lapso de la sección.
     *
     * @return array{
     *   errors: array<string, string>,
     *   seccion: Seccion|null,
     *   docentePerfil: PersonaPerfil|null,
     *   horasActualesLapso: int,
     *   horasMaximasDedicacion: int
     * }
     */
    public static function validarReglasAsignacion(
        int $secId,
        int $uncId,
        int $pepId,
        int $horasSemanales,
        ?int $ignoreCaaId = null,
        int $statusIdObjetivo = self::STATUS_ACTIVO
    ): array {
        $errors = [];

        $seccion = Seccion::query()
            ->where('id_sec', $secId)
            ->whereNull('deleted_at')
            ->first();

        if (! $seccion) {
            $errors['sec_id'] = 'La sección seleccionada no existe o fue eliminada.';
        } elseif ((int) $seccion->status_id !== self::STATUS_ACTIVO) {
            $errors['sec_id'] = 'La sección seleccionada debe estar activa para asignarle carga académica.';
        }

        $unidad = UnidadCurricular::query()
            ->where('id_unc', $uncId)
            ->whereNull('deleted_at')
            ->first();

        if (! $unidad || (int) $unidad->status_id !== self::STATUS_ACTIVO) {
            $errors['unc_id'] = 'La unidad curricular seleccionada no existe o no se encuentra activa.';
        }

        $duplicada = self::query()
            ->where('sec_id', $secId)
            ->where('unc_id', $uncId)
            ->whereNull('deleted_at')
            ->when($ignoreCaaId !== null, fn (Builder $query) => $query->where('id_caa', '!=', $ignoreCaaId))
            ->exists();

        if ($duplicada) {
            $errors['unc_id'] = 'Esta unidad curricular ya posee un docente asignado en la sección seleccionada.';
        }

        $docentePerfil = PersonaPerfil::query()
            ->with(['persona.dedicacionDocente', 'perfil'])
            ->where('id_pep', $pepId)
            ->whereNull('deleted_at')
            ->first();

        $horasActualesLapso = 0;
        $horasMaximasDedicacion = 0;

        $perfilEsDocente = $docentePerfil?->perfil !== null
            && $docentePerfil->perfil->deleted_at === null
            && (int) $docentePerfil->perfil->status_id === self::STATUS_ACTIVO
            && strtoupper((string) $docentePerfil->perfil->codigo_prf) === 'DOCENTE';

        $personaActiva = $docentePerfil?->persona !== null
            && $docentePerfil->persona->deleted_at === null
            && (int) $docentePerfil->persona->status_id === self::STATUS_ACTIVO;

        if (! $docentePerfil || (int) $docentePerfil->status_id !== self::STATUS_ACTIVO || ! $perfilEsDocente || ! $personaActiva) {
            $errors['pep_id'] = 'El docente seleccionado no se encuentra activo o no posee el perfil DOCENTE habilitado.';

            return [
                'errors'                 => $errors,
                'seccion'                => $seccion,
                'docentePerfil'          => $docentePerfil,
                'horasActualesLapso'     => $horasActualesLapso,
                'horasMaximasDedicacion' => $horasMaximasDedicacion,
            ];
        }

        $dedicacion = $docentePerfil->persona->dedicacionDocente;

        if (! $dedicacion || $dedicacion->deleted_at !== null || (int) $dedicacion->status_id !== self::STATUS_ACTIVO) {
            $errors['pep_id'] = 'El docente seleccionado no tiene una dedicación laboral activa asignada.';

            return [
                'errors'                 => $errors,
                'seccion'                => $seccion,
                'docentePerfil'          => $docentePerfil,
                'horasActualesLapso'     => $horasActualesLapso,
                'horasMaximasDedicacion' => $horasMaximasDedicacion,
            ];
        }

        $horasMaximasDedicacion = (int) ($dedicacion->horas_academicas_ded ?? 0);

        if ($seccion && $seccion->laa_id !== null) {
            $horasActualesLapso = self::horasAsignadasDocenteEnLapso($pepId, (int) $seccion->laa_id, $ignoreCaaId);
        }

        if ($statusIdObjetivo === self::STATUS_ACTIVO && ($horasActualesLapso + $horasSemanales) > $horasMaximasDedicacion) {
            $totalProyectado = $horasActualesLapso + $horasSemanales;
            $nombreDocente = trim(($docentePerfil->persona->nombres_per ?? '') . ' ' . ($docentePerfil->persona->apellidos_per ?? ''));
            $errors['horas_semanales_caa'] = sprintf(
                'El docente %s excede su dedicación "%s" (%dh máx./semana en el lapso). Carga actual: %dh; asignando: %dh (total: %dh).',
                $nombreDocente !== '' ? $nombreDocente : 'seleccionado',
                $dedicacion->nombre_ded,
                $horasMaximasDedicacion,
                $horasActualesLapso,
                $horasSemanales,
                $totalProyectado
            );
        }

        return [
            'errors'                 => $errors,
            'seccion'                => $seccion,
            'docentePerfil'          => $docentePerfil,
            'horasActualesLapso'     => $horasActualesLapso,
            'horasMaximasDedicacion' => $horasMaximasDedicacion,
        ];
    }
}
