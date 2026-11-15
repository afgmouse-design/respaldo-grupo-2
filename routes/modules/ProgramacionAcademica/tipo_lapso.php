<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\TipoLapsoController;
use Illuminate\Support\Facades\Route;

Route::prefix('tipo-lapso')->name('tipo_lapso.')->group(function () {
    Route::get('/', [TipoLapsoController::class, 'index'])->name('index');
    Route::get('/create', [TipoLapsoController::class, 'create'])->name('create');
    Route::post('/store', [TipoLapsoController::class, 'store'])->name('store');
    
    Route::get('/{tipo_lapso}/edit', [TipoLapsoController::class, 'edit'])->name('edit');
    Route::put('/{tipo_lapso}', [TipoLapsoController::class, 'update'])->name('update');
    Route::get('/{tipo_lapso}/show', [TipoLapsoController::class, 'show'])->name('show');
    Route::delete('/{tipo_lapso}', [TipoLapsoController::class, 'destroy'])->name('destroy');
});