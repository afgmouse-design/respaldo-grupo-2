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
 * Modelo: Régimen de Estudio (tabla `regimen_estudio`).
 *
 * Propósito: Clasifica el régimen académico de los programas (Diurno, Nocturno, Fines de Semana).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a convenciones de `sogac_mio.sql` (`status_id`, `SoftDeletes`).
 */
class RegimenEstudio extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'regimen_estudio';
    protected $primaryKey = 'id_ree';
    protected $moduloAuditoria = 'RegimenEstudio';

    protected $fillable = [
        'codigo_ree',
        'nombre_ree',
        'descripcion_ree',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_ree'    => 'integer',
        'status_id' => 'integer',
    ];

    public function programas(): HasMany
    {
        return $this->hasMany(Programa::class, 'ree_id', 'id_ree');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('regimen_estudio')
            ->select(['id_ree', 'codigo_ree', 'nombre_ree'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_ree', 'asc')
            ->get();
    }
}
