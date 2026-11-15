<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\Pnf;
use App\Models\OrganizacionInstitucional\Maestros\Sede;
use App\Models\ProgramacionAcademica\Maestros\CohorteSede;
use App\Models\ProgramacionAcademica\Maestros\LapsoAcademico;
use App\Models\ProgramacionAcademica\SeccionUnidadDocente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Sección (tabla `seccion`).
 *
 * Propósito: Agrupación macro de alumnos para cursar unidades curriculares en un lapso académico,
 * vinculada a una cohorte por sede, carrera/PNF y malla curricular obligatoria.
 * Baja lógica mediante `status` (bool) y `status_id` (1 activo, 2 inactivo).
 */
class Seccion extends Model
{
    use RegistraEnBitacora;

    protected $table = 'seccion';

    protected $primaryKey = 'id_sec';

    protected $moduloAuditoria = 'Seccion';

    protected $fillable = [
        'codigo_sec',
        'nombre_sec',
        'tipo_seccion',
        'nivel_sec',
        'semestre_sec',
        'trayecto_sec',
        'turno_sec',
        'cupo_maximo_sec',
        'laa_id',
        'pnf_id',
        'prg_id',
        'sed_id',
        'tis_id',
        'tur_id',
        'cos_id',
        'mac_id',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_sec'          => 'integer',
        'nivel_sec'       => 'integer',
        'semestre_sec'    => 'integer',
        'trayecto_sec'    => 'integer',
        'cupo_maximo_sec' => 'integer',
        'laa_id'          => 'integer',
        'pnf_id'          => 'integer',
        'prg_id'          => 'integer',
        'sed_id'          => 'integer',
        'tis_id'          => 'integer',
        'tur_id'          => 'integer',
        'cos_id'          => 'integer',
        'mac_id'          => 'integer',
        'status'          => 'boolean',
        'status_id'       => 'integer',
    ];

    /**
     * Relación con el Lapso Académico.
     */
    public function lapsoAcademico(): BelongsTo
    {
        return $this->belongsTo(LapsoAcademico::class, 'laa_id', 'id_la');
    }

    /**
     * Relación con el Programa Nacional de Formación.
     */
    public function pnf(): BelongsTo
    {
        return $this->belongsTo(Pnf::class, 'pnf_id', 'id_pnf');
    }

    /**
     * Relación con la Sede física.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    /**
     * Relación con la Cohorte por Sede.
     */
    public function cohorteSede(): BelongsTo
    {
        return $this->belongsTo(CohorteSede::class, 'cos_id', 'id_cos');
    }

    /**
     * Relación con la Malla Curricular asociada.
     */
    public function mallaCurricular(): BelongsTo
    {
        return $this->belongsTo(MallaCurricular::class, 'mac_id', 'id_mac');
    }

    /**
     * Relación con las unidades docentes asignadas a la sección.
     */
    public function unidadesDocentes(): HasMany
    {
        return $this->hasMany(SeccionUnidadDocente::class, 'sec_id', 'id_sec');
    }

    /**
     * Listado paginado con filtros.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $status   = $filtros['status'] ?? null;
        $laaId    = $filtros['laa_id'] ?? null;
        $pnfId    = $filtros['pnf_id'] ?? null;
        $sedId    = $filtros['sed_id'] ?? null;
        $turnoSec = $filtros['turno_sec'] ?? null;

        return DB::table('seccion')
            ->leftJoin('lapso_academico', 'lapso_academico.id_la', '=', 'seccion.laa_id')
            ->leftJoin('pnf', 'pnf.id_pnf', '=', 'seccion.pnf_id')
            ->leftJoin('sede', 'sede.id_sed', '=', 'seccion.sed_id')
            ->leftJoin('cohorte_sede', 'cohorte_sede.id_cos', '=', 'seccion.cos_id')
            ->leftJoin('malla_curricular', 'malla_curricular.id_mac', '=', 'seccion.mac_id')
            ->select(
                'seccion.*',
                'seccion.id_sec as id',
                'lapso_academico.nombre_la',
                'pnf.nombre_pnf',
                'pnf.codigo_pnf',
                'sede.nombre_sed',
                'cohorte_sede.nombre_cos',
                'malla_curricular.nombre_mac'
            )
            ->whereNull('seccion.deleted_at')
            ->when($status !== null && $status !== '' && $status !== 'all', fn ($q) => $q->where('seccion.status', filter_var($status, FILTER_VALIDATE_BOOLEAN)))
            ->when(!empty($laaId) && $laaId !== 'all', fn ($q) => $q->where('seccion.laa_id', $laaId))
            ->when(!empty($pnfId) && $pnfId !== 'all', fn ($q) => $q->where('seccion.pnf_id', $pnfId))
            ->when(!empty($sedId) && $sedId !== 'all', fn ($q) => $q->where('seccion.sed_id', $sedId))
            ->when(!empty($turnoSec) && $turnoSec !== 'all', fn ($q) => $q->where('seccion.turno_sec', $turnoSec))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('seccion.nombre_sec', 'ilike', "%{$search}%")
                      ->orWhere('seccion.codigo_sec', 'ilike', "%{$search}%")
                      ->orWhere('seccion.tipo_seccion', 'ilike', "%{$search}%")
                      ->orWhere('seccion.turno_sec', 'ilike', "%{$search}%")
                      ->orWhere('lapso_academico.nombre_la', 'ilike', "%{$search}%")
                      ->orWhere('pnf.nombre_pnf', 'ilike', "%{$search}%")
                      ->orWhere('pnf.codigo_pnf', 'ilike', "%{$search}%")
                      ->orWhere('sede.nombre_sed', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('seccion.id_sec', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica.
     */
    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_sec', $id)->firstOrFail();

        return $registro->update([
            'status_id'  => $estado,
            'status'     => $estado === 1,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
