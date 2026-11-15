<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('bienestar-salud')->name('bienestar.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('BienestarSalud/index');
    })->name('index');
});
