<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\EjeCurricularController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Eje Curricular.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('academico/eje-curricular', EjeCurricularController::class)->names('academico.eje-curricular');
    Route::resource('eje-curricular', EjeCurricularController::class)->names('academico.eje_curricular');
});


