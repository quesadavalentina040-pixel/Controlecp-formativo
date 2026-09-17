<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlECP\Http\Controllers\ControlECPController;
use Modules\ControlECP\Http\Controllers\ElementoController;
use Modules\ControlECP\Http\Controllers\MaterialController;
use Modules\ControlECP\Http\Controllers\PqrController;

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

    Route::get('/control-ecp/administrador/momentos', [ControlECPController::class, 'momentosAdmin'])
        ->name('controlecp.administrador.momentos');
    Route::get('/control-ecp/administrador/actividades', [ControlECPController::class, 'actividadesAdmin'])
        ->name('controlecp.administrador.actividades');
    Route::get('/control-ecp/administrador/cronograma', [ControlECPController::class, 'cronogramaAdmin'])
        ->name('controlecp.administrador.cronograma');

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

    // --- Inventario / Materiales ---
    Route::get('/control-ecp/administrador/inventario', [MaterialController::class, 'index'])
        ->name('controlecp.administrador.inventario');
    Route::get('/control-ecp/administrador/inventario/create', [MaterialController::class, 'create'])
        ->name('controlecp.administrador.inventario.create');
    Route::post('/control-ecp/administrador/inventario', [MaterialController::class, 'store'])
        ->name('controlecp.administrador.inventario.store');
    Route::get('/control-ecp/administrador/inventario/{id}/edit', [MaterialController::class, 'edit'])
        ->name('controlecp.administrador.inventario.edit');
    Route::put('/control-ecp/administrador/inventario/{id}', [MaterialController::class, 'update'])
        ->name('controlecp.administrador.inventario.update');
    Route::delete('/control-ecp/administrador/inventario/{id}', [MaterialController::class, 'destroy'])
        ->name('controlecp.administrador.inventario.destroy');

    // --- PQR ---
    Route::get('/control-ecp/administrador/pqr', [PqrController::class, 'index'])
        ->name('controlecp.administrador.pqr');
    Route::get('/control-ecp/administrador/pqr/create', [PqrController::class, 'create'])
        ->name('controlecp.administrador.pqr.create');
    Route::post('/control-ecp/administrador/pqr', [PqrController::class, 'store'])
        ->name('controlecp.administrador.pqr.store');
    Route::get('/control-ecp/administrador/pqr/{id}', [PqrController::class, 'show'])
        ->name('controlecp.administrador.pqr.show');
    Route::put('/control-ecp/administrador/pqr/{id}/responder', [PqrController::class, 'responder'])
        ->name('controlecp.administrador.pqr.responder');
    Route::delete('/control-ecp/administrador/pqr/{id}', [PqrController::class, 'destroy'])
        ->name('controlecp.administrador.pqr.destroy');

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