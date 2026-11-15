<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Unidad Curricular Prelación (tabla `unidad_curricular_prelacion`).
 * Módulo: Estructura Académica / Currículo.
 * Propósito: Define las asignaturas o requisitos previos obligatorios para poder cursar una materia en la Malla Curricular.
 */
class UnidadCurricularPrelacion extends Model
{
    use RegistraEnBitacora, SoftDeletes;

    protected $table = 'unidad_curricular_prelacion';

    protected $primaryKey = 'id_ucp';

    protected $moduloAuditoria = 'UnidadCurricularPrelacion';

    protected $fillable = [
        'mcd_id',
        'mcd_prelacion_id',
        'tipo_prelacion',
        'creditos_requeridos',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_ucp'              => 'integer',
        'mcd_id'              => 'integer',
        'mcd_prelacion_id'    => 'integer',
        'creditos_requeridos' => 'integer',
        'status'              => 'boolean',
        'status_id'           => 'integer',
    ];

    /**
     * Renglón de malla que exige la prelación.
     */
    public function detalleMalla(): BelongsTo
    {
        return $this->belongsTo(MallaCurricularDetalle::class, 'mcd_id', 'id_mcd');
    }

    /**
     * Renglón de malla que actúa como materia requisito previo.
     */
    public function detallePrelacion(): BelongsTo
    {
        return $this->belongsTo(MallaCurricularDetalle::class, 'mcd_prelacion_id', 'id_mcd');
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

