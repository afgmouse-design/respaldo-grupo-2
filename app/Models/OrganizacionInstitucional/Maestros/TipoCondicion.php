<?php

namespace App\Models\OrganizacionInstitucional\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: TipoCondicion (tabla `tipo_condicion`).
 *
 * Propósito: Preserva intacta la tabla `tipo_condicion` de `sogac_mio.sql`
 * con sus columnas nativas (`tco_codigo`, `tco_nombre`, `tco_estatus`).
 *
 * Historial:
 *  - 2026-11-14: Creación inicial alineada exactamente a `sogac_mio.sql`.
 */
class TipoCondicion extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;

    protected $table = 'tipo_condicion';
    protected $primaryKey = 'id_tco';
    protected $moduloAuditoria = 'TipoCondicion';

    protected $fillable = [
        'tco_codigo',
        'tco_nombre',
        'tco_descripcion',
        'tco_cod_universidad',
        'tco_estatus',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tco'              => 'integer',
        'tco_cod_universidad' => 'integer',
    ];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('tco_estatus', 'A');
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('tipo_condicion')
            ->select(['id_tco', 'tco_codigo', 'tco_nombre'])
            ->where('tco_estatus', 'A')
            ->whereNull('deleted_at')
            ->orderBy('tco_nombre', 'asc')
            ->get();
    }
}
