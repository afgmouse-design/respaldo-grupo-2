<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\UnidadCurricularController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Unidad Curricular.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::patch('academico/unidad-curricular/{unidad_curricular}/toggle-status', [UnidadCurricularController::class, 'toggleStatus'])
        ->name('academico.unidad-curricular.toggle_status');
    Route::patch('unidad-curricular/{unidad_curricular}/toggle-status', [UnidadCurricularController::class, 'toggleStatus'])
        ->name('unidad_curricular.toggle_status');
    Route::resource('academico/unidad-curricular', UnidadCurricularController::class)->names('academico.unidad-curricular');
    Route::resource('unidad-curricular', UnidadCurricularController::class)->names('unidad_curricular');
});

