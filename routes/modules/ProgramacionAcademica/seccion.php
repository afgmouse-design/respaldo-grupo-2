<?php

use App\Http\Controllers\ProgramacionAcademica\Maestros\SeccionController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del maestro y transacciones de Sección (Programación Académica).
 * Se cargan automáticamente bajo el middleware web y auth.
 */
Route::middleware(['web', 'auth'])->group(function () {
    // Consulta pública/estudiantil de Horario por Sección y Semestre
    Route::get('consulta-horarios', [SeccionController::class, 'horarioEstudiante'])->name('seccion.horario_estudiante');

    // Búsqueda AJAX de docentes por cédula/nombre
    Route::get('secciones/buscar-docente', [SeccionController::class, 'buscarDocente'])->name('seccion.buscar_docente');

    // Transacciones de Asignación Docente
    Route::post('secciones/{seccion}/asignar-docente', [SeccionController::class, 'asignarDocente'])->name('seccion.asignar_docente');
    Route::delete('secciones/asignacion-docente/{id}', [SeccionController::class, 'eliminarAsignacionDocente'])->name('seccion.eliminar_asignacion_docente');

    // Transacciones de Bloques Horarios con Motor Anti-Choque
    Route::post('secciones/{seccion}/horarios', [SeccionController::class, 'agregarHorario'])->name('seccion.agregar_horario');
    Route::delete('secciones/horarios/{id}', [SeccionController::class, 'eliminarHorario'])->name('seccion.eliminar_horario');

    // Resource principal
    Route::resource('secciones', SeccionController::class)
        ->parameters(['secciones' => 'seccion'])
        ->names('seccion');

    Route::resource('seccion', SeccionController::class)
        ->parameters(['seccion' => 'seccion'])
        ->names('secciones');
});
