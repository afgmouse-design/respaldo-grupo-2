<?php

namespace App\Models\ProgramacionAcademica;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\ProgramacionAcademica\Maestros\Aula;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo: Bloque Horario de Sección y Aula física.
 * Módulo: Programación Académica / Planificación de Horarios.
 */
class SeccionHorario extends Model
{
    use RegistraEnBitacora;

    protected $table = 'seccion_horario';

    protected $primaryKey = 'id_sho';

    protected $moduloAuditoria = 'SeccionHorario';

    protected $fillable = [
        'sud_id',
        'aul_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'tipo_sesion',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_sho'     => 'integer',
        'sud_id'     => 'integer',
        'aul_id'     => 'integer',
        'dia_semana' => 'integer',
        'status'     => 'boolean',
        'status_id'  => 'integer',
    ];

    public function seccionUnidadDocente(): BelongsTo
    {
        return $this->belongsTo(SeccionUnidadDocente::class, 'sud_id', 'id_sud');
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'aul_id', 'id_aul');
    }
}

