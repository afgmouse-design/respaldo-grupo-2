<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Modalidad de Estudio (tabla `modalidad_estudio`).
 *
 * Propósito: Clasifica la modalidad de dictado de los programas (Presencial, Semipresencial, Virtual).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a convenciones de `sogac_mio.sql` (`status_id`, `SoftDeletes`).
 */
class ModalidadEstudio extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'modalidad_estudio';
    protected $primaryKey = 'id_moe';
    protected $moduloAuditoria = 'ModalidadEstudio';

    protected $fillable = [
        'codigo_moe',
        'nombre_moe',
        'descripcion_moe',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_moe'    => 'integer',
        'status_id' => 'integer',
    ];

    public function programas(): HasMany
    {
        return $this->hasMany(Programa::class, 'moe_id', 'id_moe');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('modalidad_estudio')
            ->select(['id_moe', 'codigo_moe', 'nombre_moe'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_moe', 'asc')
            ->get();
    }
}
