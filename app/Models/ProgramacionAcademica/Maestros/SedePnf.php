<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\EstructuraAcademica\Maestros\Pnf;
use App\Models\OrganizacionInstitucional\Maestros\Sede;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Sede PNF (tabla `sede_pnf`).
 *
 * Propósito: oferta académica por núcleo; vincula una sede con un PNF que en ella se imparte.
 * Es la entidad que referencia `cohorte_sede.sep_id`. Baja lógica mediante el campo booleano `status`.
 *
 * Actualización 2026-11-01 (Inclusión de relaciones Eloquent sede y pnf):
 * Se añadieron los métodos `sede()` y `pnf()` para permitir carga eager (e.g. `with(['sedePnf.sede', 'sedePnf.pnf'])`).
 */
class SedePnf extends Model
{
    use RegistraEnBitacora;

    protected $table = 'sede_pnf';
    protected $primaryKey = 'id_sep';
    protected $moduloAuditoria = 'SedePnf';

    protected $fillable = [
        'sed_id',
        'pnf_id',
        'status',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relación con Sede física.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    /**
     * Relación con PNF (Programa Nacional de Formación).
     */
    public function pnf(): BelongsTo
    {
        return $this->belongsTo(Pnf::class, 'pnf_id', 'id_pnf');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Ofertas activas para selectores (ej. Cohorte Sede). Etiqueta: "Sede - PNF".
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('sede_pnf')
            ->join('sede', 'sede.id_sed', '=', 'sede_pnf.sed_id')
            ->join('pnf', 'pnf.id_pnf', '=', 'sede_pnf.pnf_id')
            ->whereNull('sede_pnf.deleted_at')
            ->where('sede_pnf.status', true)
            ->selectRaw("sede_pnf.id_sep, sede.nombre_sed || ' - ' || pnf.nombre_pnf as nombre_sep")
            ->orderBy('sede.nombre_sed')
            ->orderBy('pnf.nombre_pnf')
            ->get();
    }

    /**
     * Listado paginado con filtros (search, status, sed_id, pnf_id) para el CRUD con modales.
     * Expone `id` como alias de `id_sep` y los nombres de sede y PNF.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $status = $filtros['status'] ?? null;
        $sedId  = $filtros['sed_id'] ?? null;
        $pnfId  = $filtros['pnf_id'] ?? null;

        return DB::table('sede_pnf')
            ->join('sede', 'sede.id_sed', '=', 'sede_pnf.sed_id')
            ->join('pnf', 'pnf.id_pnf', '=', 'sede_pnf.pnf_id')
            ->select('sede_pnf.*', 'sede_pnf.id_sep as id', 'sede.nombre_sed', 'pnf.nombre_pnf', 'pnf.codigo_pnf')
            ->whereNull('sede_pnf.deleted_at')
            ->when($status !== null && $status !== '' && $status !== 'all', fn ($q) => $q->where('sede_pnf.status', filter_var($status, FILTER_VALIDATE_BOOLEAN)))
            ->when(!empty($sedId) && $sedId !== 'all', fn ($q) => $q->where('sede_pnf.sed_id', $sedId))
            ->when(!empty($pnfId) && $pnfId !== 'all', fn ($q) => $q->where('sede_pnf.pnf_id', $pnfId))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('sede.nombre_sed', 'ilike', "%{$search}%")
                      ->orWhere('pnf.nombre_pnf', 'ilike', "%{$search}%")
                      ->orWhere('pnf.codigo_pnf', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('sede_pnf.id_sep', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica sobre `status`. Usa Eloquent para que RegistraEnBitacora audite el cambio.
     */
    public static function alternarEstatus(int $id, bool $activo, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_sep', $id)->firstOrFail();

        return $registro->update([
            'status'     => $activo,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}

