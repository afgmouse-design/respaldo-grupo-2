<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Academico\LapsoAcademico;
use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\Programa;
use App\Models\OrganizacionInstitucional\Maestros\Sede;
use App\Models\ProgramacionAcademica\CargaAcademica;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Sección Académica (tabla `seccion`).
 *
 * Propósito: Grupo de estudiantes organizado por Lapso Académico, Programa, Sede, Tipo de Sección,
 * Turno, Cohorte-Sede y Malla Curricular. De él cuelgan las asignaciones de `carga_academica`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (reemplazo de `pnf_id` por `prg_id`, adición de FKs
 *    `tis_id`, `tur_id`, `cos_id`, `mac_id` y campos `codigo_sec`, `semestre_sec`, `trayecto_sec`;
 *    uso de `status_id` sin booleano `status`, `SoftDeletes`, y relación `cargasAcademicas()`).
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class Seccion extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'seccion';

    protected $primaryKey = 'id_sec';

    protected $moduloAuditoria = 'Seccion';

    protected $fillable = [
        'nivel_sec',
        'nombre_sec',
        'codigo_sec',
        'semestre_sec',
        'trayecto_sec',
        'turno_sec',
        'cupo_maximo_sec',
        'laa_id',
        'prg_id',
        'sed_id',
        'tis_id',
        'tur_id',
        'cos_id',
        'mac_id',
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
        'prg_id'          => 'integer',
        'sed_id'          => 'integer',
        'tis_id'          => 'integer',
        'tur_id'          => 'integer',
        'cos_id'          => 'integer',
        'mac_id'          => 'integer',
        'status_id'       => 'integer',
    ];

    /**
     * Lapso Académico de la sección.
     */
    public function lapsoAcademico(): BelongsTo
    {
        return $this->belongsTo(LapsoAcademico::class, 'laa_id', 'id_laa');
    }

    /**
     * Programa académico de la sección.
     */
    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'prg_id', 'id_prg');
    }

    /**
     * Sede física de la sección.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    /**
     * Tipo de sección (Regular, PER, Repitiente, Intensivo, etc.).
     */
    public function tipoSeccion(): BelongsTo
    {
        return $this->belongsTo(TipoSeccion::class, 'tis_id', 'id_tis');
    }

    /**
     * Turno oficial asignado a la sección.
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'tur_id', 'id_tur');
    }

    /**
     * Cohorte por Sede asignada.
     */
    public function cohorteSede(): BelongsTo
    {
        return $this->belongsTo(CohorteSede::class, 'cos_id', 'id_cos');
    }

    /**
     * Malla Curricular de referencia para la sección.
     */
    public function mallaCurricular(): BelongsTo
    {
        return $this->belongsTo(MallaCurricular::class, 'mac_id', 'id_mac');
    }

    /**
     * Asignaciones de carga académica (unidad curricular + docente por perfil) de la sección.
     */
    public function cargasAcademicas(): HasMany
    {
        return $this->hasMany(CargaAcademica::class, 'sec_id', 'id_sec');
    }

    /**
     * Scope para secciones activas (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Listado paginado con filtros (search, status, laa_id, prg_id, sed_id, tis_id, tur_id, turno_sec).
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);
        $laaId    = $filtros['laa_id'] ?? null;
        $prgId    = $filtros['prg_id'] ?? null;
        $sedId    = $filtros['sed_id'] ?? null;
        $tisId    = $filtros['tis_id'] ?? null;
        $turId    = $filtros['tur_id'] ?? null;
        $turnoSec = $filtros['turno_sec'] ?? null;

        return DB::table('seccion')
            ->leftJoin('lapso_academico', 'lapso_academico.id_laa', '=', 'seccion.laa_id')
            ->leftJoin('programa', 'programa.id_prg', '=', 'seccion.prg_id')
            ->leftJoin('sede', 'sede.id_sed', '=', 'seccion.sed_id')
            ->leftJoin('tipo_seccion', 'tipo_seccion.id_tis', '=', 'seccion.tis_id')
            ->leftJoin('turno', 'turno.id_tur', '=', 'seccion.tur_id')
            ->leftJoin('cohorte_sede', 'cohorte_sede.id_cos', '=', 'seccion.cos_id')
            ->leftJoin('malla_curricular', 'malla_curricular.id_mac', '=', 'seccion.mac_id')
            ->select(
                'seccion.*',
                'seccion.id_sec as id',
                'lapso_academico.nombre_laa',
                'programa.nombre_prg',
                'programa.codigo_prg',
                'sede.nombre_sed',
                'tipo_seccion.nombre_tis',
                'turno.nombre_tur',
                'cohorte_sede.nombre_cos',
                'malla_curricular.nombre_mac'
            )
            ->whereNull('seccion.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('seccion.status_id', $statusId))
            ->when(! empty($laaId) && $laaId !== 'all', fn ($q) => $q->where('seccion.laa_id', (int) $laaId))
            ->when(! empty($prgId) && $prgId !== 'all', fn ($q) => $q->where('seccion.prg_id', (int) $prgId))
            ->when(! empty($sedId) && $sedId !== 'all', fn ($q) => $q->where('seccion.sed_id', (int) $sedId))
            ->when(! empty($tisId) && $tisId !== 'all', fn ($q) => $q->where('seccion.tis_id', (int) $tisId))
            ->when(! empty($turId) && $turId !== 'all', fn ($q) => $q->where('seccion.tur_id', (int) $turId))
            ->when(! empty($turnoSec) && $turnoSec !== 'all', fn ($q) => $q->where('seccion.turno_sec', $turnoSec))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('seccion.nombre_sec', 'ilike', "%{$search}%")
                        ->orWhere('seccion.codigo_sec', 'ilike', "%{$search}%")
                        ->orWhere('seccion.turno_sec', 'ilike', "%{$search}%")
                        ->orWhere('tipo_seccion.nombre_tis', 'ilike', "%{$search}%")
                        ->orWhere('turno.nombre_tur', 'ilike', "%{$search}%")
                        ->orWhere('lapso_academico.nombre_laa', 'ilike', "%{$search}%")
                        ->orWhere('programa.nombre_prg', 'ilike', "%{$search}%")
                        ->orWhere('programa.codigo_prg', 'ilike', "%{$search}%")
                        ->orWhere('sede.nombre_sed', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('seccion.id_sec', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica sobre `status_id` (1 = activo, 2 = inactivo).
     */
    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_sec', $id)->firstOrFail();

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Archiva con sufijo `-d{id}` secciones eliminadas lógicamente que compartan `(nombre_sec, laa_id, sed_id)`,
     * garantizando liberación segura de nombres históricos y rechazando duplicados activos (`deleted_at IS NULL`).
     *
     * @throws \DomainException
     */
    public static function liberarNombreSiEliminado(
        string $nombreSec,
        int $laaId,
        int $sedId,
        ?int $ignoreId = null
    ): void {
        $nombreLimpio = trim($nombreSec);
        if ($nombreLimpio === '' || $laaId <= 0 || $sedId <= 0) {
            return;
        }

        $coincidencias = DB::table('seccion')
            ->select(['id_sec', 'nombre_sec', 'deleted_at'])
            ->where('laa_id', $laaId)
            ->where('sed_id', $sedId)
            ->whereRaw('LOWER(TRIM(nombre_sec)) = LOWER(?)', [$nombreLimpio])
            ->get();

        foreach ($coincidencias as $registro) {
            $pk = (int) $registro->id_sec;
            if ($ignoreId !== null && $pk === $ignoreId) {
                continue;
            }

            if ($registro->deleted_at === null) {
                throw new \DomainException('Ya existe una sección activa con este nombre para el lapso y sede seleccionados.');
            }

            $sufijo = "-d{$pk}";
            if (! str_ends_with((string) $registro->nombre_sec, $sufijo)) {
                $base = mb_substr((string) $registro->nombre_sec, 0, max(0, 255 - mb_strlen($sufijo)));
                DB::table('seccion')
                    ->where('id_sec', $pk)
                    ->update(['nombre_sec' => $base . $sufijo]);
            }
        }
    }

    /**
     * Resuelve FKs `tis_id` y `tur_id` desde etiquetas textuales (`tipo_seccion`, `turno_sec`) o viceversa.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizarFksCatalogo(array $data): array
    {
        if (empty($data['tis_id']) && ! empty($data['tipo_seccion'])) {
            $tipo = TipoSeccion::query()
                ->where('status_id', self::STATUS_ACTIVO)
                ->where(function ($q) use ($data) {
                    $q->where('nombre_tis', 'ilike', $data['tipo_seccion'])
                        ->orWhere('codigo_tis', 'ilike', $data['tipo_seccion']);
                })
                ->first();
            $data['tis_id'] = $tipo?->id_tis;
        }
        unset($data['tipo_seccion']);

        if (! empty($data['tur_id']) && empty($data['turno_sec'])) {
            $turno = Turno::find($data['tur_id']);
            $data['turno_sec'] = $turno?->nombre_tur;
        } elseif (empty($data['tur_id']) && ! empty($data['turno_sec'])) {
            $texto = mb_strtolower(trim((string) $data['turno_sec']));
            $codigo = match (true) {
                str_contains($texto, 'mañana'), str_contains($texto, 'matutino') => 'MAT',
                str_contains($texto, 'tarde'), str_contains($texto, 'vespertino') => 'VES',
                str_contains($texto, 'noche'), str_contains($texto, 'nocturno') => 'NOC',
                default => null,
            };
            $turno = Turno::query()
                ->where('status_id', self::STATUS_ACTIVO)
                ->where(function ($q) use ($data, $codigo) {
                    $q->where('nombre_tur', 'ilike', $data['turno_sec']);
                    if ($codigo) {
                        $q->orWhere('codigo_tur', $codigo);
                        if ($codigo === 'MAT') {
                            $q->orWhere('codigo_tur', 'DIU');
                        }
                    }
                })
                ->first();
            $data['tur_id'] = $turno?->id_tur;
        }

        return $data;
    }
}
