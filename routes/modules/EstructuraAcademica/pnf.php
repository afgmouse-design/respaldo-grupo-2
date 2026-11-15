<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\PnfController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de PNF (Programa Nacional de Formación).
 */
Route::middleware(['web', 'auth'])->group(function () {
    // Rutas para alternar estatus
    Route::patch('academico/pnf/{id}/status', [PnfController::class, 'alternarEstatus'])->name('academico.pnf.status');
    Route::patch('pnf/{id}/status', [PnfController::class, 'alternarEstatus'])->name('pnf.status');

    // Ruta principal resource
    Route::resource('academico/pnf', PnfController::class)->names('academico.pnf');

    // Alias para compatibilidad con menú y navegación
    Route::resource('pnf', PnfController::class)->names('pnf');
    Route::resource('estructura-academica/pnf', PnfController::class)->names('estructura_academica.pnf');
    Route::resource('programa', PnfController::class)->names('programa');
});
