<?php

use App\Http\Controllers\Academico\AnoAcademicoController;
use App\Http\Controllers\Academico\LapsoAcademicoController;
use Illuminate\Support\Facades\Route;

Route::prefix('academico')->name('academico.')->group(function () {
    // Año Académico
    Route::patch('ano-academico/{ano_academico}/alternar-estatus', [AnoAcademicoController::class, 'alternarEstatus'])->name('ano-academico.alternar-estatus');
    Route::resource('ano-academico', AnoAcademicoController::class);

    // Lapso Académico
    Route::patch('lapso-academico/{lapso_academico}/alternar-estatus', [LapsoAcademicoController::class, 'alternarEstatus'])->name('lapso-academico.alternar-estatus');
    Route::resource('lapso-academico', LapsoAcademicoController::class);
});

