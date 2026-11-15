<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\UnidadCurricularController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Unidad Curricular.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('academico/unidad-curricular', UnidadCurricularController::class)->names('academico.unidad-curricular');
    Route::resource('unidad-curricular', UnidadCurricularController::class)->names('unidad_curricular');
});

