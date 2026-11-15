<?php

use App\Http\Controllers\ProgramacionAcademica\InformesProgramacionController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del grupo INFORMES (Reportes y Cuadrículas).
 */
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('informes-programacion')->name('informes.')->group(function () {
        Route::get('chequeo-intensivo', [InformesProgramacionController::class, 'chequeoIntensivo'])->name('chequeo_intensivo');
        Route::get('chequeo-intensivo/exportar-pdf', [InformesProgramacionController::class, 'exportarChequeoIntensivoPdf'])->name('chequeo_intensivo.pdf');
        Route::get('chequeo-intensivo/guia-pdf', [InformesProgramacionController::class, 'descargarGuiaIntensivoPdf'])->name('chequeo_intensivo.guia_pdf');
        Route::get('clases', [InformesProgramacionController::class, 'listadoClases'])->name('clases');
        Route::get('secciones', [InformesProgramacionController::class, 'listadoSecciones'])->name('secciones');
        Route::get('secciones-abiertas', [InformesProgramacionController::class, 'seccionesAbiertas'])->name('secciones_abiertas');
        Route::get('horario-aula', [InformesProgramacionController::class, 'horarioAula'])->name('horario_aula');
        Route::get('horario-docente', [InformesProgramacionController::class, 'horarioDocente'])->name('horario_docente');
        Route::get('horario-seccion', [InformesProgramacionController::class, 'horarioSeccion'])->name('horario_seccion');
        Route::get('horarios-semanal-turnos/pdf', [InformesProgramacionController::class, 'exportarHorariosSemanalesTurnosPdf'])->name('horarios_semanal_turnos_pdf');
    });

    // Rutas directas para el módulo de Intensivos
    Route::get('intensivo', [InformesProgramacionController::class, 'chequeoIntensivo'])->name('intensivo.index');
    Route::get('intensivos', [InformesProgramacionController::class, 'chequeoIntensivo'])->name('intensivos.index');
    Route::get('cursos-intensivos', [InformesProgramacionController::class, 'chequeoIntensivo'])->name('cursos_intensivos.index');
    Route::get('programacion-academica/intensivo', [InformesProgramacionController::class, 'chequeoIntensivo'])->name('programacion.intensivo');
});

