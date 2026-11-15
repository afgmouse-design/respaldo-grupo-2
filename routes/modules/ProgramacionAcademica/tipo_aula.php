<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\TipoAulaController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del maestro Tipo de Aula. Se cargan automáticamente desde routes/web.php.
 * Nombres: tipo_aula.index / store / update / destroy (crear, editar y ver son modales).
 */
Route::resource('tipo-aula', TipoAulaController::class)
    ->parameters(['tipo-aula' => 'tipo_aula'])
    ->names('tipo_aula');

