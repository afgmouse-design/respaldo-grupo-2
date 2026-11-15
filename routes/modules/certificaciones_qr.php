<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('certificaciones-qr')->name('certificaciones.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('CertificacionesQR/index');
    })->name('qr');
});
