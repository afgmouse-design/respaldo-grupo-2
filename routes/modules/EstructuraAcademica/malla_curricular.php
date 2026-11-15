<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\MallaCurricularController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Malla Curricular.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('academico/malla-curricular/pnf/{id}/unidades', [MallaCurricularController::class, 'unidadesPorPnf'])->name('academico.malla-curricular.unidades-pnf');
    Route::get('malla-curricular/pnf/{id}/unidades', [MallaCurricularController::class, 'unidadesPorPnf'])->name('malla-curricular.unidades-pnf');

    Route::patch('academico/malla-curricular/{id}/status', [MallaCurricularController::class, 'alternarEstatus'])->name('academico.malla-curricular.status');
    Route::patch('malla-curricular/{id}/status', [MallaCurricularController::class, 'alternarEstatus'])->name('malla_curricular.status');

    Route::resource('academico/malla-curricular', MallaCurricularController::class)->names('academico.malla-curricular');
    Route::resource('malla-curricular', MallaCurricularController::class)->names('malla_curricular');
    Route::resource('malla', MallaCurricularController::class)->names('malla');
});

