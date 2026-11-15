<?php

use App\Http\Controllers\OrganizacionInstitucional\Maestros\CondicionController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del maestro Condición. Se cargan automáticamente desde routes/web.php.
 * Nombres: condicion.index / store / update / destroy (crear, editar y ver son modales).
 */
Route::resource('condicion', CondicionController::class)
    ->names('condicion');

