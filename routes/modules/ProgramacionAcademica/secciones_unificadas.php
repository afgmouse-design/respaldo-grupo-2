<?php

use App\Http\Controllers\ProgramacionAcademica\SeccionUnificadaController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del Controlador Unificado de Sección + Carga Académica (`SeccionUnificadaController`).
 *
 * Historial:
 *  - 2026-11-16: Registro inicial de rutas `secciones-unificadas` bajo middleware `web` y `auth`.
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('secciones-unificadas', [SeccionUnificadaController::class, 'index'])
        ->name('secciones_unificadas.index');

    Route::get('secciones-unificadas/buscar', [SeccionUnificadaController::class, 'buscarSeccion'])
        ->name('secciones_unificadas.buscar');

    Route::post('secciones-unificadas', [SeccionUnificadaController::class, 'store'])
        ->name('secciones_unificadas.store');

    Route::match(['put', 'patch'], 'secciones-unificadas/{seccion}', [SeccionUnificadaController::class, 'update'])
        ->whereNumber('seccion')
        ->name('secciones_unificadas.update');
});
