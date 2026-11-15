<?php

namespace App\Models\ProgramacionAcademica;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo: CargaAcademica (tabla `carga_academica` en PostgreSQL).
 * Módulo 9: Oferta, Planificación e Inscripción.
 */
class CargaAcademica extends Model
{
    use RegistraEnBitacora;

    protected $table = 'carga_academica';

    protected $primaryKey = 'id_caa';

    protected $moduloAuditoria = 'CargaAcademica';

    protected $fillable = [
        'sec_id',
        'unc_id',
        'docente_per_id',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_caa'         => 'integer',
        'sec_id'         => 'integer',
        'unc_id'         => 'integer',
        'docente_per_id' => 'integer',
        'status'         => 'boolean',
        'status_id'      => 'integer',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'sec_id', 'id_sec');
    }

    public function unidadCurricular(): BelongsTo
    {
        return $this->belongsTo(UnidadCurricular::class, 'unc_id', 'id_unc');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_per_id', 'id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'caa_id', 'id_caa');
    }
}

