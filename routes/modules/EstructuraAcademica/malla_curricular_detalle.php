<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\MallaCurricularDetalleController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Detalle de Malla Curricular.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('academico/malla-curricular-detalle', MallaCurricularDetalleController::class)->names('academico.malla-curricular-detalle');
    Route::resource('malla-curricular-detalle', MallaCurricularDetalleController::class)->names('malla-curricular-detalle');
});

