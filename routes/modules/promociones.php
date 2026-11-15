<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('promociones')->name('promociones.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Promociones/index');
    })->name('index');

    Route::get('/graduandos', function () {
        return Inertia::render('Promociones/Graduandos/index');
    })->name('graduandos');
});
