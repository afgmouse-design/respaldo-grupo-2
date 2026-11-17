<?php

namespace App\Models\Personas;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Dedicación Docente (tabla `dedicacion_docente`).
 *
 * Propósito: Define las modalidades de dedicación laboral docente (Dedicación Exclusiva,
 * Tiempo Completo, Medio Tiempo, Tiempo Convencional) y su tope de horas académicas semanales
 * (`horas_academicas_ded`) utilizado al validar la carga horaria en `carga_academica`.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial alineada a `dedicacion_docente` en PostgreSQL (`sogac_mio.sql`).
 */
class DedicacionDocente extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'dedicacion_docente';

    protected $primaryKey = 'id_ded';

    protected $moduloAuditoria = 'DedicacionDocente';

    protected $fillable = [
        'nombre_ded',
        'horas_academicas_ded',
        'horas_administrativas_ded',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_ded'                    => 'integer',
        'horas_academicas_ded'      => 'integer',
        'horas_administrativas_ded' => 'integer',
        'status_id'                 => 'integer',
    ];

    /**
     * Personas docentes adscritas a esta dedicación laboral.
     */
    public function docentes(): HasMany
    {
        return $this->hasMany(Persona::class, 'doc_ded_id', 'id_ded');
    }

    /**
     * Scope para registros activos (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
