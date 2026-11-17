<?php

namespace App\Models\OrganizacionInstitucional\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: DependenciaInstitucion (tabla `dependencia_institucion`).
 *
 * Propósito: Representa la dependencia pública/privada o adscripción de la universidad.
 *
 * Historial:
 *  - 2026-11-14: Creación inicial alineada a `dependencia_institucion(id_dpi)` en `sogac_mio.sql`.
 */
class DependenciaInstitucion extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'dependencia_institucion';
    protected $primaryKey = 'id_dpi';
    protected $moduloAuditoria = 'DependenciaInstitucion';

    protected $fillable = [
        'nombre_dpi',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_dpi'    => 'integer',
        'status_id' => 'integer',
    ];

    public function universidades(): HasMany
    {
        return $this->hasMany(Universidad::class, 'dpi_id', 'id_dpi');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('dependencia_institucion')
            ->select(['id_dpi', 'nombre_dpi'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_dpi', 'asc')
            ->get();
    }
}
