<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\EjeCurricularController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Eje Curricular.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::patch('academico/eje-curricular/{eje_curricular}/toggle-status', [EjeCurricularController::class, 'toggleStatus'])
        ->name('academico.eje-curricular.toggle_status');
    Route::patch('eje-curricular/{eje_curricular}/toggle-status', [EjeCurricularController::class, 'toggleStatus'])
        ->name('academico.eje_curricular.toggle_status');
    Route::resource('academico/eje-curricular', EjeCurricularController::class)->names('academico.eje-curricular');
    Route::resource('eje-curricular', EjeCurricularController::class)->names('academico.eje_curricular');
});


