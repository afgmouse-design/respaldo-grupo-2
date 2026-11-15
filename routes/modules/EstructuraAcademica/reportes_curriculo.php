<?php

use App\Http\Controllers\EstructuraAcademica\ReportesCurriculoController;
use App\Http\Controllers\Reportes\PdfReporteController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas de Reportes de Currículo y Mallas Académicas.
 */
Route::prefix('academico/reportes')->name('academico.reportes.')->group(function () {
    // 1. Listado De Malla
    Route::get('listado-malla', [ReportesCurriculoController::class, 'listadoMalla'])
        ->name('listado-malla');
    Route::get('listado-malla/excel', [ReportesCurriculoController::class, 'exportarMallaExcel'])
        ->name('listado-malla.excel');
    Route::get('listado-malla/pdf', [PdfReporteController::class, 'exportarMallaPdf'])
        ->name('listado-malla.pdf');

    // 2. Listado De Malla Con Prelación
    Route::get('listado-malla-prelacion', [ReportesCurriculoController::class, 'listadoMallaConPrelacion'])
        ->name('listado-malla-prelacion');
    Route::get('listado-malla-prelacion/excel', [ReportesCurriculoController::class, 'exportarMallaPrelacionExcel'])
        ->name('listado-malla-prelacion.excel');
    Route::get('listado-malla-prelacion/pdf', [PdfReporteController::class, 'exportarMallaPrelacionPdf'])
        ->name('listado-malla-prelacion.pdf');

    // 3. Relación De Unidades
    Route::get('relacion-unidades', [ReportesCurriculoController::class, 'relacionUnidades'])
        ->name('relacion-unidades');
    Route::get('relacion-unidades/excel', [ReportesCurriculoController::class, 'exportarRelacionUnidadesExcel'])
        ->name('relacion-unidades.excel');
    Route::get('relacion-unidades/pdf', [PdfReporteController::class, 'exportarRelacionUnidadesPdf'])
        ->name('relacion-unidades.pdf');

    // 4. Relación De Unidades General
    Route::get('relacion-unidades-general', [ReportesCurriculoController::class, 'relacionUnidadesGeneral'])
        ->name('relacion-unidades-general');
    Route::get('relacion-unidades-general/excel', [ReportesCurriculoController::class, 'exportarRelacionUnidadesGeneralExcel'])
        ->name('relacion-unidades-general.excel');
    Route::get('relacion-unidades-general/pdf', [PdfReporteController::class, 'exportarRelacionUnidadesGeneralPdf'])
        ->name('relacion-unidades-general.pdf');
});

