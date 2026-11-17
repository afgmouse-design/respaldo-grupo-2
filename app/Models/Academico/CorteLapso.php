<?php

namespace App\Models\Academico;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Corte de Lapso (tabla `corte_lapso`).
 *
 * Propósito: Define los cortes evaluativos y fechas de carga de notas de un lapso académico (`laa_id`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `corte_lapso(id_col)` con FK `laa_id -> lapso_academico.id_laa`,
 *    `status_id` y `SoftDeletes`.
 */
class CorteLapso extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'corte_lapso';
    protected $primaryKey = 'id_col';
    protected $moduloAuditoria = 'CorteLapso';

    protected $fillable = [
        'numero_corte_col',
        'porcentaje_valor_col',
        'fecha_apertura_notas_col',
        'fecha_cierre_notas_col',
        'laa_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_col'                   => 'integer',
        'numero_corte_col'         => 'integer',
        'porcentaje_valor_col'     => 'decimal:2',
        'fecha_apertura_notas_col' => 'datetime',
        'fecha_cierre_notas_col'   => 'datetime',
        'laa_id'                   => 'integer',
        'status_id'                => 'integer',
    ];

    public function lapsoAcademico(): BelongsTo
    {
        return $this->belongsTo(LapsoAcademico::class, 'laa_id', 'id_laa');
    }
}
