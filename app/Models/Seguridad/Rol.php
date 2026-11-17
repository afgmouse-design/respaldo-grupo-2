<?php

namespace App\Models\Seguridad;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Rol de Seguridad (tabla `rol`).
 *
 * Propósito: Define roles institucionales vinculados a permisos mediante `rol_permiso`
 * y asignados a usuarios por contexto en `rol_usuario_contexto`.
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql` (`id_rol`, `perfiles_permitidos_rol`, `es_super_admin_rol`).
 */
class Rol extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'rol';

    protected $primaryKey = 'id_rol';

    protected $moduloAuditoria = 'Rol';

    protected $fillable = [
        'dep_id',
        'nombre_rol',
        'descripcion_rol',
        'perfiles_permitidos_rol',
        'es_super_admin_rol',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_rol'                  => 'integer',
        'dep_id'                  => 'integer',
        'perfiles_permitidos_rol' => 'array',
        'es_super_admin_rol'      => 'boolean',
        'status_id'               => 'integer',
    ];

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'pmi_id', 'id_rol', 'id_pmi')
            ->withPivot(['status_id']);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
