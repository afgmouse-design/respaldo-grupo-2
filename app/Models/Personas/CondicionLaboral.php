<?php

namespace App\Models\Personas;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Condición Laboral (tabla `condicion_laboral`).
 *
 * Propósito: Catálogo de modalidades de contratación laboral para personal docente y administrativo.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial alineada a `condicion_laboral` en PostgreSQL (`sogac_mio.sql`).
 */
class CondicionLaboral extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'condicion_laboral';

    protected $primaryKey = 'id_cla';

    protected $moduloAuditoria = 'CondicionLaboral';

    protected $fillable = [
        'nombre_cla',
        'descripcion_cla',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_cla'    => 'integer',
        'status_id' => 'integer',
    ];

    public function docentes(): HasMany
    {
        return $this->hasMany(Persona::class, 'doc_cla_id', 'id_cla');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
