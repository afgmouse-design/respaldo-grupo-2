<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\HorarioController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del maestro Horario (Grupo 3: Ángel Gonzales - Oferta y Horarios).
 * Se cargan automáticamente desde routes/web.php.
 * Nombres: horario.index / store / update / destroy (crear, editar y ver se resuelven mediante modales).
 */
Route::resource('horario', HorarioController::class)
    ->parameters(['horario' => 'horario'])
    ->names('horario');

