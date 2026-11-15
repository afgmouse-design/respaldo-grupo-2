<?php

namespace App\Models\ProgramacionAcademica;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo: Asignación Unidad Curricular - Docente por Sección.
 * Módulo: Programación Académica / Oferta y Carga Docente.
 */
class SeccionUnidadDocente extends Model
{
    use RegistraEnBitacora;

    protected $table = 'seccion_unidad_docente';

    protected $primaryKey = 'id_sud';

    protected $moduloAuditoria = 'SeccionUnidadDocente';

    protected $fillable = [
        'sec_id',
        'mcd_id',
        'docente_id',
        'tipo_contratacion',
        'horas_semanales',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_sud'          => 'integer',
        'sec_id'          => 'integer',
        'mcd_id'          => 'integer',
        'docente_id'      => 'integer',
        'horas_semanales' => 'integer',
        'status'          => 'boolean',
        'status_id'       => 'integer',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'sec_id', 'id_sec');
    }

    public function mallaCurricularDetalle(): BelongsTo
    {
        return $this->belongsTo(MallaCurricularDetalle::class, 'mcd_id', 'id_mcd');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id', 'id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(SeccionHorario::class, 'sud_id', 'id_sud');
    }
}

