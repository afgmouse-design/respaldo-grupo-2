<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('expediente-documental')->name('expediente.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('ExpedienteDocumental/index');
    })->name('documentos');
});
