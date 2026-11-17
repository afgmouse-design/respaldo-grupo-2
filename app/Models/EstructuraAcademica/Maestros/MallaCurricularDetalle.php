<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Malla Curricular Detalle (tabla `malla_curricular_detalle`).
 *
 * Propósito: Asocia una Unidad Curricular a una Malla Curricular y Trayecto con su carga
 * de horas (`htea_mcd`, `htei_mcd`, `thte_mcd`) y créditos (`unidades_credito_mcd`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, campos aditivos
 *    aprobados `nota_minima_aprobatoria_mcd`, `semestre_mcd`, `etapa_curricular_mcd`; se retiran
 *    `tuc_id` redundante y el booleano `status`).
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 *  - 2026-11-16: Agregado `duracion_semestres_mcd` (1 o 2 semestres) en `$fillable` y `$casts`.
 */
class MallaCurricularDetalle extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'malla_curricular_detalle';
    protected $primaryKey = 'id_mcd';
    protected $moduloAuditoria = 'MallaCurricularDetalle';

    protected $fillable = [
        'unidades_credito_mcd',
        'htea_mcd',
        'htei_mcd',
        'thte_mcd',
        'duracion_semanas_mcd',
        'duracion_semestres_mcd',
        'nota_minima_aprobatoria_mcd',
        'semestre_mcd',
        'etapa_curricular_mcd',
        'mac_id',
        'unc_id',
        'tra_id',
        'ecu_id',
        'esm_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_mcd'                      => 'integer',
        'unidades_credito_mcd'        => 'integer',
        'htea_mcd'                    => 'integer',
        'htei_mcd'                    => 'integer',
        'thte_mcd'                    => 'integer',
        'duracion_semanas_mcd'        => 'integer',
        'duracion_semestres_mcd'      => 'integer',
        'nota_minima_aprobatoria_mcd' => 'float',
        'semestre_mcd'                => 'integer',
        'mac_id'                      => 'integer',
        'unc_id'                      => 'integer',
        'tra_id'                      => 'integer',
        'ecu_id'                      => 'integer',
        'esm_id'                      => 'integer',
        'status_id'                   => 'integer',
    ];

    public function mallaCurricular(): BelongsTo
    {
        return $this->belongsTo(MallaCurricular::class, 'mac_id', 'id_mac');
    }

    public function unidadCurricular(): BelongsTo
    {
        return $this->belongsTo(UnidadCurricular::class, 'unc_id', 'id_unc');
    }

    public function trayecto(): BelongsTo
    {
        return $this->belongsTo(Trayecto::class, 'tra_id', 'id_tra');
    }

    public function ejeCurricular(): BelongsTo
    {
        return $this->belongsTo(EjeCurricular::class, 'ecu_id', 'id_ecu');
    }

    public function escalaMedicion(): BelongsTo
    {
        return $this->belongsTo(EscalaMedicion::class, 'esm_id', 'id_esm');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id', 'id');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id', 'id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array $filtros = [], int $porPagina = 10): LengthAwarePaginator
    {
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? self::STATUS_ACTIVO);

        $query = self::query()
            ->with([
                'mallaCurricular',
                'unidadCurricular.tipoUnidadCurricular',
                'trayecto',
                'ejeCurricular',
                'escalaMedicion',
            ])
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId));

        if (! empty($filtros['search'])) {
            $busqueda = trim((string) $filtros['search']);
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('unidadCurricular', function ($qunc) use ($busqueda) {
                    $qunc->where('nombre_unc', 'ilike', "%{$busqueda}%")
                        ->orWhere('codigo_unc', 'ilike', "%{$busqueda}%");
                })
                    ->orWhereHas('mallaCurricular', fn ($qmac) => $qmac->where('nombre_mac', 'ilike', "%{$busqueda}%"))
                    ->orWhereHas('trayecto', fn ($qtra) => $qtra->where('nombre_tra', 'ilike', "%{$busqueda}%"));
            });
        }

        if (! empty($filtros['mac_id'])) {
            $query->where('mac_id', (int) $filtros['mac_id']);
        }

        if (! empty($filtros['tra_id'])) {
            $query->where('tra_id', (int) $filtros['tra_id']);
        }

        return $query->orderBy('id_mcd', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public static function alternarEstatus(int $id, int $estado): bool
    {
        $registro = self::find($id);
        if (! $registro) {
            return false;
        }

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => auth()->id(),
        ]);
    }
}
