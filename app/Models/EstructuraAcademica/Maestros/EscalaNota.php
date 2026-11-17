<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Nota / Escala de Nota (tabla `escala_nota`).
 *
 * Propósito: Equivalencias de calificación cuantitativa/cualitativa asociadas a una
 * escala de medición (`esm_id -> escala_medicion.id_esm`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a convenciones de `sogac_mio.sql` (`status_id`, `SoftDeletes`).
 */
class EscalaNota extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'escala_nota';
    protected $primaryKey = 'id_esn';
    protected $moduloAuditoria = 'EscalaNota';

    protected $fillable = [
        'nota_original_esn',
        'nota_100_esn',
        'nota_20_esn',
        'esm_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_esn'            => 'integer',
        'nota_original_esn' => 'decimal:2',
        'nota_100_esn'      => 'decimal:2',
        'nota_20_esn'       => 'decimal:2',
        'esm_id'            => 'integer',
        'status_id'         => 'integer',
    ];

    public function escalaMedicion(): BelongsTo
    {
        return $this->belongsTo(EscalaMedicion::class, 'esm_id', 'id_esm');
    }
}
