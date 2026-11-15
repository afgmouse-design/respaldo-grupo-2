<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('calificaciones')->name('calificaciones.')->group(function () {
    Route::get('/planificacion', function () {
        return Inertia::render('Calificaciones/Planificacion/index');
    })->name('planificacion');

    Route::get('/carga', function () {
        return Inertia::render('Calificaciones/CargaNotas/index');
    })->name('carga');

    Route::get('/consolidacion', function () {
        return Inertia::render('Calificaciones/Consolidacion/index');
    })->name('consolidacion');
});
