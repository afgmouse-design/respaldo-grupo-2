<?php

namespace App\Models\Seguridad;

use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Submódulo de Navegación (tabla `submodulo`).
 *
 * Propósito: Acordeón colapsable de Nivel 2 perteneciente a un Módulo (`mod_id`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql` (`id_sbm`, `mod_id`, `*_sbm`, `status_id`, `SoftDeletes`).
 */
class Submodulo extends Model
{
    use SoftDeletes;
    use UsesStatusCatalog;

    protected $table = 'submodulo';

    protected $primaryKey = 'id_sbm';

    protected $fillable = [
        'mod_id',
        'nombre_sbm',
        'icono_sbm',
        'orden_sbm',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_sbm'    => 'integer',
        'mod_id'    => 'integer',
        'orden_sbm' => 'integer',
        'status_id' => 'integer',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'mod_id', 'id_mod');
    }

    public function vistas(): HasMany
    {
        return $this->hasMany(Vista::class, 'sbm_id', 'id_sbm');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
