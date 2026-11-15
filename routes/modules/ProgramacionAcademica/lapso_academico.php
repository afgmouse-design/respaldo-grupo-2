<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\LapsoAcademicoController;
use Illuminate\Support\Facades\Route;

Route::prefix('lapso-academico')->name('lapso.')->group(function () {
    Route::get('/', [LapsoAcademicoController::class, 'index'])->name('index');
    Route::post('/store', [LapsoAcademicoController::class, 'store'])->name('store');
    Route::put('/{lapsoAcademico}', [LapsoAcademicoController::class, 'update'])->name('update');
    Route::delete('/{lapsoAcademico}', [LapsoAcademicoController::class, 'destroy'])->name('destroy');
});

// Alias de ruta alternativo para compatibilidad total con Ziggy
Route::get('lapsos-academicos', [LapsoAcademicoController::class, 'index'])->name('lapso_academico.index');
