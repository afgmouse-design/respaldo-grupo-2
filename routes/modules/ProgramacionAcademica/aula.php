<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\AulaController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del maestro Aula. Se cargan automáticamente desde routes/web.php.
 * Nombres: aula.index / store / update / destroy (crear, editar y ver son modales).
 */
Route::resource('aula', AulaController::class)
    ->names('aula');

