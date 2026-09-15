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

    Route::get('/control-ecp/administrador/usuarios', [ControlECPController::class, 'usuariosAdmin'])
        ->name('controlecp.administrador.usuarios');
    Route::get('/control-ecp/usuarios', [ControlECPController::class, 'usuariosAdmin'])
        ->name('controlecp.usuarios');

    Route::get('/control-ecp/administrador/fichas', [ControlECPController::class, 'fichasAdmin'])
        ->name('controlecp.administrador.fichas');
    Route::get('/control-ecp/fichas', [ControlECPController::class, 'fichasAdmin'])
        ->name('controlecp.fichas');

    Route::get('/control-ecp/administrador/asistencia', [ControlECPController::class, 'asistenciaAdmin'])
        ->name('controlecp.administrador.asistencia');
    Route::get('/control-ecp/asistencia', [ControlECPController::class, 'asistenciaAdmin'])
        ->name('controlecp.asistencia');

    Route::get('/control-ecp/administrador/asesorias', [ControlECPController::class, 'asesoriasAdmin'])
        ->name('controlecp.administrador.asesorias');
    Route::get('/control-ecp/asesorias', [ControlECPController::class, 'asesoriasAdmin'])
        ->name('controlecp.asesorias');

    Route::get('/control-ecp/administrador/certificados', [ControlECPController::class, 'certificadosAdmin'])
        ->name('controlecp.administrador.certificados');
    Route::get('/control-ecp/certificados', [ControlECPController::class, 'certificadosAdmin'])
        ->name('controlecp.certificados');

            // --- Repositorio (submódulos) ---
    Route::prefix('control-ecp/administrador/repositorio')
        ->name('controlecp.administrador.repositorio.')
        ->group(function () {
            Route::get('/historia', [ControlECPController::class, 'repositorioHistoria'])
                ->name('historia');
            Route::get('/encuesta', [ControlECPController::class, 'repositorioEncuesta'])
                ->name('encuesta');
            Route::get('/tabulacion', [ControlECPController::class, 'repositorioTabulacion'])
                ->name('tabulacion');
            Route::get('/poe', [ControlECPController::class, 'repositorioPoe'])
                ->name('poe');
        });

    // --- Panel Instructor ---
    Route::get('/control-ecp/instructor', [ControlECPController::class, 'inicioInstructor'])
        ->name('controlecp.instructor.inicio');

    // --- Panel Aprendiz ---
    Route::get('/control-ecp/aprendiz', [ControlECPController::class, 'inicioAprendiz'])
        ->name('controlecp.aprendiz.inicio');

 });