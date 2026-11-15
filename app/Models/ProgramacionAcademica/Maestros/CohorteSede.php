<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Cohorte por Sede (tabla `cohorte_sede`).
 *
 * Propósito: Distribución de ingresantes por cohorte y oferta de sede-PNF.
 * 
 * Actualización 2026-10-21 (Corrección de relación y error SQL column cohorte_sede.sed_id does not exist):
 * Se restauró la relación con `sede_pnf` (campo `sep_id`) en lugar de `sed_id`, ya que la tabla en la base de datos
 * referencia a la oferta académica por núcleo (`sede_pnf.id_sep`) y no directamente a `sede`. La referencia anterior a
 * `sed_id` causaba un error fatal de columna inexistente al consultar el maestro.
 *
 * Actualización 2026-10-21 (Protección Ascendente al Activar):
 * Se incorporó la validación en `alternarEstatus()` para impedir activar una asignación de sede
 * si la Cohorte padre a la que pertenece se encuentra inactiva.
 *
 * Actualización 2026-10-21 (Corrección Filtro Inicial y Visualización de Todas las Ofertas):
 * Se ajustó `obtenerPaginados()` para que por defecto ('all' o sin filtro explícito) no filtre
 * exclusivamente por registros activos, permitiendo visualizar todas las cohortes por sede registradas.
 *
 * Actualización 2026-10-21 (Protección Ascendente con deleted_at):
 * Se actualizó la verificación en `alternarEstatus()` para comprobar que la cohorte padre no esté eliminada (whereNull('deleted_at')).
 *
 * Actualización 2026-11-01 (Corrección RelationNotFoundException [cohorte]):
 * Se definieron explícitamente las relaciones Eloquent `cohorte()` y `sedePnf()` en el modelo, resolviendo
 * la excepción `RelationNotFoundException: Call to undefined relationship [cohorte] on model CohorteSede`
 * disparada al invocar `Seccion::with(['cohorteSede.cohorte'])` en la programación de horarios.
 *
 * Baja lógica mediante `status` (bool) y `status_id` (1 activo, 2 inactivo).
 */
class CohorteSede extends Model
{
    use RegistraEnBitacora;

    protected $table = 'cohorte_sede';

    protected $primaryKey = 'id_cos';

    protected $moduloAuditoria = 'Cohorte Sede';

    protected $fillable = [
        'nombre_cos',
        'coh_id',
        'sep_id',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_cos'    => 'integer',
        'coh_id'    => 'integer',
        'sep_id'    => 'integer',
        'status'    => 'boolean',
        'status_id' => 'integer',
    ];

    /**
     * Relación con la Cohorte matriz.
     */
    public function cohorte(): BelongsTo
    {
        return $this->belongsTo(Cohorte::class, 'coh_id', 'id_coh');
    }

    /**
     * Relación con la Oferta Sede-PNF.
     */
    public function sedePnf(): BelongsTo
    {
        return $this->belongsTo(SedePnf::class, 'sep_id', 'id_sep');
    }

    /**
     * Listado paginado con filtros (search, status, coh_id, sep_id). Por defecto solo activos.
     * Expone `id` (alias de id_cos), `nombre_coh`, `nombre_sed`, `nombre_pnf` y `oferta_academica`.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $status = $filtros['status'] ?? null;
        $cohId  = $filtros['coh_id'] ?? null;
        $sepId  = $filtros['sep_id'] ?? null;

        return DB::table('cohorte_sede')
            ->leftJoin('cohorte', 'cohorte.id_coh', '=', 'cohorte_sede.coh_id')
            ->leftJoin('sede_pnf', 'sede_pnf.id_sep', '=', 'cohorte_sede.sep_id')
            ->leftJoin('sede', 'sede.id_sed', '=', 'sede_pnf.sed_id')
            ->leftJoin('pnf', 'pnf.id_pnf', '=', 'sede_pnf.pnf_id')
            ->select(
                'cohorte_sede.*',
                'cohorte_sede.id_cos as id',
                'cohorte.nombre_coh',
                'sede.nombre_sed',
                'pnf.nombre_pnf',
                DB::raw("COALESCE(sede.nombre_sed || ' - ' || pnf.nombre_pnf, '—') as oferta_academica")
            )
            ->whereNull('cohorte_sede.deleted_at')
            ->when($status === 'true' || $status === '1' || $status === 1 || $status === true, function ($q) {
                $q->where(function ($w) {
                    $w->where('cohorte_sede.status', true)
                      ->orWhere('cohorte_sede.status_id', 1);
                });
            })
            ->when($status === 'false' || $status === '0' || $status === 0 || $status === false, function ($q) {
                $q->where(function ($w) {
                    $w->where('cohorte_sede.status', false)
                      ->orWhere('cohorte_sede.status_id', 2);
                });
            })
            ->when(!empty($cohId) && $cohId !== 'all', fn ($q) => $q->where('cohorte_sede.coh_id', $cohId))
            ->when(!empty($sepId) && $sepId !== 'all', fn ($q) => $q->where('cohorte_sede.sep_id', $sepId))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('cohorte_sede.nombre_cos', 'ilike', "%{$search}%")
                      ->orWhere('cohorte.nombre_coh', 'ilike', "%{$search}%")
                      ->orWhere('sede.nombre_sed', 'ilike', "%{$search}%")
                      ->orWhere('pnf.nombre_pnf', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('cohorte_sede.id_cos', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica: fija status_id (1 = activo, 2 = inactivo) y sincroniza `status`.
     * Aplica protección ascendente impidiendo activar si la cohorte padre está inactiva.
     *
     * @throws \DomainException
     */
    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_cos', $id)->firstOrFail();

        // Protección Ascendente: No activar si la Cohorte Padre está inactiva
        if ($estado === 1) {
            if (!empty($registro->coh_id)) {
                $cohorte = DB::table('cohorte')->where('id_coh', $registro->coh_id)->whereNull('deleted_at')->first();
                if (!$cohorte || !$cohorte->status) {
                    throw new \DomainException("No se puede activar la asignación porque la Cohorte a la que pertenece se encuentra inactiva.");
                }
            }
        }

        return $registro->update([
            'status_id'  => $estado,
            'status'     => $estado === 1,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}
