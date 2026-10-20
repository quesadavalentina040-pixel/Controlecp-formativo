<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlECP\Http\Controllers\ControlECPController;
use Modules\ControlECP\Http\Controllers\ElementoController;
use Modules\ControlECP\Http\Controllers\MaterialController;
use Modules\ControlECP\Http\Controllers\PqrController;
use Modules\ControlECP\Http\Controllers\UsuarioSyncController;
use Modules\ControlECP\Http\Controllers\FichaSyncController;
use Modules\ControlECP\Http\Controllers\AsistenciaController;
use Modules\ControlECP\Http\Controllers\CertificadoController;

/*
|--------------------------------------------------------------------------
| Web Routes - Módulo Control ECP (Escuela Cultura de Paz)
|--------------------------------------------------------------------------
*/

// --- 1. RUTA PÚBLICA (Landing / Index y Endpoints Mock API SICEFA) ---
Route::get('/control-ecp', [ControlECPController::class, 'index'])->name('controlecp.index');
Route::get('/controlecp', [ControlECPController::class, 'index']);
Route::get('/control-ecp/inicio', [ControlECPController::class, 'index'])->name('controlecp.welcome');
Route::get('/api/v1/controlecp/sicefa-usuarios', [UsuarioSyncController::class, 'mockSicefaApi'])
    ->name('controlecp.api.usuarios.mock');
Route::get('/api/v1/controlecp/sicefa-fichas', [FichaSyncController::class, 'mockSicefaFichasApi'])
    ->name('controlecp.api.fichas.mock');


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

    // --- Gestión de Usuarios (Integración y Sincronización API Central) ---
    Route::get('/control-ecp/administrador/usuarios', [UsuarioSyncController::class, 'index'])
        ->name('controlecp.administrador.usuarios');
    Route::get('/control-ecp/usuarios', [UsuarioSyncController::class, 'index'])
        ->name('controlecp.usuarios');
    Route::post('/control-ecp/administrador/usuarios/test-api', [UsuarioSyncController::class, 'testApi'])
        ->name('controlecp.administrador.usuarios.test_api');
    Route::post('/control-ecp/administrador/usuarios/sync', [UsuarioSyncController::class, 'sync'])
        ->name('controlecp.administrador.usuarios.sync');
    Route::post('/control-ecp/administrador/usuarios/search-central', [UsuarioSyncController::class, 'searchCentral'])
        ->name('controlecp.administrador.usuarios.search_central');
    Route::post('/control-ecp/administrador/usuarios/link-user', [UsuarioSyncController::class, 'linkUser'])
        ->name('controlecp.administrador.usuarios.link_user');
    Route::post('/control-ecp/administrador/usuarios/{id}/change-role', [UsuarioSyncController::class, 'changeRole'])
        ->name('controlecp.administrador.usuarios.change_role');
    Route::post('/control-ecp/administrador/usuarios/config', [UsuarioSyncController::class, 'saveConfig'])
        ->name('controlecp.administrador.usuarios.save_config');

    // --- Gestión de Fichas (Integración y Sincronización API Académica Central) ---
    Route::get('/control-ecp/administrador/fichas', [FichaSyncController::class, 'index'])
        ->name('controlecp.administrador.fichas');
    Route::get('/control-ecp/fichas', [FichaSyncController::class, 'index'])
        ->name('controlecp.fichas');
    Route::post('/control-ecp/administrador/fichas/test-api', [FichaSyncController::class, 'testApi'])
        ->name('controlecp.administrador.fichas.test_api');
    Route::post('/control-ecp/administrador/fichas/sync', [FichaSyncController::class, 'sync'])
        ->name('controlecp.administrador.fichas.sync');
    Route::post('/control-ecp/administrador/fichas/config', [FichaSyncController::class, 'saveConfig'])
        ->name('controlecp.administrador.fichas.save_config');
    Route::get('/control-ecp/administrador/fichas/{id}/aprendices', [FichaSyncController::class, 'getAprendices'])
        ->name('controlecp.administrador.fichas.aprendices');

    Route::get('/control-ecp/administrador/momentos', [ControlECPController::class, 'momentosAdmin'])
        ->name('controlecp.administrador.momentos');
    Route::get('/control-ecp/administrador/actividades', [ControlECPController::class, 'actividadesAdmin'])
        ->name('controlecp.administrador.actividades');
    Route::get('/control-ecp/administrador/cronograma', [ControlECPController::class, 'cronogramaAdmin'])
        ->name('controlecp.administrador.cronograma');

    Route::get('/control-ecp/administrador/asistencia', [AsistenciaController::class, 'adminIndex'])
        ->name('controlecp.administrador.asistencia');
    Route::get('/control-ecp/asistencia', [AsistenciaController::class, 'adminIndex'])
        ->name('controlecp.asistencia');

    // --- Asistencia Instructor (con carga de aprendices desde API) ---
    Route::get('/control-ecp/instructor/asistencia', [AsistenciaController::class, 'instructorIndex'])
        ->name('controlecp.instructor.asistencia');
    Route::get('/control-ecp/instructor/asistencia/aprendices/{fichaId}', [AsistenciaController::class, 'getAprendicesFicha'])
        ->name('controlecp.instructor.asistencia.aprendices');
    Route::post('/control-ecp/instructor/asistencia/guardar', [AsistenciaController::class, 'store'])
        ->name('controlecp.instructor.asistencia.store');
    Route::post('/control-ecp/instructor/asistencia/evidencia', [AsistenciaController::class, 'subirEvidencia'])
        ->name('controlecp.instructor.asistencia.evidencia');

    // --- Asistencia: Editar, Anular, Detalle, Historial (HU-004..010) ---
    Route::put('/control-ecp/asistencia/{id}/editar', [AsistenciaController::class, 'editar'])
        ->name('controlecp.asistencia.editar');
    Route::put('/control-ecp/asistencia/{id}/anular', [AsistenciaController::class, 'anular'])
        ->name('controlecp.asistencia.anular');
    Route::get('/control-ecp/asistencia/detalle/{fichaId}/{actividadId}/{fecha}', [AsistenciaController::class, 'detalle'])
        ->name('controlecp.asistencia.detalle');
    Route::get('/control-ecp/asistencia/aprendiz', [AsistenciaController::class, 'historialAprendiz'])
        ->name('controlecp.asistencia.historial_aprendiz');
    Route::get('/control-ecp/asistencia/{id}/historial', [AsistenciaController::class, 'historialCambios'])
        ->name('controlecp.asistencia.historial_cambios');
    Route::get('/control-ecp/asistencia/reporte', [AsistenciaController::class, 'reporte'])
        ->name('controlecp.asistencia.reporte');
    Route::get('/control-ecp/administrador/asesorias', [ControlECPController::class, 'asesoriasAdmin'])
        ->name('controlecp.administrador.asesorias');
    Route::get('/control-ecp/asesorias', [ControlECPController::class, 'asesoriasAdmin'])
        ->name('controlecp.asesorias');

    Route::get('/control-ecp/administrador/certificados', [CertificadoController::class, 'adminIndex'])
        ->name('controlecp.administrador.certificados');
    Route::get('/control-ecp/certificados', [CertificadoController::class, 'adminIndex'])
        ->name('controlecp.certificados');

    // Certificados API endpoints (AJAX)
    Route::post('/control-ecp/certificados/certificar', [CertificadoController::class, 'certificar'])
        ->name('controlecp.certificados.certificar');
    Route::get('/control-ecp/certificados/{id}/detalle', [CertificadoController::class, 'detalle'])
        ->name('controlecp.certificados.detalle');
    Route::get('/control-ecp/certificados/{id}/historial', [CertificadoController::class, 'historial'])
        ->name('controlecp.certificados.historial');
    Route::get('/control-ecp/certificados/ficha/{fichaId}/progreso', [CertificadoController::class, 'progresoPorFicha'])
        ->name('controlecp.certificados.progreso');
    Route::get('/control-ecp/certificados/reporte', [CertificadoController::class, 'reporte'])
        ->name('controlecp.certificados.reporte');

    // Certificados Instructor (solo lectura)
    Route::get('/control-ecp/instructor/certificados', [CertificadoController::class, 'instructorIndex'])
        ->name('controlecp.instructor.certificados');

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
    Route::get('/control-ecp/instructor/asistencia', [AsistenciaController::class, 'instructorIndex'])
        ->name('controlecp.instructor.asistencia');
    Route::get('/control-ecp/instructor/asesorias', [ControlECPController::class, 'asesoriasInstructor'])
        ->name('controlecp.instructor.asesorias');
    Route::get('/control-ecp/instructor/certificados', [CertificadoController::class, 'instructorIndex'])
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