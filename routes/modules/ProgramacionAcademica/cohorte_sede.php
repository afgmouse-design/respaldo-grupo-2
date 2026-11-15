<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\CohorteSedeController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del maestro Cohorte por Sede.
 */
Route::resource('cohorte-sede', CohorteSedeController::class)
    ->parameters(['cohorte-sede' => 'cohorte_sede'])
    ->names('cohorte_sede');

// Alias de conveniencia para Ziggy
Route::resource('cohorte_sede', CohorteSedeController::class)
    ->parameters(['cohorte_sede' => 'cohorte_sede'])
    ->names('cohorte-sede');

