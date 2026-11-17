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
 * Modelo: Tipo de Programa Académico (tabla `tipo_programa`).
 *
 * Propósito: Clasifica programas de formación (PNF, PNFA, Especialización, Diplomado).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a convenciones de `sogac_mio.sql` (`status_id`, `SoftDeletes`).
 */
class TipoPrograma extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'tipo_programa';
    protected $primaryKey = 'id_tpg';
    protected $moduloAuditoria = 'TipoPrograma';

    protected $fillable = [
        'codigo_tpg',
        'nombre_tpg',
        'descripcion_tpg',
        'conducente_grado_tpg',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tpg'               => 'integer',
        'conducente_grado_tpg' => 'boolean',
        'status_id'            => 'integer',
    ];

    public function programas(): HasMany
    {
        return $this->hasMany(Programa::class, 'tpg_id', 'id_tpg');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('tipo_programa')
            ->select(['id_tpg', 'codigo_tpg', 'nombre_tpg', 'conducente_grado_tpg'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_tpg', 'asc')
            ->get();
    }
}
