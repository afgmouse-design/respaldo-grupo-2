<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\TipoUnidadCurricularController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Tipo de Unidad Curricular.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::patch('academico/tipo-unidad-curricular/{tipo_unidad_curricular}/toggle-status', [TipoUnidadCurricularController::class, 'toggleStatus'])
        ->name('academico.tipo-unidad-curricular.toggle_status');
    Route::patch('tipo-unidad-curricular/{tipo_unidad_curricular}/toggle-status', [TipoUnidadCurricularController::class, 'toggleStatus'])
        ->name('tipo-unidad-curricular.toggle_status');

    Route::resource('academico/tipo-unidad-curricular', TipoUnidadCurricularController::class)
        ->names('academico.tipo-unidad-curricular');

    // Alias para compatibilidad con guión bajo y nombres alternativos
    Route::resource('academico/tipo_unidad_curricular', TipoUnidadCurricularController::class)
        ->names('academico.tipo_unidad_curricular');
    Route::resource('tipo-unidad-curricular', TipoUnidadCurricularController::class)
        ->names('tipo-unidad-curricular');
    Route::resource('tipo_unidad_curricular', TipoUnidadCurricularController::class)
        ->names('tipo_unidad_curricular');
});
