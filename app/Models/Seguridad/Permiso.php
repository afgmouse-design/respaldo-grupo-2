<?php

namespace App\Models\Seguridad;

use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Permiso de Acceso (tabla `permiso`).
 *
 * Propósito: Catálogo oficial de permisos (`clave_pmi`, `nombre_pmi`) asociados a un Módulo (`mod_id`)
 * y opcionalmente a una Vista (`vis_id`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql` (`id_pmi`, `mod_id`, `vis_id`, `clave_pmi`, `status_id`).
 */
class Permiso extends Model
{
    use SoftDeletes;
    use UsesStatusCatalog;

    protected $table = 'permiso';

    protected $primaryKey = 'id_pmi';

    protected $fillable = [
        'mod_id',
        'vis_id',
        'clave_pmi',
        'nombre_pmi',
        'descripcion_pmi',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_pmi'    => 'integer',
        'mod_id'    => 'integer',
        'vis_id'    => 'integer',
        'status_id' => 'integer',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'mod_id', 'id_mod');
    }

    public function vista(): BelongsTo
    {
        return $this->belongsTo(Vista::class, 'vis_id', 'id_vis');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'pmi_id', 'rol_id', 'id_pmi', 'id_rol')
            ->withPivot(['status_id']);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
