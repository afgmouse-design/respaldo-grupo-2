<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\ProgramacionAcademica\CargaAcademica;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Horario de Clases (tabla `horario`).
 *
 * Propósito: Define los bloques horarios de encuentro presencial/virtual vinculados
 * a una Carga Académica (`carga_academica.id_caa`) y un Aula física (`aula.id_aul`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, campo aditivo `tipo_sesion_hor`,
 *    join docente vía `carga_academica.pep_id -> persona_perfil.id_pep -> persona.id_per`, y auditoría Eloquent en `alternarEstatus`).
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class Horario extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'horario';

    protected $primaryKey = 'id_hor';

    protected $moduloAuditoria = 'Horario';

    protected $fillable = [
        'dia_semana_hor',
        'hora_inicio_hor',
        'hora_fin_hor',
        'tipo_sesion_hor',
        'caa_id',
        'aul_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_hor'    => 'integer',
        'caa_id'    => 'integer',
        'aul_id'    => 'integer',
        'status_id' => 'integer',
    ];

    /**
     * Carga Académica asociada al bloque horario.
     */
    public function cargaAcademica(): BelongsTo
    {
        return $this->belongsTo(CargaAcademica::class, 'caa_id', 'id_caa');
    }

    /**
     * Aula física asignada al bloque horario.
     */
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'aul_id', 'id_aul');
    }

    /**
     * Scope para horarios activos (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Listado paginado de bloques horarios con carga académica, aula y docente asignado.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search    = $filtros['search'] ?? null;
        $statusId  = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);
        $diaSemana = $filtros['dia_semana_hor'] ?? null;
        $aulId     = $filtros['aul_id'] ?? null;

        return DB::table('horario')
            ->join('carga_academica', 'carga_academica.id_caa', '=', 'horario.caa_id')
            ->join('seccion', 'seccion.id_sec', '=', 'carga_academica.sec_id')
            ->join('unidad_curricular', 'unidad_curricular.id_unc', '=', 'carga_academica.unc_id')
            ->join('aula', 'aula.id_aul', '=', 'horario.aul_id')
            ->leftJoin('persona_perfil', 'persona_perfil.id_pep', '=', 'carga_academica.pep_id')
            ->leftJoin('persona', 'persona.id_per', '=', 'persona_perfil.per_id')
            ->select(
                'horario.*',
                'horario.id_hor as id',
                'seccion.id_sec',
                'seccion.nombre_sec',
                'seccion.codigo_sec',
                'unidad_curricular.id_unc',
                'unidad_curricular.nombre_unc',
                'unidad_curricular.codigo_unc',
                'aula.codigo_aula_aul',
                'carga_academica.pep_id',
                DB::raw("COALESCE(persona.nombres_per || ' ' || persona.apellidos_per, 'Sin asignar') as docente_nombre")
            )
            ->whereNull('horario.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('horario.status_id', $statusId))
            ->when(! empty($diaSemana) && $diaSemana !== 'all', fn ($q) => $q->where('horario.dia_semana_hor', $diaSemana))
            ->when(! empty($aulId) && $aulId !== 'all', fn ($q) => $q->where('horario.aul_id', (int) $aulId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('horario.dia_semana_hor', 'ilike', "%{$search}%")
                        ->orWhere('horario.tipo_sesion_hor', 'ilike', "%{$search}%")
                        ->orWhere('seccion.nombre_sec', 'ilike', "%{$search}%")
                        ->orWhere('seccion.codigo_sec', 'ilike', "%{$search}%")
                        ->orWhere('unidad_curricular.nombre_unc', 'ilike', "%{$search}%")
                        ->orWhere('aula.codigo_aula_aul', 'ilike', "%{$search}%")
                        ->orWhere('persona.nombres_per', 'ilike', "%{$search}%")
                        ->orWhere('persona.apellidos_per', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('horario.dia_semana_hor', 'asc')
            ->orderBy('horario.hora_inicio_hor', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Alterna el estatus lógico de un horario (`1` Activo, `2` Inactivo).
     */
    public static function alternarEstatus(int $id, int $nuevoEstado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_hor', $id)->firstOrFail();

        return (bool) $registro->update([
            'status_id'  => $nuevoEstado,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
