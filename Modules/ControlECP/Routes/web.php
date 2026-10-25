<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlECP\Http\Controllers\ControlECPController;
use Modules\ControlECP\Http\Controllers\ElementoController;
use Modules\ControlECP\Http\Controllers\InventarioController;
use Modules\ControlECP\Http\Controllers\MaterialController;
use Modules\ControlECP\Http\Controllers\PqrController;
use Modules\ControlECP\Http\Controllers\UsuarioSyncController;
use Modules\ControlECP\Http\Controllers\FichaSyncController;
use Modules\ControlECP\Http\Controllers\AsistenciaController;
use Modules\ControlECP\Http\Controllers\CertificadoController;
use Modules\ControlECP\Http\Controllers\MomentoController;
use Modules\ControlECP\Http\Controllers\ActividadController;
use Modules\ControlECP\Http\Controllers\ReportesController;

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

    // --- Registro y edición manual de usuarios (HU3, HU4, HU5, HU6) ---
    Route::get('/control-ecp/administrador/usuarios/crear', [UsuarioSyncController::class, 'create'])
        ->name('controlecp.administrador.usuarios.create');
    Route::post('/control-ecp/administrador/usuarios/crear', [UsuarioSyncController::class, 'store'])
        ->name('controlecp.administrador.usuarios.store');
    Route::get('/control-ecp/administrador/usuarios/{id}/editar', [UsuarioSyncController::class, 'editUser'])
        ->name('controlecp.administrador.usuarios.edit');
    Route::put('/control-ecp/administrador/usuarios/{id}', [UsuarioSyncController::class, 'updateUser'])
        ->name('controlecp.administrador.usuarios.update');

    // --- Activar / Desactivar usuario (HU7, HU8) ---
    Route::post('/control-ecp/administrador/usuarios/{id}/desactivar', [UsuarioSyncController::class, 'deactivate'])
        ->name('controlecp.administrador.usuarios.deactivate');
    Route::post('/control-ecp/administrador/usuarios/{id}/activar', [UsuarioSyncController::class, 'activate'])
        ->name('controlecp.administrador.usuarios.activate');

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

    // --- Detalle, activar/desactivar y sync de aprendices (HU2, HU4, HU6, HU7) ---
    Route::get('/control-ecp/administrador/fichas/{id}', [FichaSyncController::class, 'show'])
        ->name('controlecp.administrador.fichas.show');
    Route::post('/control-ecp/administrador/fichas/{id}/activar', [FichaSyncController::class, 'activate'])
        ->name('controlecp.administrador.fichas.activate');
    Route::post('/control-ecp/administrador/fichas/{id}/desactivar', [FichaSyncController::class, 'deactivate'])
        ->name('controlecp.administrador.fichas.deactivate');
    Route::post('/control-ecp/administrador/fichas/{id}/sync-aprendices', [FichaSyncController::class, 'syncAprendices'])
        ->name('controlecp.administrador.fichas.sync_aprendices');

    // --- Momentos pedagógicos (HU1–HU7: CRUD + activar/desactivar + ver actividades) ---
    Route::get('/control-ecp/administrador/momentos', [MomentoController::class, 'index'])
        ->name('controlecp.administrador.momentos');
    Route::get('/control-ecp/momentos', [MomentoController::class, 'index'])
        ->name('controlecp.momentos');
    Route::post('/control-ecp/administrador/momentos', [MomentoController::class, 'store'])
        ->name('controlecp.administrador.momentos.store');
    Route::put('/control-ecp/administrador/momentos/{id}', [MomentoController::class, 'update'])
        ->name('controlecp.administrador.momentos.update');
    Route::delete('/control-ecp/administrador/momentos/{id}', [MomentoController::class, 'destroy'])
        ->name('controlecp.administrador.momentos.destroy');
    Route::post('/control-ecp/administrador/momentos/{id}/activar', [MomentoController::class, 'activate'])
        ->name('controlecp.administrador.momentos.activate');
    Route::post('/control-ecp/administrador/momentos/{id}/desactivar', [MomentoController::class, 'deactivate'])
        ->name('controlecp.administrador.momentos.deactivate');
    Route::get('/control-ecp/administrador/momentos/{id}/actividades', [MomentoController::class, 'actividades'])
        ->name('controlecp.administrador.momentos.actividades');
    Route::get('/control-ecp/administrador/actividades', [ActividadController::class, 'index'])
        ->name('controlecp.administrador.actividades');
    Route::post('/control-ecp/administrador/actividades', [ActividadController::class, 'store'])
        ->name('controlecp.administrador.actividades.store');
    Route::put('/control-ecp/administrador/actividades/{id}', [ActividadController::class, 'update'])
        ->name('controlecp.administrador.actividades.update');
    Route::delete('/control-ecp/administrador/actividades/{id}', [ActividadController::class, 'destroy'])
        ->name('controlecp.administrador.actividades.destroy');
    Route::post('/control-ecp/administrador/actividades/{id}/cambiar-momento', [ActividadController::class, 'cambiarMomento'])
        ->name('controlecp.administrador.actividades.cambiar_momento');
    Route::get('/control-ecp/administrador/actividades/{id}/componentes', [ActividadController::class, 'componentes'])
        ->name('controlecp.administrador.actividades.componentes');
    Route::post('/control-ecp/administrador/actividades/{id}/activar', [ActividadController::class, 'activate'])
        ->name('controlecp.administrador.actividades.activate');
    Route::post('/control-ecp/administrador/actividades/{id}/desactivar', [ActividadController::class, 'deactivate'])
        ->name('controlecp.administrador.actividades.deactivate');
    Route::post('/control-ecp/administrador/actividades/{id}/finalizar', [ActividadController::class, 'finalizar'])
        ->name('controlecp.administrador.actividades.finalizar');
    Route::post('/control-ecp/administrador/actividades/{id}/evidencias', [ActividadController::class, 'subirEvidencia'])
        ->name('controlecp.administrador.actividades.evidencias.store');
    Route::get('/control-ecp/administrador/actividades/{id}/evidencias', [ActividadController::class, 'evidencias'])
        ->name('controlecp.administrador.actividades.evidencias');
    Route::get('/control-ecp/administrador/actividades/{id}/calificaciones', [ActividadController::class, 'calificaciones'])
        ->name('controlecp.administrador.actividades.calificaciones');
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

    // --- Gestión de Inventario (HU-014 - Rol Administrador) ---
    Route::prefix('control-ecp/administrador/inventario')->name('controlecp.administrador.inventario')->group(function () {
        Route::get('/', [InventarioController::class, 'index']);
        Route::get('/create', [InventarioController::class, 'create'])->name('.create');
        Route::post('/', [InventarioController::class, 'store'])->name('.store');
        Route::get('/categorias', [InventarioController::class, 'categorias'])->name('.categorias');
        Route::post('/categorias', [InventarioController::class, 'categoriaStore'])->name('.categorias.store');
        Route::put('/categorias/{id}', [InventarioController::class, 'categoriaUpdate'])->name('.categorias.update');
        Route::patch('/categorias/{id}/toggle', [InventarioController::class, 'categoriaToggle'])->name('.categorias.toggle');
        Route::get('/movimientos', [InventarioController::class, 'movimientos'])->name('.movimientos');
        Route::get('/reporte', [InventarioController::class, 'reporte'])->name('.reporte');
        Route::get('/{id}', [InventarioController::class, 'show'])->name('.show');
        Route::get('/{id}/edit', [InventarioController::class, 'edit'])->name('.edit');
        Route::put('/{id}', [InventarioController::class, 'update'])->name('.update');
        Route::delete('/{id}', [InventarioController::class, 'destroy'])->name('.destroy');
        Route::post('/{id}/novedades', [InventarioController::class, 'storeNovedad'])->name('.novedades.store');
        Route::post('/{id}/entrega', [InventarioController::class, 'registrarEntrega'])->name('.entrega');
        Route::post('/{id}/devolucion', [InventarioController::class, 'registrarDevolucion'])->name('.devolucion');
        Route::post('/{id}/dar-baja', [InventarioController::class, 'darDeBaja'])->name('.dar-baja');
        Route::delete('/fotos/{idFoto}', [InventarioController::class, 'eliminarFoto'])->name('.fotos.destroy');
        Route::delete('/foto/{idFoto}', [InventarioController::class, 'eliminarFoto'])->name('.foto.eliminar');
        Route::get('/{id}/detalle-json', [InventarioController::class, 'getRecursoDetalle'])->name('.detalle-json');
        Route::post('/verificar-disponibilidad', [InventarioController::class, 'verificarDisponibilidad'])->name('.verificar-disponibilidad');
        Route::post('/movimiento-rapido', [InventarioController::class, 'registrarMovimientoRapido'])->name('.movimiento-rapido');
    });

    // --- Módulo PQRS (Rol Administrador) ---
    Route::get('/control-ecp/administrador/pqr', [PqrController::class, 'index'])
        ->name('controlecp.administrador.pqr');
    Route::get('/control-ecp/administrador/pqr/reporte', [PqrController::class, 'reporte'])
        ->name('controlecp.administrador.pqr.reporte');
    // HU 3: Las rutas create y store del admin están deshabilitadas.
    // El administrador NO puede radicar PQRS. Solo los aprendices pueden hacerlo.
    Route::get('/control-ecp/administrador/pqr/{id}/detalle-json', [PqrController::class, 'getDetalleJson'])
        ->name('controlecp.administrador.pqr.detalle-json');
    Route::get('/control-ecp/administrador/pqr/{id}/historial-json', [PqrController::class, 'getHistorialJson'])
        ->name('controlecp.administrador.pqr.historial-json');
    Route::get('/control-ecp/administrador/pqr/{id}', [PqrController::class, 'show'])
        ->name('controlecp.administrador.pqr.show');
    Route::match(['post', 'put'], '/control-ecp/administrador/pqr/{id}/responder', [PqrController::class, 'responder'])
        ->name('controlecp.administrador.pqr.responder');
    Route::patch('/control-ecp/administrador/pqr/{id}/estado', [PqrController::class, 'cambiarEstado'])
        ->name('controlecp.administrador.pqr.estado');
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

    // --- Módulo Reportes (HU: Generar reportes) ---
    Route::prefix('control-ecp/administrador/reportes')->name('controlecp.administrador.reportes')->group(function () {
        Route::get('/', [ReportesController::class, 'index']);
        Route::get('/asistencias', [ReportesController::class, 'asistencias'])->name('.asistencias');
        Route::get('/encuestas',   [ReportesController::class, 'encuestas'])->name('.encuestas');
        Route::get('/asesorias',   [ReportesController::class, 'asesorias'])->name('.asesorias');
        Route::get('/pqrs',        [ReportesController::class, 'pqrs'])->name('.pqrs');
        Route::get('/certificacion', [ReportesController::class, 'certificacion'])->name('.certificacion');
        Route::get('/inventario',  [ReportesController::class, 'inventario'])->name('.inventario');
        Route::get('/poe',         [ReportesController::class, 'poe'])->name('.poe');
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