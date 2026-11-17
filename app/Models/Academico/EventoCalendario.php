<?php

namespace App\Models\Academico;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Evento de Calendario Académico (tabla `evento_calendario`).
 *
 * Propósito: Registra hitos del calendario de un lapso académico (`laa_id`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `evento_calendario(id_evc)` con FK `laa_id -> lapso_academico.id_laa`,
 *    `status_id` y `SoftDeletes`.
 */
class EventoCalendario extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'evento_calendario';
    protected $primaryKey = 'id_evc';
    protected $moduloAuditoria = 'EventoCalendario';

    protected $fillable = [
        'tipo_evento_evc',
        'fecha_inicio_evc',
        'fecha_fin_evc',
        'laa_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_evc'           => 'integer',
        'fecha_inicio_evc' => 'date',
        'fecha_fin_evc'    => 'date',
        'laa_id'           => 'integer',
        'status_id'        => 'integer',
    ];

    public function lapsoAcademico(): BelongsTo
    {
        return $this->belongsTo(LapsoAcademico::class, 'laa_id', 'id_laa');
    }
}
