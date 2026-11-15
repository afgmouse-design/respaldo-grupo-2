<?php

use App\Http\Controllers\OrganizacionInstitucional\Maestros\UniversidadController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del Módulo 2: Organización Institucional y Sedes - Universidad (SOGAC - UPTP).
 *
 * Propósito: Endpoints RESTful para la gestión de la entidad Universidad matriz
 * mediante páginas completas (Standalone / Zero-Modal).
 *
 * Mapeo de parámetro de clave primaria:
 *  - universidad: id_uni
 */
Route::prefix('organizacion')->name('organizacion.')->group(function () {
    Route::patch('universidad/{id_uni}/status', [UniversidadController::class, 'toggleStatus'])->name('universidad.status');
    Route::resource('universidad', UniversidadController::class)->parameters(['universidad' => 'id_uni']);
});

