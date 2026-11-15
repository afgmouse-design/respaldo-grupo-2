<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\SedePnfController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del maestro Sede PNF (oferta académica por sede).
 * Se cargan automáticamente desde routes/modules bajo el grupo de middlewares protegidos.
 * Nombres: sede_pnf.index / store / update / destroy. URL: /sede-pnf
 *
 * Actualización 2026-10-21: Se añade alias de recurso 'sede_pnf' para compatibilidad con Ziggy.
 */
Route::resource('sede-pnf', SedePnfController::class)
    ->parameters(['sede-pnf' => 'sede_pnf'])
    ->names('sede_pnf');

// Alias de conveniencia para Ziggy
Route::resource('sede_pnf', SedePnfController::class)
    ->parameters(['sede_pnf' => 'sede_pnf'])
    ->names('sede-pnf');

