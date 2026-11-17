<?php

namespace App\Models\Seguridad;

use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Vista de Navegación (tabla `vista`).
 *
 * Propósito: Enlace final de Nivel 3 en la navegación lateral (`clave_vis`, `ruta_vis`)
 * asociado a un Módulo (`mod_id`) y Submódulo (`sbm_id`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql` (`id_vis`, `clave_vis`, `ruta_vis`, `status_id`, `SoftDeletes`).
 */
class Vista extends Model
{
    use SoftDeletes;
    use UsesStatusCatalog;

    protected $table = 'vista';

    protected $primaryKey = 'id_vis';

    protected $fillable = [
        'mod_id',
        'sbm_id',
        'clave_vis',
        'nombre_vis',
        'descripcion_vis',
        'ruta_vis',
        'icono_vis',
        'orden_vis',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_vis'    => 'integer',
        'mod_id'    => 'integer',
        'sbm_id'    => 'integer',
        'orden_vis' => 'integer',
        'status_id' => 'integer',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'mod_id', 'id_mod');
    }

    public function submodulo(): BelongsTo
    {
        return $this->belongsTo(Submodulo::class, 'sbm_id', 'id_sbm');
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'vis_id', 'id_vis');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
