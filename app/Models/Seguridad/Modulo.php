<?php

namespace App\Models\Seguridad;

use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Módulo de Seguridad y Navegación (tabla `modulo`).
 *
 * Propósito: Agrupador de Nivel 1 en el menú lateral (`sidebar`) y matriz de permisos.
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql` (`id_mod`, `*_mod`, `status_id`, `SoftDeletes`).
 */
class Modulo extends Model
{
    use SoftDeletes;
    use UsesStatusCatalog;

    protected $table = 'modulo';

    protected $primaryKey = 'id_mod';

    protected $fillable = [
        'es_sistema_mod',
        'nombre_mod',
        'descripcion_mod',
        'icono_mod',
        'orden_mod',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_mod'         => 'integer',
        'es_sistema_mod' => 'boolean',
        'orden_mod'      => 'integer',
        'status_id'      => 'integer',
    ];

    public function submodulos(): HasMany
    {
        return $this->hasMany(Submodulo::class, 'mod_id', 'id_mod');
    }

    public function vistas(): HasMany
    {
        return $this->hasMany(Vista::class, 'mod_id', 'id_mod');
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'mod_id', 'id_mod');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
