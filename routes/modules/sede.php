<?php

use App\Http\Controllers\OrganizacionInstitucional\Maestros\SedeController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del Módulo 2: Organización Institucional y Sedes - Sede Universitaria (SOGAC - UPTP).
 *
 * Propósito: Endpoints RESTful para la gestión de las sedes o núcleos universitarios
 * mediante páginas completas (Standalone / Zero-Modal).
 *
 * Mapeo de parámetro de clave primaria:
 *  - sede: id_sed
 */
Route::prefix('organizacion')->name('organizacion.')->group(function () {
    Route::patch('sede/{id_sed}/status', [SedeController::class, 'toggleStatus'])->name('sede.status');
    Route::resource('sede', SedeController::class)->parameters(['sede' => 'id_sed']);
});

