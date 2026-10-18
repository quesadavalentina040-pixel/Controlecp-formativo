<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlECP\Http\Controllers\ControlECPController;
use Modules\ControlECP\Http\Controllers\ElementoController;

/*
|--------------------------------------------------------------------------
| Web Routes - Módulo Control ECP (Escuela Cultura de Paz)
|--------------------------------------------------------------------------
*/

// --- 1. RUTA PÚBLICA (Landing / Index accesible antes de iniciar sesión) ---
Route::get('/control-ecp', [ControlECPController::class, 'index'])->name('controlecp.index');
Route::get('/controlecp', [ControlECPController::class, 'index']);
Route::get('/control-ecp/inicio', [ControlECPController::class, 'index'])->name('controlecp.welcome');

// --- 2. RUTAS PROTEGIDAS (Gestión interna / CRUD de Elementos y Vistas por Rol) ---
Route::middleware(['auth', 'verified'])->group(function () {

    Route::resource('controlecps', ControlECPController::class)->names('controlecp');

    // Gestión de Elementos
    Route::get('/control-ecp/elementos', [ElementoController::class, 'index'])
        ->name('controlecp.elementos');

    // Formulario para crear
    Route::get('/elementos/create', [ElementoController::class, 'create'])
        ->name('controlecp.create');

    // Guardar elemento
    Route::post('/elementos', [ElementoController::class, 'store'])
        ->name('controlecp.store');

    // Formulario para editar
    Route::get('/elementos/{id}/edit', [ElementoController::class, 'edit'])
        ->name('controlecp.edit');

    // Actualizar elemento
    Route::put('/elementos/{id}', [ElementoController::class, 'update'])
        ->name('controlecp.update');

    // Eliminar elemento
    Route::delete('/elementos/{id}', [ElementoController::class, 'destroy'])
        ->name('controlecp.destroy');

    // --- Panel Administrador ---
    Route::get('/control-ecp/administrador', [ControlECPController::class, 'inicioAdmin'])
        ->name('controlecp.administrador.inicio');

    // --- Panel Instructor ---
    Route::get('/control-ecp/instructor', [ControlECPController::class, 'inicioInstructor'])
        ->name('controlecp.instructor.inicio');
    Route::get('/control-ecp/instructor/cronograma', [ControlECPController::class, 'cronogramaInstructor'])
        ->name('controlecp.instructor.cronograma');
    Route::get('/control-ecp/instructor/momentos', [ControlECPController::class, 'momentosInstructor'])
        ->name('controlecp.instructor.momentos');
    Route::get('/control-ecp/instructor/actividades', [ControlECPController::class, 'actividadesInstructor'])
        ->name('controlecp.instructor.actividades');
    Route::get('/control-ecp/instructor/asistencia', [ControlECPController::class, 'asistenciaInstructor'])
        ->name('controlecp.instructor.asistencia');
    Route::get('/control-ecp/instructor/asesorias', [ControlECPController::class, 'asesoriasInstructor'])
        ->name('controlecp.instructor.asesorias');
    Route::get('/control-ecp/instructor/certificados', [ControlECPController::class, 'certificadosInstructor'])
        ->name('controlecp.instructor.certificados');
    Route::get('/control-ecp/instructor/poe', [ControlECPController::class, 'poeInstructor'])
        ->name('controlecp.instructor.poe');
    Route::get('/control-ecp/instructor/acompaniamiento', [ControlECPController::class, 'acompaniamientoInstructor'])
        ->name('controlecp.instructor.acompaniamiento');

    // --- Panel Aprendiz ---
    Route::get('/control-ecp/aprendiz', [ControlECPController::class, 'inicioAprendiz'])
        ->name('controlecp.aprendiz.inicio');
    Route::get('/control-ecp/aprendiz/cronograma', [ControlECPController::class, 'cronogramaAprendiz'])
        ->name('controlecp.aprendiz.cronograma');
    Route::get('/control-ecp/aprendiz/encuesta', [ControlECPController::class, 'encuestaAprendiz'])
        ->name('controlecp.aprendiz.encuesta');
    Route::get('/control-ecp/aprendiz/asesorias', [ControlECPController::class, 'asesoriasAprendiz'])
        ->name('controlecp.aprendiz.asesorias');
    Route::get('/control-ecp/aprendiz/pqr', [ControlECPController::class, 'pqrAprendiz'])
        ->name('controlecp.aprendiz.pqr');

 });