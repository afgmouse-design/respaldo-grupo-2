<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Catálogo de Estados (tabla `status`).
 *
 * Propósito: Catálogo maestro referenciado por `status_id` en todas las tablas del sistema
 * (`1 = Activo`, `2 = Inactivo`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada a `sogac_mio.sql`.
 */
class Status extends Model
{
    use SoftDeletes;

    protected $table = 'status';

    protected $primaryKey = 'id_sta';

    protected $fillable = [
        'nombre_sta',
        'descripcion_sta',
        'modulo_sta',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_sta' => 'integer',
    ];
}
