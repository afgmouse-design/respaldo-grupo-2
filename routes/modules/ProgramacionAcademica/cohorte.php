<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\CohorteController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del maestro Cohorte.
 */
Route::patch('cohorte/{cohorte}/alternar-estatus', [CohorteController::class, 'alternarEstatus'])->name('cohorte.alternar-estatus');
Route::resource('cohorte', CohorteController::class)
    ->names('cohorte');

