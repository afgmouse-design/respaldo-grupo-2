<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('secciones-docente')->name('secciones.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('SeccionesDocente/index');
    })->name('docente');
});
