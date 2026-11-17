<?php

namespace App\Models\Personas;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Perfil de Persona (tabla `perfil`).
 *
 * Propósito: Catálogo oficial de perfiles universitarios (ESTUDIANTE, DOCENTE, ADMINISTRATIVO,
 * AUTORIDAD, OBRERO, EXTERNO) asignables a una persona mediante `persona_perfil`.
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql` (`id_prf`, `codigo_prf`, `nombre_prf`, `status_id`, `SoftDeletes`).
 */
class Perfil extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'perfil';

    protected $primaryKey = 'id_prf';

    protected $moduloAuditoria = 'Perfil';

    protected $fillable = [
        'codigo_prf',
        'nombre_prf',
        'descripcion_prf',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_prf'    => 'integer',
        'status_id' => 'integer',
    ];

    /**
     * Asignaciones `persona_perfil` correspondientes a este perfil.
     */
    public function personaPerfiles(): HasMany
    {
        return $this->hasMany(PersonaPerfil::class, 'prf_id', 'id_prf');
    }

    /**
     * Personas que poseen este perfil.
     */
    public function personas(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'persona_perfil', 'prf_id', 'per_id', 'id_prf', 'id_per')
            ->withPivot(['id_pep', 'fecha_inicio_pep', 'fecha_fin_pep', 'status_id']);
    }

    /**
     * Scope para perfiles activos (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
