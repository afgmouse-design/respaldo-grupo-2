<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\EstructuraAcademica\Maestros\Programa;
use App\Models\OrganizacionInstitucional\Maestros\Sede;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Oferta Sede-Programa (tabla `sede_programa`).
 *
 * Propósito: Vincula una Sede física con un Programa de formación impartido en ella.
 * Es referenciado por `cohorte_sede.sep_id` (`id_spr`), `asignacion_programa.spr_id` y `oferta_academica.spr_id`.
 *
 * Historial:
 *  - 2026-11-14: Creación sustituyendo al modelo legacy `SedePnf` para alinearse con `sogac_mio.sql`
 *    (`id_spr`, `prg_id`, `codigo_ministerio_spr`, `cantidad_asignados_spr`, `status_id`, `SoftDeletes`).
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class SedePrograma extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'sede_programa';

    protected $primaryKey = 'id_spr';

    protected $moduloAuditoria = 'SedePrograma';

    protected $fillable = [
        'prg_id',
        'sed_id',
        'codigo_ministerio_spr',
        'cantidad_asignados_spr',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_spr'                 => 'integer',
        'prg_id'                 => 'integer',
        'sed_id'                 => 'integer',
        'cantidad_asignados_spr' => 'integer',
        'status_id'              => 'integer',
    ];

    /**
     * Relación con la Sede física.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    /**
     * Relación con el Programa académico.
     */
    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'prg_id', 'id_prg');
    }

    /**
     * Relación con las asignaciones Cohorte-Sede asociadas.
     */
    public function cohortesSede(): HasMany
    {
        return $this->hasMany(CohorteSede::class, 'sep_id', 'id_spr');
    }

    /**
     * Scope para filtrar únicamente ofertas activas (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Ofertas activas para selectores (ej. Cohorte por Sede). Etiqueta: "Sede - Programa".
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('sede_programa')
            ->join('sede', 'sede.id_sed', '=', 'sede_programa.sed_id')
            ->join('programa', 'programa.id_prg', '=', 'sede_programa.prg_id')
            ->whereNull('sede_programa.deleted_at')
            ->where('sede_programa.status_id', self::STATUS_ACTIVO)
            ->selectRaw("sede_programa.id_spr, sede.nombre_sed || ' - ' || programa.nombre_prg as nombre_spr")
            ->orderBy('sede.nombre_sed')
            ->orderBy('programa.nombre_prg')
            ->get();
    }

    /**
     * Listado paginado con filtros (search, status, sed_id, prg_id).
     * Expone `id` como alias de `id_spr` y los datos descriptivos de Sede y Programa.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search   = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);
        $sedId    = $filtros['sed_id'] ?? null;
        $prgId    = $filtros['prg_id'] ?? null;

        return DB::table('sede_programa')
            ->join('sede', 'sede.id_sed', '=', 'sede_programa.sed_id')
            ->join('programa', 'programa.id_prg', '=', 'sede_programa.prg_id')
            ->select(
                'sede_programa.*',
                'sede_programa.id_spr as id',
                'sede.nombre_sed',
                'programa.nombre_prg',
                'programa.codigo_prg'
            )
            ->whereNull('sede_programa.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('sede_programa.status_id', $statusId))
            ->when(! empty($sedId) && $sedId !== 'all', fn ($q) => $q->where('sede_programa.sed_id', (int) $sedId))
            ->when(! empty($prgId) && $prgId !== 'all', fn ($q) => $q->where('sede_programa.prg_id', (int) $prgId))
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('sede.nombre_sed', 'ilike', "%{$search}%")
                        ->orWhere('programa.nombre_prg', 'ilike', "%{$search}%")
                        ->orWhere('programa.codigo_prg', 'ilike', "%{$search}%")
                        ->orWhere('sede_programa.codigo_ministerio_spr', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('sede_programa.id_spr', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica sobre `status_id` (1 = Activo, 2 = Inactivo).
     */
    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_spr', $id)->firstOrFail();

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
