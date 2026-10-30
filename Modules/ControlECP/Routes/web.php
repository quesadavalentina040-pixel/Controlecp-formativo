<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlECP\Http\Controllers\AsesoriaController;
use Modules\ControlECP\Http\Controllers\ControlECPController;
use Modules\ControlECP\Http\Controllers\ElementoController;
use Modules\ControlECP\Http\Controllers\EncuestaController;
use Modules\ControlECP\Http\Controllers\HistoriaController;
use Modules\ControlECP\Http\Controllers\NotificacionController;
use Modules\ControlECP\Http\Controllers\PoeController;
use Modules\ControlECP\Http\Controllers\PoeProgramacionController;
use Modules\ControlECP\Http\Controllers\TabulacionController;
use Modules\ControlECP\Http\Controllers\UsuarioSyncController;
use Modules\ControlECP\Http\Controllers\FichaSyncController;
use Modules\ControlECP\Http\Controllers\InventarioController;
use Modules\ControlECP\Http\Controllers\PqrController;
use Modules\ControlECP\Http\Controllers\MomentoController;
use Modules\ControlECP\Http\Controllers\ActividadController;
use Modules\ControlECP\Http\Controllers\CronogramaController;
use Modules\ControlECP\Http\Controllers\ReporteController;

/*
|--------------------------------------------------------------------------
| Web Routes - Módulo Control ECP (Escuela Cultura de Paz)
|--------------------------------------------------------------------------
*/

// --- 1. RUTA PÚBLICA (Landing / Index accesible antes de iniciar sesión) ---
Route::get('/control-ecp', [ControlECPController::class, 'index'])->name('controlecp.index');
Route::get('/controlecp', [ControlECPController::class, 'index']);
Route::get('/control-ecp/inicio', [ControlECPController::class, 'index'])->name('controlecp.welcome');
Route::get('/api/v1/controlecp/sicefa-usuarios', [UsuarioSyncController::class, 'mockSicefaApi'])
    ->name('controlecp.api.usuarios.mock');
Route::get('/api/v1/controlecp/sicefa-fichas', [FichaSyncController::class, 'mockSicefaFichasApi'])
    ->name('controlecp.api.fichas.mock');

// --- 2. RUTAS PROTEGIDAS (Gestión interna / CRUD de Elementos y Vistas por Rol) ---
Route::middleware(['auth', 'verified'])->group(function () {

    Route::resource('controlecps', ControlECPController::class)->names('controlecp.resources');

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

    // --- Gestión de usuarios y fichas ---
    Route::get('/control-ecp/administrador/usuarios', [UsuarioSyncController::class, 'index'])
        ->name('controlecp.administrador.usuarios');
    Route::post('/control-ecp/administrador/usuarios/test-api', [UsuarioSyncController::class, 'testApi'])
        ->name('controlecp.administrador.usuarios.test_api');
    Route::post('/control-ecp/administrador/usuarios/sync', [UsuarioSyncController::class, 'sync'])
        ->name('controlecp.administrador.usuarios.sync');
    Route::post('/control-ecp/administrador/usuarios/search-central', [UsuarioSyncController::class, 'searchCentral'])
        ->name('controlecp.administrador.usuarios.search_central');
    Route::post('/control-ecp/administrador/usuarios/link-user', [UsuarioSyncController::class, 'linkUser'])
        ->name('controlecp.administrador.usuarios.link_user');
    Route::post('/control-ecp/administrador/usuarios/config', [UsuarioSyncController::class, 'saveConfig'])
        ->name('controlecp.administrador.usuarios.save_config');
    Route::get('/control-ecp/administrador/usuarios/crear', [UsuarioSyncController::class, 'create'])
        ->name('controlecp.administrador.usuarios.create');
    Route::post('/control-ecp/administrador/usuarios/crear', [UsuarioSyncController::class, 'store'])
        ->name('controlecp.administrador.usuarios.store');
    Route::get('/control-ecp/administrador/usuarios/{id}/editar', [UsuarioSyncController::class, 'editUser'])
        ->name('controlecp.administrador.usuarios.edit');
    Route::put('/control-ecp/administrador/usuarios/{id}', [UsuarioSyncController::class, 'updateUser'])
        ->name('controlecp.administrador.usuarios.update');
    Route::post('/control-ecp/administrador/usuarios/{id}/change-role', [UsuarioSyncController::class, 'changeRole'])
        ->name('controlecp.administrador.usuarios.change_role');
    Route::post('/control-ecp/administrador/usuarios/{id}/desactivar', [UsuarioSyncController::class, 'deactivate'])
        ->name('controlecp.administrador.usuarios.deactivate');
    Route::post('/control-ecp/administrador/usuarios/{id}/activar', [UsuarioSyncController::class, 'activate'])
        ->name('controlecp.administrador.usuarios.activate');

    Route::get('/control-ecp/administrador/fichas', [FichaSyncController::class, 'index'])
        ->name('controlecp.administrador.fichas');
    Route::post('/control-ecp/administrador/fichas/test-api', [FichaSyncController::class, 'testApi'])
        ->name('controlecp.administrador.fichas.test_api');
    Route::post('/control-ecp/administrador/fichas/sync', [FichaSyncController::class, 'sync'])
        ->name('controlecp.administrador.fichas.sync');
    Route::post('/control-ecp/administrador/fichas/config', [FichaSyncController::class, 'saveConfig'])
        ->name('controlecp.administrador.fichas.save_config');
    Route::get('/control-ecp/administrador/fichas/{id}/aprendices', [FichaSyncController::class, 'getAprendices'])
        ->name('controlecp.administrador.fichas.aprendices');
    Route::post('/control-ecp/administrador/fichas/{id}/sync-aprendices', [FichaSyncController::class, 'syncAprendices'])
        ->name('controlecp.administrador.fichas.sync_aprendices');
    Route::post('/control-ecp/administrador/fichas/{id}/activar', [FichaSyncController::class, 'activate'])
        ->name('controlecp.administrador.fichas.activate');
    Route::post('/control-ecp/administrador/fichas/{id}/desactivar', [FichaSyncController::class, 'deactivate'])
        ->name('controlecp.administrador.fichas.deactivate');
    Route::get('/control-ecp/administrador/fichas/{id}', [FichaSyncController::class, 'show'])
        ->name('controlecp.administrador.fichas.show');

    // --- Momentos y actividades ---
    Route::get('/control-ecp/administrador/momentos', [MomentoController::class, 'index'])
        ->name('controlecp.administrador.momentos');
    Route::post('/control-ecp/administrador/momentos', [MomentoController::class, 'store'])
        ->name('controlecp.administrador.momentos.store');
    Route::put('/control-ecp/administrador/momentos/{id}', [MomentoController::class, 'update'])
        ->name('controlecp.administrador.momentos.update');
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

    // --- Cronograma, asistencia y certificados ---
    Route::get('/control-ecp/administrador/cronograma', [CronogramaController::class, 'index'])
        ->name('controlecp.administrador.cronograma');
    Route::get('/control-ecp/administrador/cronograma/eventos', [CronogramaController::class, 'getEventos'])
        ->name('controlecp.administrador.cronograma.eventos');
    Route::post('/control-ecp/administrador/cronograma/verificar-disponibilidad', [CronogramaController::class, 'verificarDisponibilidad'])
        ->name('controlecp.administrador.cronograma.verificar_disponibilidad');
    Route::post('/control-ecp/administrador/cronograma', [CronogramaController::class, 'store'])
        ->name('controlecp.administrador.cronograma.store');
    Route::get('/control-ecp/administrador/cronograma/{id}', [CronogramaController::class, 'show'])
        ->name('controlecp.administrador.cronograma.show');
    Route::put('/control-ecp/administrador/cronograma/{id}', [CronogramaController::class, 'update'])
        ->name('controlecp.administrador.cronograma.update');
    Route::delete('/control-ecp/administrador/cronograma/{id}', [CronogramaController::class, 'destroy'])
        ->name('controlecp.administrador.cronograma.destroy');
    Route::post('/control-ecp/administrador/cronograma/{id}/reasignar', [CronogramaController::class, 'reasignar'])
        ->name('controlecp.administrador.cronograma.reasignar');
    Route::post('/control-ecp/administrador/cronograma/{id}/posponer', [CronogramaController::class, 'posponer'])
        ->name('controlecp.administrador.cronograma.posponer');
    Route::get('/control-ecp/administrador/asistencia', [ControlECPController::class, 'asistenciaAdmin'])
        ->name('controlecp.administrador.asistencia');
    Route::get('/control-ecp/administrador/certificados', [ControlECPController::class, 'certificadosAdmin'])
        ->name('controlecp.administrador.certificados');

    // --- Reportes consolidados HU-016 ---
    Route::get('/control-ecp/administrador/reportes', [ReporteController::class, 'index'])
        ->name('controlecp.administrador.reportes');

    // --- Gestión de Asesorías (Administrador) ---
    Route::prefix('control-ecp/administrador/asesorias')->name('controlecp.administrador.asesorias.')->group(function () {
        Route::get('/',                              [AsesoriaController::class, 'index'])                ->name('index');
        Route::post('/',                             [AsesoriaController::class, 'store'])                ->name('store');
        Route::get('/instructores-disponibles',      [AsesoriaController::class, 'instructoresDisponibles'])->name('instructores-disponibles');
        Route::get('/reporte',                       [AsesoriaController::class, 'reporte'])              ->name('reporte');
        Route::get('/disponibilidad/{usuario}',      [AsesoriaController::class, 'consultarDisponibilidad'])->name('disponibilidad');
        Route::post('/disponibilidad',               [AsesoriaController::class, 'guardarDisponibilidad'])->name('disponibilidad.guardar');
        Route::post('/categorias',                   [AsesoriaController::class, 'storeCategoria'])       ->name('categorias.store');
        Route::put('/categorias/{id}',               [AsesoriaController::class, 'updateCategoria'])      ->name('categorias.update');
        Route::put('/categorias/{id}/estado',        [AsesoriaController::class, 'toggleCategoriaEstado'])->name('categorias.estado');
        Route::put('/seguimiento/{id}',              [AsesoriaController::class, 'corregirSeguimiento'])  ->name('seguimiento.corregir');
        Route::get('/{id}',                          [AsesoriaController::class, 'show'])                 ->name('show');
        Route::put('/{id}',                          [AsesoriaController::class, 'update'])               ->name('update');
        Route::put('/{id}/programar',                [AsesoriaController::class, 'programar'])            ->name('programar');
        Route::put('/{id}/reprogramar',              [AsesoriaController::class, 'reprogramar'])          ->name('reprogramar');
        Route::put('/{id}/cancelar',                 [AsesoriaController::class, 'cancelar'])             ->name('cancelar');
        Route::put('/{id}/finalizar',                [AsesoriaController::class, 'finalizar'])            ->name('finalizar');
        Route::post('/{id}/seguimiento',             [AsesoriaController::class, 'storeSeguimiento'])     ->name('seguimiento');
        Route::post('/{id}/recursos',                [AsesoriaController::class, 'agregarRecurso'])       ->name('recursos');
    });

    // --- Inventario ---
    Route::get('/control-ecp/administrador/inventario', [InventarioController::class, 'index'])
        ->name('controlecp.administrador.inventario');
    Route::prefix('control-ecp/administrador/inventario')->name('controlecp.administrador.inventario.')->group(function () {
        Route::get('/create', [InventarioController::class, 'create'])->name('create');
        Route::post('/', [InventarioController::class, 'store'])->name('store');
        Route::get('/categorias', [InventarioController::class, 'categorias'])->name('categorias');
        Route::post('/categorias', [InventarioController::class, 'categoriaStore'])->name('categorias.store');
        Route::put('/categorias/{id}', [InventarioController::class, 'categoriaUpdate'])->name('categorias.update');
        Route::patch('/categorias/{id}/toggle', [InventarioController::class, 'categoriaToggle'])->name('categorias.toggle');
        Route::get('/movimientos', [InventarioController::class, 'movimientos'])->name('movimientos');
        Route::get('/reporte', [InventarioController::class, 'reporte'])->name('reporte');
        Route::post('/verificar-disponibilidad', [InventarioController::class, 'verificarDisponibilidad'])->name('verificar-disponibilidad');
        Route::post('/movimiento-rapido', [InventarioController::class, 'registrarMovimientoRapido'])->name('movimiento-rapido');
        Route::delete('/fotos/{idFoto}', [InventarioController::class, 'eliminarFoto'])->name('fotos.destroy');
        Route::delete('/foto/{idFoto}', [InventarioController::class, 'eliminarFoto'])->name('foto.eliminar');
        Route::get('/{id}/detalle-json', [InventarioController::class, 'getRecursoDetalle'])->name('detalle-json');
        Route::get('/{id}/edit', [InventarioController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InventarioController::class, 'update'])->name('update');
        Route::delete('/{id}', [InventarioController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/novedades', [InventarioController::class, 'storeNovedad'])->name('novedades.store');
        Route::post('/{id}/entrega', [InventarioController::class, 'registrarEntrega'])->name('entrega');
        Route::post('/{id}/devolucion', [InventarioController::class, 'registrarDevolucion'])->name('devolucion');
        Route::post('/{id}/dar-baja', [InventarioController::class, 'darDeBaja'])->name('dar-baja');
        Route::get('/{id}', [InventarioController::class, 'show'])->name('show');
    });

    // --- PQRS y sus reportes ---
    Route::get('/control-ecp/administrador/pqr', [PqrController::class, 'index'])
        ->name('controlecp.administrador.pqr');
    Route::get('/control-ecp/administrador/pqr/instructores', [PqrController::class, 'index'])
        ->name('controlecp.administrador.pqr.instructores');
    Route::get('/control-ecp/administrador/pqr/reporte', [PqrController::class, 'reporte'])
        ->name('controlecp.administrador.pqr.reporte');
    Route::get('/control-ecp/administrador/pqr/{id}/detalle-json', [PqrController::class, 'getDetalleJson'])
        ->name('controlecp.administrador.pqr.detalle-json');
    Route::get('/control-ecp/administrador/pqr/{id}/historial-json', [PqrController::class, 'getHistorialJson'])
        ->name('controlecp.administrador.pqr.historial-json');
    Route::match(['post', 'put'], '/control-ecp/administrador/pqr/{id}/responder', [PqrController::class, 'responder'])
        ->name('controlecp.administrador.pqr.responder');
    Route::patch('/control-ecp/administrador/pqr/{id}/estado', [PqrController::class, 'cambiarEstado'])
        ->name('controlecp.administrador.pqr.estado');
    Route::delete('/control-ecp/administrador/pqr/{id}', [PqrController::class, 'destroy'])
        ->name('controlecp.administrador.pqr.destroy');
    Route::get('/control-ecp/administrador/pqr/{id}', [PqrController::class, 'show'])
        ->name('controlecp.administrador.pqr.show');

    // --- Repositorio > Historia (Administrador - HU-008) ---
    Route::prefix('control-ecp/administrador/repositorio/historia')->name('controlecp.administrador.repositorio.historia.')->group(function () {
        Route::get('/',                  [HistoriaController::class, 'index'])     ->name('index');
        Route::post('/',                 [HistoriaController::class, 'store'])     ->name('store');
        Route::put('/{id}',              [HistoriaController::class, 'update'])    ->name('update');
        Route::post('/reordenar',        [HistoriaController::class, 'reordenar']) ->name('reordenar');
        Route::patch('/{id}/desactivar', [HistoriaController::class, 'desactivar'])->name('desactivar');
        Route::patch('/{id}/activar',    [HistoriaController::class, 'activar'])   ->name('activar');
    });

    // --- Repositorio > Encuestas (Administrador - HU-009) ---
    Route::prefix('control-ecp/administrador/repositorio/encuestas')->name('controlecp.administrador.repositorio.encuesta.')->group(function () {
        Route::get('/',                               [EncuestaController::class, 'index'])             ->name('index');
        Route::post('/',                              [EncuestaController::class, 'store'])             ->name('store');
        Route::get('/{id}',                           [EncuestaController::class, 'show'])              ->name('show');
        Route::put('/{id}',                           [EncuestaController::class, 'update'])            ->name('update');
        Route::patch('/{id}/activar',                 [EncuestaController::class, 'activar'])           ->name('activar');
        Route::patch('/{id}/desactivar',              [EncuestaController::class, 'desactivar'])         ->name('desactivar');
        Route::post('/{id}/preguntas',                [EncuestaController::class, 'agregarPregunta'])   ->name('preguntas.store');
        Route::put('/{id}/preguntas/{preguntaId}',    [EncuestaController::class, 'editarPregunta'])    ->name('preguntas.update');
        Route::delete('/{id}/preguntas/{preguntaId}', [EncuestaController::class, 'eliminarPregunta'])  ->name('preguntas.destroy');
        Route::post('/{id}/preguntas/reordenar',      [EncuestaController::class, 'reordenarPreguntas'])->name('preguntas.reordenar');
    });

    // --- Repositorio > Tabulación (Administrador - HU-010) ---
    Route::prefix('control-ecp/administrador/repositorio/tabulacion')->name('controlecp.administrador.repositorio.tabulacion.')->group(function () {
        Route::get('/', [TabulacionController::class, 'index'])->name('index');
        Route::get('/datos', [TabulacionController::class, 'datos'])->name('datos');
        Route::get('/exportar', [TabulacionController::class, 'exportarPdf'])->name('exportar');
    });

    // --- Repositorio > POE (Administrador - HU-011) ---
    Route::prefix('control-ecp/administrador/repositorio/poe')->name('controlecp.administrador.repositorio.poe.')->group(function () {
        Route::get('/',                                         [PoeController::class, 'index'])                  ->name('index');
        Route::post('/',                                        [PoeController::class, 'store'])                  ->name('store');
        Route::post('/{id}/nueva-version',                      [PoeController::class, 'nuevaVersion'])           ->name('nueva-version');
        Route::get('/version/{idVersion}/ver',                  [PoeController::class, 'ver'])                    ->name('ver');
        Route::get('/version/{idVersion}/descargar',            [PoeController::class, 'descargar'])              ->name('descargar');
        Route::get('/{id}/historial',                           [PoeController::class, 'historial'])              ->name('historial');
        Route::get('/{id}/lideres',                             [PoeController::class, 'lideresHistorial'])       ->name('lideres');
        Route::post('/{idPoe}/programar',                       [PoeProgramacionController::class, 'store'])      ->name('programar');
        Route::get('/programaciones',                           [PoeProgramacionController::class, 'index'])      ->name('programaciones.index');
        Route::put('/programaciones/{idProgramacion}/reprogramar', [PoeProgramacionController::class, 'reprogramar'])->name('programaciones.reprogramar');
        Route::put('/programaciones/{idProgramacion}/cancelar',    [PoeProgramacionController::class, 'cancelar'])   ->name('programaciones.cancelar');
    });

    // --- Notificaciones (Control ECP) ---
    Route::prefix('control-ecp/notificaciones')->name('controlecp.notificaciones.')->group(function () {
        Route::get('/',              [NotificacionController::class, 'index'])            ->name('index');
        Route::get('/resumen',       [NotificacionController::class, 'resumen'])          ->name('resumen');
        Route::get('/{id}/ir',       [NotificacionController::class, 'ir'])               ->name('ir');
        Route::patch('/{id}/toggle', [NotificacionController::class, 'toggleLeida'])      ->name('toggle');
        Route::post('/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('marcar-todas');
        Route::delete('/{id}',       [NotificacionController::class, 'destroy'])          ->name('destroy');
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
    Route::post('/control-ecp/aprendiz/asesorias', [ControlECPController::class, 'storeAsesoriaAprendiz'])
        ->name('controlecp.aprendiz.asesorias.store');
    Route::get('/control-ecp/aprendiz/pqr', [ControlECPController::class, 'pqrAprendiz'])
        ->name('controlecp.aprendiz.pqr');

 });