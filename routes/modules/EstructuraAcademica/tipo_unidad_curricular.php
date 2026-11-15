<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\TipoUnidadCurricularController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Tipo de Unidad Curricular.
 */
Route::middleware(['web', 'auth'])->group(function () {
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
