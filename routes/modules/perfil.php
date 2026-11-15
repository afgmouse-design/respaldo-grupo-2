<?php

use App\Http\Controllers\Personas\Maestros\PerfilController;
use Illuminate\Support\Facades\Route;

// [OPCIÓN B - SOGAC]: Rutas temporalmente resguardadas hasta la restauración del controlador PerfilController (Opción C).
if (class_exists(PerfilController::class)) {
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [PerfilController::class, 'index'])->name('index');
        Route::get('/create', [PerfilController::class, 'create'])->name('create');
        Route::post('/store', [PerfilController::class, 'store'])->name('store');
        
        Route::get('/{perfil}/edit', [PerfilController::class, 'edit'])->name('edit');
        Route::put('/{perfil}', [PerfilController::class, 'update'])->name('update');
        Route::get('/{perfil}/show', [PerfilController::class, 'show'])->name('show');
        Route::delete('/{perfil}', [PerfilController::class, 'destroy'])->name('destroy');
    });
}