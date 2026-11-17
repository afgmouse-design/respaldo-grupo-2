<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Regla de Prelación (tabla `regla_prelacion`).
 *
 * Propósito: Modelo oficial de `sogac_mio.sql` (reemplaza a `unidad_curricular_prelacion`)
 * que define prelaciones entre unidades curriculares (`unc_origen_id` -> `unc_destino_id`),
 * distinguiendo requisito `'Aprobada'` vs `'Cursada'` y unidades de crédito mínimas.
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `regla_prelacion(id_rep)` con `status_id` y `SoftDeletes`.
 */
class ReglaPrelacion extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'regla_prelacion';
    protected $primaryKey = 'id_rep';
    protected $moduloAuditoria = 'ReglaPrelacion';

    protected $fillable = [
        'tipo_requisito_rep',
        'condicion_unidades_credito_rep',
        'unc_destino_id',
        'unc_origen_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_rep'                         => 'integer',
        'condicion_unidades_credito_rep' => 'integer',
        'unc_destino_id'                 => 'integer',
        'unc_origen_id'                  => 'integer',
        'status_id'                      => 'integer',
    ];

    public function unidadDestino(): BelongsTo
    {
        return $this->belongsTo(UnidadCurricular::class, 'unc_destino_id', 'id_unc');
    }

    public function unidadOrigen(): BelongsTo
    {
        return $this->belongsTo(UnidadCurricular::class, 'unc_origen_id', 'id_unc');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }
}
