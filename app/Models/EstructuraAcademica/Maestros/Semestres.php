<?php

namespace App\Models\EstructuraAcademica\Maestros;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semestres extends Model {

    use HasFactory;
    protected $fillable = [
        'codigo',
        'nombre',
        'numero',
        'cod_trayecto',
        'status'
    ];
    protected $table = 'semestres';
}