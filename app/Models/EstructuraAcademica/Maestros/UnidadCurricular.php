<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\ProgramacionAcademica\CargaAcademica;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Unidad Curricular (tabla `unidad_curricular`).
 *
 * Propósito: Catálogo base de asignaturas / unidades curriculares en `sogac_mio.sql`.
 * Las cargas horarias (`htea_mcd`, `htei_mcd`, `thte_mcd`, `unidades_credito_mcd`) residen en
 * `malla_curricular_detalle`, sin duplicarlas en esta tabla.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`codigo_anterior_unc`, `dep_id`, `status_id`,
 *    `SoftDeletes`, relaciones hacia `regla_prelacion`, sin columnas duplicadas de carga/trayecto ni `status`).
 *  - 2026-11-16: Añadido `HandlesSoftDeleteWithStatus` y `liberarCodigoSiEliminado()` para baja lógica
 *    en interfaz y reutilización segura de `codigo_unc`.
 */
class UnidadCurricular extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    /**
     * Libera `codigo_unc` si pertenece a una unidad curricular eliminada lógicamente.
     */
    public static function liberarCodigoSiEliminado(?string $codigo, ?int $ignoreId = null): void
    {
        if ($codigo === null || trim($codigo) === '') {
            return;
        }

        static::liberarValorUnicoEliminado(
            'unidad_curricular',
            'id_unc',
            'codigo_unc',
            trim($codigo),
            $ignoreId,
            30,
            "Ya existe una unidad curricular activa con el código '{$codigo}'."
        );
    }

    protected $table = 'unidad_curricular';
    protected $primaryKey = 'id_unc';
    protected $moduloAuditoria = 'UnidadCurricular';

    protected $fillable = [
        'codigo_unc',
        'codigo_anterior_unc',
        'nombre_unc',
        'tuc_id',
        'dep_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_unc'    => 'integer',
        'tuc_id'    => 'integer',
        'dep_id'    => 'integer',
        'status_id' => 'integer',
    ];

    public function tipoUnidadCurricular(): BelongsTo
    {
        return $this->belongsTo(TipoUnidadCurricular::class, 'tuc_id', 'id_tuc');
    }

    public function detallesMalla(): HasMany
    {
        return $this->hasMany(MallaCurricularDetalle::class, 'unc_id', 'id_unc');
    }

    public function prelacionesExigidas(): HasMany
    {
        return $this->hasMany(ReglaPrelacion::class, 'unc_destino_id', 'id_unc');
    }

    public function esPrerrequisitoDe(): HasMany
    {
        return $this->hasMany(ReglaPrelacion::class, 'unc_origen_id', 'id_unc');
    }

    public function cargasAcademicas(): HasMany
    {
        return $this->hasMany(CargaAcademica::class, 'unc_id', 'id_unc');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array|string|null $busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $statusId = static::resolverFiltroStatusId(is_array($busqueda) ? ($busqueda['status'] ?? $busqueda['status_id'] ?? null) : null);

        $query = static::with(['tipoUnidadCurricular', 'prelacionesExigidas'])
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_unc', 'ilike', "%{$search}%")
                    ->orWhere('codigo_unc', 'ilike', "%{$search}%")
                    ->orWhere('codigo_anterior_unc', 'ilike', "%{$search}%")
                    ->orWhereHas('tipoUnidadCurricular', function ($qt) use ($search) {
                        $qt->where('nombre_tuc', 'ilike', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('id_unc', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO): bool
    {
        $registro = static::where('id_unc', $id)->first();
        if (! $registro) {
            return false;
        }

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => auth()->id(),
        ]);
    }
}
