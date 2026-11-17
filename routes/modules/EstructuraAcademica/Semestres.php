<?php

use App\Http\Controllers\EstructuraAcademica\Maestros\SemestresController;
use Illuminate\Support\Facades\Route;

Route::prefix('semestres')->name('semestres.')->group(function () {
    Route::get('/', [SemestresController::class, 'index'])->name('index');
    Route::get('/create', [SemestresController::class, 'create'])->name ('create');
    Route::post('/store', [SemestresController::class, 'store'])->name ('store');

    Route::get('/{semestre}/edit', [SemestresController::class, 'edit'])->name('edit');
    Route::put('/{semestre}', [SemestresController::class, 'update'])->name('update');
    Route::get('/{semestre}/show', [SemestresController::class, 'show'])->name('show');
    Route::patch('/{semestre}/toggle-status', [SemestresController::class, 'toggleStatus'])->name('toggle_status');
    Route::delete('/{semestre}', [SemestresController::class, 'destroy'])->name('destroy');
});

// Alias para compatibilidad con la nueva nomenclatura Tipo de Período
Route::prefix('academico/tipo-periodo')->name('academico.tipo-periodo.')->group(function () {
    Route::get('/', [SemestresController::class, 'index'])->name('index');
    Route::get('/create', [SemestresController::class, 'create'])->name('create');
    Route::post('/store', [SemestresController::class, 'store'])->name('store');
    Route::get('/{tipo_periodo}/edit', [SemestresController::class, 'edit'])->name('edit');
    Route::put('/{tipo_periodo}', [SemestresController::class, 'update'])->name('update');
    Route::get('/{tipo_periodo}/show', [SemestresController::class, 'show'])->name('show');
    Route::delete('/{tipo_periodo}', [SemestresController::class, 'destroy'])->name('destroy');
    Route::patch('/{tipo_periodo}/toggle-status', [SemestresController::class, 'toggleStatus'])->name('toggle_status');
});

