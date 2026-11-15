<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\TipoSeccionController;
use App\Http\Controllers\ProgramacionAcademica\SeccionOperacionesController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas de Operaciones y Catálogos Modulares de Secciones.
 * Corresponden al grupo SECCIONES en la barra lateral.
 */
Route::middleware(['web', 'auth'])->group(function () {
    // 1. Sección (SUD)
    Route::get('seccion-sud', [SeccionOperacionesController::class, 'sudIndex'])->name('seccion_unidad_docente.index');

    // 2. Tipo De Sección (Resource CRUD completo sobre la tabla `tipo_seccion`)
    Route::resource('tipos-seccion', TipoSeccionController::class)
        ->parameters(['tipos-seccion' => 'tipo_seccion'])
        ->names('tipo_seccion');

    // 3. Abrir Sección
    Route::get('secciones-abrir', [SeccionOperacionesController::class, 'abrirIndex'])->name('seccion.abrir');
    Route::post('secciones/{id}/cambiar-estado', [SeccionOperacionesController::class, 'cambiarEstadoSeccion'])->name('seccion.cambiar_estado');

    // 4. Cerrar Sección
    Route::get('secciones-cerrar', [SeccionOperacionesController::class, 'cerrarIndex'])->name('seccion.cerrar');

    // 5. Fusionar Secciones (pronto)
    Route::get('secciones-fusionar', [SeccionOperacionesController::class, 'fusionarIndex'])->name('seccion.fusionar');

    // 6. Cambiar Capacidad Por Sección
    Route::get('secciones-capacidad', [SeccionOperacionesController::class, 'capacidadIndex'])->name('seccion.capacidad');
    Route::patch('secciones/{id}/capacidad', [SeccionOperacionesController::class, 'actualizarCapacidad'])->name('seccion.actualizar_capacidad');
});
