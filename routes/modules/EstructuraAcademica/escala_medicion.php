<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\EscalaMedicionController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Escala de Medición.
 *
 * Convención SOGAC: Carga recursiva automática desde routes/web.php.
 */
Route::prefix('estructura-academica/escalas-medicion')->name('estructura_academica.escalas_medicion.')->group(function () {
    Route::get('/', [EscalaMedicionController::class, 'index'])->name('index');
    Route::get('/create', [EscalaMedicionController::class, 'create'])->name('create');
    Route::post('/store', [EscalaMedicionController::class, 'store'])->name('store');
    Route::get('/{escala_medicion}/edit', [EscalaMedicionController::class, 'edit'])->name('edit');
    Route::put('/{escala_medicion}', [EscalaMedicionController::class, 'update'])->name('update');
    Route::get('/{escala_medicion}/show', [EscalaMedicionController::class, 'show'])->name('show');
    Route::patch('/{escala_medicion}/toggle-status', [EscalaMedicionController::class, 'toggleStatus'])->name('toggle_status');
    Route::delete('/{escala_medicion}', [EscalaMedicionController::class, 'destroy'])->name('destroy');
});

// Alias corto para compatibilidad
Route::prefix('escala-medicion')->name('escala_medicion.')->group(function () {
    Route::get('/', [EscalaMedicionController::class, 'index'])->name('index');
    Route::get('/create', [EscalaMedicionController::class, 'create'])->name('create');
    Route::post('/store', [EscalaMedicionController::class, 'store'])->name('store');
    Route::get('/{escala_medicion}/edit', [EscalaMedicionController::class, 'edit'])->name('edit');
    Route::put('/{escala_medicion}', [EscalaMedicionController::class, 'update'])->name('update');
    Route::get('/{escala_medicion}/show', [EscalaMedicionController::class, 'show'])->name('show');
    Route::patch('/{escala_medicion}/toggle-status', [EscalaMedicionController::class, 'toggleStatus'])->name('toggle_status');
    Route::delete('/{escala_medicion}', [EscalaMedicionController::class, 'destroy'])->name('destroy');
});

// Alias escalas.medicion.*
Route::prefix('escalas-medicion')->name('escalas.medicion.')->group(function () {
    Route::get('/', [EscalaMedicionController::class, 'index'])->name('index');
    Route::get('/create', [EscalaMedicionController::class, 'create'])->name('create');
    Route::post('/store', [EscalaMedicionController::class, 'store'])->name('store');
    Route::get('/{escala_medicion}/edit', [EscalaMedicionController::class, 'edit'])->name('edit');
    Route::put('/{escala_medicion}', [EscalaMedicionController::class, 'update'])->name('update');
    Route::get('/{escala_medicion}/show', [EscalaMedicionController::class, 'show'])->name('show');
    Route::patch('/{escala_medicion}/toggle-status', [EscalaMedicionController::class, 'toggleStatus'])->name('toggle_status');
    Route::delete('/{escala_medicion}', [EscalaMedicionController::class, 'destroy'])->name('destroy');
});

// Alias estandarizado academico.escala-medicion.*
Route::prefix('academico/escala-medicion')->name('academico.escala-medicion.')->group(function () {
    Route::get('/', [EscalaMedicionController::class, 'index'])->name('index');
    Route::get('/create', [EscalaMedicionController::class, 'create'])->name('create');
    Route::post('/', [EscalaMedicionController::class, 'store'])->name('store');
    Route::get('/{escala_medicion}/edit', [EscalaMedicionController::class, 'edit'])->name('edit');
    Route::put('/{escala_medicion}', [EscalaMedicionController::class, 'update'])->name('update');
    Route::get('/{escala_medicion}', [EscalaMedicionController::class, 'show'])->name('show');
    Route::delete('/{escala_medicion}', [EscalaMedicionController::class, 'destroy'])->name('destroy');
});

