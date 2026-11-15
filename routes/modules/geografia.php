<?php

use App\Http\Controllers\EstructuraGeografica\Maestros\EstadoController;
use App\Http\Controllers\EstructuraGeografica\Maestros\LocalidadController;
use App\Http\Controllers\EstructuraGeografica\Maestros\MunicipioController;
use App\Http\Controllers\EstructuraGeografica\Maestros\PaisController;
use App\Http\Controllers\EstructuraGeografica\Maestros\ParroquiaController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del Módulo 1: Estructura Geográfica (SOGAC - UPTP).
 *
 * Propósito: Define los endpoints RESTful para la administración de la jerarquía
 * territorial mediante páginas completas (Standalone) sin el uso de modales.
 *
 * Mapeo de parámetros con claves primarias personalizadas:
 *  - pais: id_pai
 *  - estado: id_esd
 *  - municipio: id_mun
 *  - parroquia: id_par
 *  - localidad: id_loc
 */
Route::prefix('geografia')->name('geografia.')->group(function () {
    // Maestro de País
    Route::patch('pais/{id_pai}/status', [PaisController::class, 'toggleStatus'])->name('pais.status');
    Route::resource('pais', PaisController::class)->parameters(['pais' => 'id_pai']);

    // Maestro de Estado
    Route::patch('estado/{id_esd}/status', [EstadoController::class, 'toggleStatus'])->name('estado.status');
    Route::resource('estado', EstadoController::class)->parameters(['estado' => 'id_esd']);

    // Maestro de Municipio
    Route::patch('municipio/{id_mun}/status', [MunicipioController::class, 'toggleStatus'])->name('municipio.status');
    Route::resource('municipio', MunicipioController::class)->parameters(['municipio' => 'id_mun']);

    // Maestro de Parroquia
    Route::patch('parroquia/{id_par}/status', [ParroquiaController::class, 'toggleStatus'])->name('parroquia.status');
    Route::resource('parroquia', ParroquiaController::class)->parameters(['parroquia' => 'id_par']);

    // Maestro de Localidad
    Route::patch('localidad/{id_loc}/status', [LocalidadController::class, 'toggleStatus'])->name('localidad.status');
    Route::resource('localidad', LocalidadController::class)->parameters(['localidad' => 'id_loc']);
});

