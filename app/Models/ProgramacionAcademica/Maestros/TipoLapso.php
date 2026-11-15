<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoLapso extends Model
{
    use HasFactory;
    protected $fillable = [
        'tap_codigo',
        'tap_nombre',
        'tap_estatus'
    ];
    protected $table = 'tipo_lapsos';
}