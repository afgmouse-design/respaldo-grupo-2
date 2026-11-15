<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Personas\PersonaController;

// [OPCIÓN B - SOGAC]: Rutas temporalmente resguardadas hasta la restauración del controlador PersonaController (Opción C).
if (class_exists(PersonaController::class)) {
    Route::prefix('personas')->name('personas.')->group(function () {
        Route::get('/', [PersonaController::class, 'index'])->name('index');
        Route::get('/create', [PersonaController::class, 'create'])->name('create');
        Route::get('/docente', [PersonaController::class, 'docente'])->name('docente');
        Route::get('/docente/create', [PersonaController::class, 'docenteCreate'])->name('docente.create');
        Route::get('/docente/{per_cedula}/show', [PersonaController::class, 'docenteShow'])->name('docente.show');
        Route::get('/administrativo', [PersonaController::class, 'administrativo'])->name('administrativo');
        Route::get('/administrativo/create', [PersonaController::class, 'administrativoCreate'])->name('administrativo.create');
        Route::get('/administrativo/{per_cedula}/show', [PersonaController::class, 'administrativoShow'])->name('administrativo.show');
        Route::get('/autoridad', [PersonaController::class, 'autoridad'])->name('autoridad');
        Route::get('/autoridad/create', [PersonaController::class, 'autoridadCreate'])->name('autoridad.create');
        Route::get('/autoridad/{per_cedula}/show', [PersonaController::class, 'autoridadShow'])->name('autoridad.show');
        Route::get('/tipo-autoridad', [PersonaController::class, 'tipoAutoridad'])->name('tipoAutoridad');
        Route::get('/modificar-datos-personal', [PersonaController::class, 'modificarDatosPersonal'])->name('modificarDatosPersonal');
        Route::get('/buscar-por-cedula/{per_cedula}', [PersonaController::class, 'buscarPorCedula'])->name('buscarPorCedula');
        Route::get('/subir-foto', [PersonaController::class, 'subirFoto'])->name('subirFoto');
        Route::get('/actualizar-informacion', [PersonaController::class, 'actualizarInformacion'])->name('actualizarInformacion');
        Route::get('/actualizar-estudiante', [PersonaController::class, 'actualizarEstudiante'])->name('actualizarEstudiante');
        Route::post('/', [PersonaController::class, 'store'])->name('store');
        Route::get('/{per_cedula}/show', [PersonaController::class, 'show'])->name('show');
        Route::get('/{per_cedula}/edit', [PersonaController::class, 'edit'])->name('edit');
        Route::put('/{per_cedula}', [PersonaController::class, 'update'])->name('update');
        Route::delete('/{per_cedula}', [PersonaController::class, 'destroy'])->name('destroy');
    });
}
