<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\TrayectoController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el módulo Maestro de Trayecto / Nivel Académico.
 */
Route::prefix('academico/trayecto')->name('academico.trayecto.')->group(function () {
    Route::get('/', [TrayectoController::class, 'index'])->name('index');
    Route::get('/create', [TrayectoController::class, 'create'])->name('create');
    Route::post('/store', [TrayectoController::class, 'store'])->name('store');
    Route::get('/{trayecto}/edit', [TrayectoController::class, 'edit'])->name('edit');
    Route::put('/{trayecto}', [TrayectoController::class, 'update'])->name('update');
    Route::get('/{trayecto}/show', [TrayectoController::class, 'show'])->name('show');
    Route::patch('/{trayecto}/toggle-status', [TrayectoController::class, 'toggleStatus'])->name('toggle_status');
    Route::delete('/{trayecto}', [TrayectoController::class, 'destroy'])->name('destroy');
});

// Alias estructura_academica.trayectos.*
Route::prefix('estructura-academica/trayectos')->name('estructura_academica.trayectos.')->group(function () {
    Route::get('/', [TrayectoController::class, 'index'])->name('index');
    Route::get('/create', [TrayectoController::class, 'create'])->name('create');
    Route::post('/store', [TrayectoController::class, 'store'])->name('store');
    Route::get('/{trayecto}/edit', [TrayectoController::class, 'edit'])->name('edit');
    Route::put('/{trayecto}', [TrayectoController::class, 'update'])->name('update');
    Route::get('/{trayecto}/show', [TrayectoController::class, 'show'])->name('show');
    Route::patch('/{trayecto}/toggle-status', [TrayectoController::class, 'toggleStatus'])->name('toggle_status');
    Route::delete('/{trayecto}', [TrayectoController::class, 'destroy'])->name('destroy');
});

// Alias trayecto.*
Route::prefix('trayecto')->name('trayecto.')->group(function () {
    Route::get('/', [TrayectoController::class, 'index'])->name('index');
    Route::get('/create', [TrayectoController::class, 'create'])->name('create');
    Route::post('/store', [TrayectoController::class, 'store'])->name('store');
    Route::get('/{trayecto}/edit', [TrayectoController::class, 'edit'])->name('edit');
    Route::put('/{trayecto}', [TrayectoController::class, 'update'])->name('update');
    Route::get('/{trayecto}/show', [TrayectoController::class, 'show'])->name('show');
    Route::patch('/{trayecto}/toggle-status', [TrayectoController::class, 'toggleStatus'])->name('toggle_status');
    Route::delete('/{trayecto}', [TrayectoController::class, 'destroy'])->name('destroy');
});

