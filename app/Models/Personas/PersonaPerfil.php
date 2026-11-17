<?php

namespace App\Models\Personas;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\ProgramacionAcademica\CargaAcademica;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Persona-Perfil (tabla `persona_perfil`).
 *
 * Propósito: Asigna un Perfil (`perfil.id_prf`) a una Persona (`persona.id_per`).
 * Su clave primaria `id_pep` es el identificador obligatorio de rol operativo para docentes
 * en `carga_academica.pep_id`.
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql` (`id_pep`, `per_id`, `prf_id`, `status_id`, `SoftDeletes`).
 *  - 2026-11-16: Enriquecido `obtenerDocentesActivosParaSelect()` y agregado helper de horas asignadas
 *    por lapso con datos de `dedicacion_docente` (`nombre_ded`, `horas_academicas_ded`).
 */
class PersonaPerfil extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'persona_perfil';

    protected $primaryKey = 'id_pep';

    protected $moduloAuditoria = 'PersonaPerfil';

    protected $fillable = [
        'per_id',
        'prf_id',
        'fecha_inicio_pep',
        'fecha_fin_pep',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_pep'           => 'integer',
        'per_id'           => 'integer',
        'prf_id'           => 'integer',
        'fecha_inicio_pep' => 'date',
        'fecha_fin_pep'    => 'date',
        'status_id'        => 'integer',
    ];

    /**
     * Persona asociada a este rol.
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    /**
     * Perfil institucional asignado.
     */
    public function perfil(): BelongsTo
    {
        return $this->belongsTo(Perfil::class, 'prf_id', 'id_prf');
    }

    /**
     * Cargas académicas asignadas a este docente por perfil.
     */
    public function cargasAcademicas(): HasMany
    {
        return $this->hasMany(CargaAcademica::class, 'pep_id', 'id_pep');
    }

    /**
     * Scope para asignaciones activas (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Listado de docentes activos con su `id_pep`, dedicación laboral y carga horaria por lapso.
     */
    public static function obtenerDocentesActivosParaSelect(?int $laaId = null): Collection
    {
        $docentes = DB::table('persona_perfil as pep')
            ->join('persona as per', 'per.id_per', '=', 'pep.per_id')
            ->join('perfil as prf', 'prf.id_prf', '=', 'pep.prf_id')
            ->leftJoin('dedicacion_docente as ded', function ($join) {
                $join->on('ded.id_ded', '=', 'per.doc_ded_id')
                    ->whereNull('ded.deleted_at');
            })
            ->where('pep.status_id', self::STATUS_ACTIVO)
            ->whereNull('pep.deleted_at')
            ->where('per.status_id', self::STATUS_ACTIVO)
            ->whereNull('per.deleted_at')
            ->where('prf.status_id', self::STATUS_ACTIVO)
            ->whereNull('prf.deleted_at')
            ->where(function ($q) {
                $q->whereRaw('upper(prf.codigo_prf) = ?', ['DOCENTE'])
                    ->orWhere('prf.nombre_prf', 'ilike', '%docente%');
            })
            ->select(
                'pep.id_pep',
                'per.id_per',
                'per.cedula_per',
                'per.nombres_per',
                'per.apellidos_per',
                'per.correo_per',
                'per.doc_ded_id',
                'ded.nombre_ded as dedicacion_nombre',
                DB::raw('COALESCE(ded.horas_academicas_ded, 0)::integer as horas_maximas_ded'),
                DB::raw("TRIM(per.nombres_per || ' ' || per.apellidos_per) as nombre_completo")
            )
            ->orderBy('per.apellidos_per')
            ->orderBy('per.nombres_per')
            ->get();

        $horasPorDocenteYLapso = DB::table('carga_academica as caa')
            ->join('seccion as sec', 'sec.id_sec', '=', 'caa.sec_id')
            ->where('caa.status_id', self::STATUS_ACTIVO)
            ->whereNull('caa.deleted_at')
            ->whereNull('sec.deleted_at')
            ->where('sec.status_id', self::STATUS_ACTIVO)
            ->groupBy('caa.pep_id', 'sec.laa_id')
            ->select(
                'caa.pep_id',
                'sec.laa_id',
                DB::raw('COALESCE(SUM(COALESCE(caa.horas_semanales_caa, 0)), 0)::integer as total_horas')
            )
            ->get();

        $mapaHoras = [];
        foreach ($horasPorDocenteYLapso as $row) {
            $pepKey = (int) $row->pep_id;
            $lapsoKey = (int) $row->laa_id;
            $mapaHoras[$pepKey][$lapsoKey] = (int) $row->total_horas;
        }

        return $docentes->map(function ($d) use ($mapaHoras, $laaId) {
            $pepKey = (int) $d->id_pep;
            $horasPorLapso = $mapaHoras[$pepKey] ?? [];
            $d->horas_por_lapso = $horasPorLapso;
            $d->horas_asignadas_lapso = $laaId !== null ? ($horasPorLapso[$laaId] ?? 0) : 0;

            return $d;
        });
    }
}
