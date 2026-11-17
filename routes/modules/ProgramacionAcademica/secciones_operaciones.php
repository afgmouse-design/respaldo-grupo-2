<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\TipoSeccionController;
use App\Http\Controllers\ProgramacionAcademica\SeccionOperacionesController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas de Operaciones y Catálogos Modulares de Secciones.
 * Corresponden al grupo SECCIONES en la barra lateral.
 */
Route::middleware(['web', 'auth'])->group(function () {
    // 1. Sección - Unidad Curricular - Docente (SUD / Carga Académica)
    Route::get('seccion-sud', [\App\Http\Controllers\ProgramacionAcademica\SeccionUnidadDocenteController::class, 'index'])->name('seccion_unidad_docente.index');
    Route::get('seccion-sud/crear', [\App\Http\Controllers\ProgramacionAcademica\SeccionUnidadDocenteController::class, 'create'])->name('seccion_unidad_docente.create');
    Route::post('seccion-sud', [\App\Http\Controllers\ProgramacionAcademica\SeccionUnidadDocenteController::class, 'store'])->name('seccion_unidad_docente.store');
    Route::get('seccion-sud/{seccion_unidad_docente}/editar', [\App\Http\Controllers\ProgramacionAcademica\SeccionUnidadDocenteController::class, 'edit'])
        ->whereNumber('seccion_unidad_docente')
        ->name('seccion_unidad_docente.edit');
    Route::match(['put', 'patch'], 'seccion-sud/{seccion_unidad_docente}', [\App\Http\Controllers\ProgramacionAcademica\SeccionUnidadDocenteController::class, 'update'])
        ->whereNumber('seccion_unidad_docente')
        ->name('seccion_unidad_docente.update');
    Route::patch('seccion-sud/{seccion_unidad_docente}/estatus', [\App\Http\Controllers\ProgramacionAcademica\SeccionUnidadDocenteController::class, 'alternarEstatus'])
        ->whereNumber('seccion_unidad_docente')
        ->name('seccion_unidad_docente.alternar_estatus');
    Route::delete('seccion-sud/{seccion_unidad_docente}', [\App\Http\Controllers\ProgramacionAcademica\SeccionUnidadDocenteController::class, 'destroy'])
        ->whereNumber('seccion_unidad_docente')
        ->name('seccion_unidad_docente.destroy');

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
