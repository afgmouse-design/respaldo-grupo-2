<?php

use App\Http\Controllers\ProgramacionAcademica\EspacioTiempoController;
use App\Http\Controllers\ProgramacionAcademica\Maestros\TurnoController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del grupo ESPACIOS Y TIEMPO (Turnos y Horarios).
 */
Route::middleware(['web', 'auth'])->group(function () {
    // 1. Turno (Resource CRUD completo sobre la tabla `turno`)
    Route::resource('turnos-academicos', TurnoController::class)
        ->parameters(['turnos-academicos' => 'turno'])
        ->names('turno');

    // 2. Horarios
    Route::get('programacion-horarios', [EspacioTiempoController::class, 'horariosIndex'])->name('horarios.index');
    Route::post('programacion-horarios/asignar-directo', [EspacioTiempoController::class, 'asignarHorarioDirecto'])->name('horarios.asignar_directo');
    Route::delete('programacion-horarios/eliminar-bloque/{id}', [EspacioTiempoController::class, 'eliminarBloqueHorario'])->name('horarios.eliminar_bloque');
});
