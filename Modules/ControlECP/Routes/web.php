<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlECP\Http\Controllers\AsesoriaController;
use Modules\ControlECP\Http\Controllers\InstructorAsesoriaController;
use Modules\ControlECP\Http\Controllers\InstructorCertificacionController;
use Modules\ControlECP\Http\Controllers\InstructorEncuestaController;
use Modules\ControlECP\Http\Controllers\InstructorPqrsController;
use Modules\ControlECP\Http\Controllers\InstructorRepositorioController;
use Modules\ControlECP\Http\Controllers\InstructorReporteController;
use Modules\ControlECP\Http\Controllers\AprendizPqrsController;
use Modules\ControlECP\Http\Controllers\AprendizEncuestaController;
use Modules\ControlECP\Http\Controllers\AprendizCalificacionController;
use Modules\ControlECP\Http\Controllers\AprendizCalificacionAsesoriaController;
use Modules\ControlECP\Http\Controllers\AprendizCertificadoController;
use Modules\ControlECP\Http\Controllers\InstructorInventarioController;
use Modules\ControlECP\Http\Controllers\AcompanamientoInstructorController;
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
use Modules\ControlECP\Http\Controllers\AsistenciaController;
use Modules\ControlECP\Http\Controllers\CertificacionController;
use Modules\ControlECP\Http\Controllers\RepositorioEvidenciaController;

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
    Route::post('/control-ecp/administrador/actividades/{id}/encuesta', [ActividadController::class, 'asignarEncuesta'])
        ->name('controlecp.administrador.actividades.encuesta');
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
    Route::post('/control-ecp/administrador/cronograma/{id}/desactivar', [CronogramaController::class, 'desactivar'])
        ->name('controlecp.administrador.cronograma.desactivar');
    Route::post('/control-ecp/administrador/cronograma/{id}/reasignar', [CronogramaController::class, 'reasignar'])
        ->name('controlecp.administrador.cronograma.reasignar');
    Route::post('/control-ecp/administrador/cronograma/{id}/posponer', [CronogramaController::class, 'posponer'])
        ->name('controlecp.administrador.cronograma.posponer');
    Route::get('/control-ecp/administrador/asistencia', [AsistenciaController::class, 'index'])
        ->name('controlecp.administrador.asistencia');
    Route::get('/control-ecp/administrador/asistencia/fichas/{fichaId}/aprendices', [AsistenciaController::class, 'aprendices'])
        ->name('controlecp.administrador.asistencia.aprendices');
    Route::get('/control-ecp/administrador/asistencia/fichas/{fichaId}/actividades', [AsistenciaController::class, 'actividadesProgramadas'])
        ->name('controlecp.administrador.asistencia.actividades');
    Route::post('/control-ecp/administrador/asistencia', [AsistenciaController::class, 'storeManual'])
        ->name('controlecp.administrador.asistencia.store-manual');
    Route::post('/control-ecp/administrador/asistencia/{id}', [AsistenciaController::class, 'store'])
        ->name('controlecp.administrador.asistencia.store');
    Route::put('/control-ecp/administrador/asistencia/registro/{id}', [AsistenciaController::class, 'update'])
        ->name('controlecp.administrador.asistencia.update');
    Route::post('/control-ecp/administrador/asistencia/registro/{id}/anular', [AsistenciaController::class, 'annul'])
        ->name('controlecp.administrador.asistencia.annul');
    Route::post('/control-ecp/administrador/asistencia/{cronogramaId}/finalizar-actividad', [AsistenciaController::class, 'finalizarActividad'])
        ->name('controlecp.administrador.asistencia.finalizar-actividad');
    Route::get('/control-ecp/administrador/certificados', [CertificacionController::class, 'index'])
        ->name('controlecp.administrador.certificados');
    Route::post('/control-ecp/administrador/certificados/{fichaId}/{momentoId}', [CertificacionController::class, 'certificar'])
        ->name('controlecp.administrador.certificados.certificar');
    Route::get('/control-ecp/administrador/certificados/{fichaId}/constancia', [CertificacionController::class, 'descargarConstancia'])
        ->name('controlecp.administrador.certificados.constancia');

    // --- Reportes consolidados HU-016 ---
    Route::get('/control-ecp/administrador/reportes/pdf', [ReporteController::class, 'exportarPdf'])
        ->name('controlecp.administrador.reportes.pdf');
    Route::get('/control-ecp/administrador/reportes', [ReporteController::class, 'index'])
        ->name('controlecp.administrador.reportes');

    // --- Gestión de Asesorías (Administrador) ---
    Route::prefix('control-ecp/administrador/asesorias')->name('controlecp.administrador.asesorias.')->group(function () {
        Route::get('/',                              [AsesoriaController::class, 'index'])                ->name('index');
        Route::get('/instructores-disponibles',      [AsesoriaController::class, 'instructoresDisponibles'])->name('instructores-disponibles');
        Route::get('/reporte',                       [AsesoriaController::class, 'reporte'])              ->name('reporte');
        Route::post('/mi-disponibilidad',             [AsesoriaController::class, 'guardarDisponibilidad'])->name('mi-disponibilidad.guardar');
        Route::post('/mi-disponibilidad/categoria',   [AsesoriaController::class, 'guardarCategoriaDisponibilidad'])->name('mi-disponibilidad.categoria');
        Route::get('/disponibilidad/{usuario}',      [AsesoriaController::class, 'consultarDisponibilidad'])->name('disponibilidad');
        Route::put('/disponibilidad/{id}/estado',     [AsesoriaController::class, 'cambiarEstadoDisponibilidad'])->whereNumber('id')->name('disponibilidad.estado');
        Route::put('/mi-disponibilidad/{id}',         [AsesoriaController::class, 'actualizarDisponibilidad'])->whereNumber('id')->name('mi-disponibilidad.actualizar');
        Route::post('/disponibilidad',               [AsesoriaController::class, 'guardarDisponibilidad'])->name('disponibilidad.guardar');
        Route::post('/categorias',                   [AsesoriaController::class, 'storeCategoria'])       ->name('categorias.store');
        Route::put('/categorias/{id}',               [AsesoriaController::class, 'updateCategoria'])      ->name('categorias.update');
        Route::put('/categorias/{id}/estado',        [AsesoriaController::class, 'toggleCategoriaEstado'])->name('categorias.estado');
        Route::put('/seguimiento/{id}',              [AsesoriaController::class, 'corregirSeguimiento'])  ->name('seguimiento.corregir');
        Route::put('/solicitudes/{id}/aceptar', [InstructorAsesoriaController::class, 'aceptarSolicitudInstructor'])
            ->whereNumber('id')->name('solicitudes.aceptar');
        Route::put('/solicitudes/{id}/rechazar', [InstructorAsesoriaController::class, 'rechazarSolicitudInstructor'])
            ->whereNumber('id')->name('solicitudes.rechazar');
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
    Route::get('/control-ecp/administrador/repositorio/evidencias', [RepositorioEvidenciaController::class, 'index'])
        ->name('controlecp.administrador.repositorio.evidencias');

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
    Route::post('/control-ecp/instructor/cronograma/{id}/posponer', [ControlECPController::class, 'posponerCronogramaInstructor'])
        ->name('controlecp.instructor.cronograma.posponer');
    Route::get('/control-ecp/instructor/momentos', [ControlECPController::class, 'momentosInstructor'])
        ->name('controlecp.instructor.momentos');
    Route::get('/control-ecp/instructor/actividades', [ControlECPController::class, 'actividadesInstructor'])
        ->name('controlecp.instructor.actividades');
    Route::post('/control-ecp/instructor/actividades/{actividadId}/encuesta', [ControlECPController::class, 'encuestaActividadInstructor'])
        ->name('controlecp.instructor.actividades.encuesta');
    Route::get('/control-ecp/instructor/actividades/{actividadId}/evidencias', [ControlECPController::class, 'evidenciasActividadInstructor'])
        ->name('controlecp.instructor.actividades.evidencias');
    Route::post('/control-ecp/instructor/actividades/{actividadId}/evidencias', [ControlECPController::class, 'subirEvidenciaActividadInstructor'])
        ->name('controlecp.instructor.actividades.evidencias.store');
    Route::get('/control-ecp/instructor/actividades/{actividadId}/calificaciones', [ControlECPController::class, 'calificacionesActividadInstructor'])
        ->name('controlecp.instructor.actividades.calificaciones');
    Route::post('/control-ecp/instructor/cronograma/{cronogramaId}/finalizar', [ControlECPController::class, 'finalizarActividadInstructor'])
        ->name('controlecp.instructor.cronograma.finalizar');
    Route::get('/control-ecp/instructor/asistencia', [ControlECPController::class, 'asistenciaInstructor'])
        ->name('controlecp.instructor.asistencia');
    Route::get('/control-ecp/instructor/asesorias', [InstructorAsesoriaController::class, 'indexInstructor'])
        ->name('controlecp.instructor.asesorias');
    Route::post('/control-ecp/instructor/asesorias/disponibilidad', [InstructorAsesoriaController::class, 'guardarDisponibilidad'])
        ->name('controlecp.instructor.asesorias.disponibilidad');
    Route::post('/control-ecp/instructor/asesorias/disponibilidad/franjas', [InstructorAsesoriaController::class, 'guardarFranjaDisponibilidad'])
        ->name('controlecp.instructor.asesorias.disponibilidad.franjas.store');
    Route::post('/control-ecp/instructor/asesorias/disponibilidad/categoria', [InstructorAsesoriaController::class, 'guardarCategoriaDisponibilidad'])
        ->name('controlecp.instructor.asesorias.disponibilidad.categoria');
    Route::put('/control-ecp/instructor/asesorias/disponibilidad/franjas/{id}', [InstructorAsesoriaController::class, 'actualizarFranjaDisponibilidad'])
        ->whereNumber('id')
        ->name('controlecp.instructor.asesorias.disponibilidad.franjas.update');
    Route::put('/control-ecp/instructor/asesorias/disponibilidad/franjas/{id}/estado', [InstructorAsesoriaController::class, 'cambiarEstadoFranjaDisponibilidad'])
        ->whereNumber('id')
        ->name('controlecp.instructor.asesorias.disponibilidad.franjas.estado');
    Route::get('/control-ecp/instructor/asesorias/instructores-disponibles', [InstructorAsesoriaController::class, 'instructoresDisponibles'])
        ->name('controlecp.instructor.asesorias.disponibles');
    Route::post('/control-ecp/instructor/asesorias/solicitudes', [InstructorAsesoriaController::class, 'storeSolicitudInstructor'])
        ->name('controlecp.instructor.asesorias.store');
    Route::put('/control-ecp/instructor/asesorias/{id}/aceptar', [InstructorAsesoriaController::class, 'aceptarSolicitudInstructor'])
        ->whereNumber('id')
        ->name('controlecp.instructor.asesorias.aceptar');
    Route::put('/control-ecp/instructor/asesorias/{id}/rechazar', [InstructorAsesoriaController::class, 'rechazarSolicitudInstructor'])
        ->whereNumber('id')
        ->name('controlecp.instructor.asesorias.rechazar');
    Route::put('/control-ecp/instructor/asesorias/{id}/reprogramar', [InstructorAsesoriaController::class, 'reprogramar'])
        ->name('controlecp.instructor.asesorias.reprogramar');
    Route::put('/control-ecp/instructor/asesorias/{id}/cancelar', [InstructorAsesoriaController::class, 'cancelar'])
        ->name('controlecp.instructor.asesorias.cancelar');
    Route::post('/control-ecp/instructor/asesorias/{id}/seguimiento', [InstructorAsesoriaController::class, 'guardarSeguimiento'])
        ->name('controlecp.instructor.asesorias.seguimiento');
    Route::put('/control-ecp/instructor/asesorias/seguimientos/{seguimientoId}', [InstructorAsesoriaController::class, 'corregirSeguimiento'])
        ->name('controlecp.instructor.asesorias.seguimiento.corregir');
    Route::post('/control-ecp/instructor/asesorias/{id}/recursos', [InstructorAsesoriaController::class, 'agregarRecurso'])
        ->name('controlecp.instructor.asesorias.recursos');
    Route::get('/control-ecp/instructor/certificados', [InstructorCertificacionController::class, 'index'])
        ->name('controlecp.instructor.certificados');
    Route::post('/control-ecp/instructor/certificados/{fichaId}/{momentoId}', [InstructorCertificacionController::class, 'certificar'])
        ->name('controlecp.instructor.certificados.certificar');
    Route::get('/control-ecp/instructor/certificados/{fichaId}/constancia', [InstructorCertificacionController::class, 'descargarConstancia'])
        ->name('controlecp.instructor.certificados.constancia');
    Route::get('/control-ecp/instructor/poe', [ControlECPController::class, 'poeInstructor'])
        ->name('controlecp.instructor.poe');
    Route::get('/control-ecp/instructor/reportes/pdf', [InstructorReporteController::class, 'exportarPdf'])
        ->name('controlecp.instructor.reportes.pdf');
    Route::get('/control-ecp/instructor/reportes', [InstructorReporteController::class, 'index'])
        ->name('controlecp.instructor.reportes');
    Route::get('/control-ecp/instructor/acompaniamiento', [AcompanamientoInstructorController::class, 'index'])
        ->name('controlecp.instructor.acompaniamiento');
    Route::post('/control-ecp/instructor/acompaniamiento/categorias', [AcompanamientoInstructorController::class, 'crearCategoria'])
        ->name('controlecp.instructor.acompaniamiento.categorias.store');
    Route::post('/control-ecp/instructor/acompaniamiento', [AcompanamientoInstructorController::class, 'store'])
        ->name('controlecp.instructor.acompaniamiento.store');
    Route::put('/control-ecp/instructor/acompaniamiento/{accompanimentId}', [AcompanamientoInstructorController::class, 'actualizar'])
        ->name('controlecp.instructor.acompaniamiento.update');
    Route::post('/control-ecp/instructor/acompaniamiento/sesiones/{sessionId}/registrar', [AcompanamientoInstructorController::class, 'registrarSesion'])
        ->name('controlecp.instructor.acompaniamiento.sesiones.registrar');
    Route::post('/control-ecp/instructor/acompaniamiento/sesiones/{sessionId}/no-asistio', [AcompanamientoInstructorController::class, 'noAsistio'])
        ->name('controlecp.instructor.acompaniamiento.sesiones.no-asistio');
    Route::put('/control-ecp/instructor/acompaniamiento/sesiones/{sessionId}/reprogramar', [AcompanamientoInstructorController::class, 'reprogramarSesion'])
        ->name('controlecp.instructor.acompaniamiento.sesiones.reprogramar');
    Route::put('/control-ecp/instructor/acompaniamiento/sesiones/{sessionId}/cancelar', [AcompanamientoInstructorController::class, 'cancelarSesion'])
        ->name('controlecp.instructor.acompaniamiento.sesiones.cancelar');
    Route::put('/control-ecp/instructor/acompaniamiento/sesiones/{sessionId}/corregir', [AcompanamientoInstructorController::class, 'corregirSesion'])
        ->name('controlecp.instructor.acompaniamiento.sesiones.corregir');
    Route::post('/control-ecp/instructor/acompaniamiento/sesiones/{sessionId}/evidencias', [AcompanamientoInstructorController::class, 'guardarEvidencia'])
        ->name('controlecp.instructor.acompaniamiento.sesiones.evidencias.store');
    Route::post('/control-ecp/instructor/acompaniamiento/{accompanimentId}/sesiones', [AcompanamientoInstructorController::class, 'agregarSesiones'])
        ->name('controlecp.instructor.acompaniamiento.sesiones.agregar');
    Route::put('/control-ecp/instructor/acompaniamiento/{accompanimentId}/cerrar', [AcompanamientoInstructorController::class, 'cerrar'])
        ->name('controlecp.instructor.acompaniamiento.cerrar');
    Route::get('/control-ecp/instructor/asistencia/{cronogramaId}/checklist', [ControlECPController::class, 'asistenciaChecklistInstructor'])
        ->name('controlecp.instructor.asistencia.checklist');
    Route::post('/control-ecp/instructor/asistencia/{cronogramaId}/checklist', [ControlECPController::class, 'registrarAsistenciaInstructor'])
        ->name('controlecp.instructor.asistencia.checklist.store');
    Route::post('/control-ecp/instructor/asistencia/{asistenciaId}/anular', [ControlECPController::class, 'anularAsistenciaInstructor'])
        ->name('controlecp.instructor.asistencia.anular');
    Route::get('/control-ecp/instructor/asistencia/reporte/exportar', [ControlECPController::class, 'exportarAsistenciaInstructor'])
        ->name('controlecp.instructor.asistencia.exportar');
    Route::get('/control-ecp/instructor/encuestas', [InstructorEncuestaController::class, 'index'])
        ->name('controlecp.instructor.encuestas');
    Route::post('/control-ecp/instructor/encuestas', [InstructorEncuestaController::class, 'store'])
        ->name('controlecp.instructor.encuestas.store');
    Route::get('/control-ecp/instructor/encuestas/{id}', [InstructorEncuestaController::class, 'show'])
        ->name('controlecp.instructor.encuestas.show');
    Route::put('/control-ecp/instructor/encuestas/{id}', [InstructorEncuestaController::class, 'update'])
        ->name('controlecp.instructor.encuestas.update');
    Route::patch('/control-ecp/instructor/encuestas/{id}/activar', [InstructorEncuestaController::class, 'activar'])
        ->name('controlecp.instructor.encuestas.activar');
    Route::patch('/control-ecp/instructor/encuestas/{id}/desactivar', [InstructorEncuestaController::class, 'desactivar'])
        ->name('controlecp.instructor.encuestas.desactivar');
    Route::post('/control-ecp/instructor/encuestas/{id}/preguntas', [InstructorEncuestaController::class, 'agregarPregunta'])
        ->name('controlecp.instructor.encuestas.preguntas.store');
    Route::put('/control-ecp/instructor/encuestas/{id}/preguntas/{preguntaId}', [InstructorEncuestaController::class, 'actualizarPregunta'])
        ->name('controlecp.instructor.encuestas.preguntas.update');
    Route::delete('/control-ecp/instructor/encuestas/{id}/preguntas/{preguntaId}', [InstructorEncuestaController::class, 'eliminarPregunta'])
        ->name('controlecp.instructor.encuestas.preguntas.destroy');
    Route::prefix('control-ecp/instructor/repositorio')->name('controlecp.instructor.repositorio.')->group(function () {
        Route::get('/evidencias', [InstructorRepositorioController::class, 'evidencias'])->name('evidencias');
        Route::get('/historias', [InstructorRepositorioController::class, 'historias'])->name('historia');
        Route::get('/encuestas', [InstructorRepositorioController::class, 'encuestas'])->name('encuestas');
        Route::get('/tabulacion', [InstructorRepositorioController::class, 'tabulacion'])->name('tabulacion');
        Route::get('/tabulacion/exportar', [InstructorRepositorioController::class, 'exportarTabulacionPdf'])->name('tabulacion.exportar');
    });
    Route::get('/control-ecp/instructor/fichas', [ControlECPController::class, 'fichasInstructor'])
        ->name('controlecp.instructor.fichas');
    Route::get('/control-ecp/instructor/fichas/{fichaId}/detalle', [ControlECPController::class, 'detalleFichaInstructor'])
        ->name('controlecp.instructor.fichas.detalle');
    Route::get('/control-ecp/instructor/fichas/{fichaId}/nomina', [ControlECPController::class, 'nominaFichaInstructor'])
        ->name('controlecp.instructor.fichas.nomina');
    Route::get('/control-ecp/instructor/historia', [ControlECPController::class, 'historiaInstructor'])
        ->name('controlecp.instructor.historia');
    Route::get('/control-ecp/instructor/inventario', [InstructorInventarioController::class, 'index'])
        ->name('controlecp.instructor.inventario');
    Route::get('/control-ecp/instructor/inventario/{id}', [InstructorInventarioController::class, 'show'])
        ->name('controlecp.instructor.inventario.show');
    Route::get('/control-ecp/instructor/inventario/{id}/disponibilidad', [InstructorInventarioController::class, 'disponibilidad'])
        ->name('controlecp.instructor.inventario.disponibilidad');
    Route::post('/control-ecp/instructor/inventario/{id}/entrega', [InstructorInventarioController::class, 'entregar'])
        ->name('controlecp.instructor.inventario.entregar');
    Route::post('/control-ecp/instructor/inventario/{id}/devolucion', [InstructorInventarioController::class, 'devolver'])
        ->name('controlecp.instructor.inventario.devolver');
    Route::post('/control-ecp/instructor/inventario/{id}/novedad', [InstructorInventarioController::class, 'novedad'])
        ->name('controlecp.instructor.inventario.novedad');
    Route::get('/control-ecp/instructor/pqr', [InstructorPqrsController::class, 'index'])
        ->name('controlecp.instructor.pqr');
    Route::get('/control-ecp/instructor/pqr/{id}', [InstructorPqrsController::class, 'show'])
        ->name('controlecp.instructor.pqr.show');
    Route::patch('/control-ecp/instructor/pqr/{id}/estado', [InstructorPqrsController::class, 'cambiarEstado'])
        ->name('controlecp.instructor.pqr.estado');
    Route::post('/control-ecp/instructor/pqr/{id}/responder', [InstructorPqrsController::class, 'responder'])
        ->name('controlecp.instructor.pqr.responder');

    // --- Panel Aprendiz ---
    Route::get('/control-ecp/aprendiz', [ControlECPController::class, 'inicioAprendiz'])
        ->name('controlecp.aprendiz.inicio');
    Route::get('/control-ecp/aprendiz/cronograma', [ControlECPController::class, 'cronogramaAprendiz'])
        ->name('controlecp.aprendiz.cronograma');
    Route::get('/control-ecp/aprendiz/historia', [ControlECPController::class, 'historiaAprendiz'])
        ->name('controlecp.aprendiz.historia');
    Route::get('/control-ecp/aprendiz/mi-certificado', [AprendizCertificadoController::class, 'index'])
        ->name('controlecp.aprendiz.certificado');
    Route::get('/control-ecp/aprendiz/mi-certificado/{fichaId}/descargar', [AprendizCertificadoController::class, 'descargar'])
        ->whereNumber('fichaId')
        ->name('controlecp.aprendiz.certificado.descargar');
    Route::get('/control-ecp/aprendiz/encuesta', [AprendizEncuestaController::class, 'index'])
        ->name('controlecp.aprendiz.encuesta');
    Route::get('/control-ecp/aprendiz/encuesta/compartida/{encuestaId}', [AprendizEncuestaController::class, 'showCompartida'])
        ->name('controlecp.aprendiz.encuesta.compartida');
    Route::get('/control-ecp/aprendiz/encuesta/{cronogramaId}', [AprendizEncuestaController::class, 'show'])
        ->name('controlecp.aprendiz.encuesta.responder');
    Route::post('/control-ecp/aprendiz/encuesta/{cronogramaId}', [AprendizEncuestaController::class, 'store'])
        ->name('controlecp.aprendiz.encuesta.store');
    Route::get('/control-ecp/aprendiz/asesorias', [InstructorAsesoriaController::class, 'indexAprendiz'])
        ->name('controlecp.aprendiz.asesorias');
    Route::get('/control-ecp/aprendiz/asesorias/instructores-disponibles', [InstructorAsesoriaController::class, 'instructoresDisponibles'])
        ->name('controlecp.aprendiz.asesorias.disponibles');
    Route::get('/control-ecp/aprendiz/asesorias/responsables', [InstructorAsesoriaController::class, 'responsablesPorCategoria'])
        ->name('controlecp.aprendiz.asesorias.responsables');
    Route::get('/control-ecp/aprendiz/asesorias/calendario', [InstructorAsesoriaController::class, 'calendarioResponsable'])
        ->name('controlecp.aprendiz.asesorias.calendario');
    Route::post('/control-ecp/aprendiz/asesorias', [InstructorAsesoriaController::class, 'storeSolicitudAprendiz'])
        ->name('controlecp.aprendiz.asesorias.store');
    Route::put('/control-ecp/aprendiz/asesorias/{id}/cancelar', [InstructorAsesoriaController::class, 'cancelarSolicitudAprendiz'])
        ->whereNumber('id')
        ->name('controlecp.aprendiz.asesorias.cancelar');
    Route::get('/control-ecp/aprendiz/notificaciones', [NotificacionController::class, 'index'])
        ->name('controlecp.aprendiz.notificaciones');
    Route::get('/control-ecp/aprendiz/calificacion-actividad', [AprendizCalificacionController::class, 'index'])
        ->name('controlecp.aprendiz.calificacion_actividad');
    Route::post('/control-ecp/aprendiz/calificacion-actividad/{cronogramaId}', [AprendizCalificacionController::class, 'store'])
        ->whereNumber('cronogramaId')
        ->name('controlecp.aprendiz.calificacion_actividad.store');
    Route::get('/control-ecp/aprendiz/calificacion-asesoria', [AprendizCalificacionAsesoriaController::class, 'index'])
        ->name('controlecp.aprendiz.calificacion_asesoria');
    Route::post('/control-ecp/aprendiz/calificacion-asesoria/{asesoriaId}', [AprendizCalificacionAsesoriaController::class, 'store'])
        ->whereNumber('asesoriaId')
        ->name('controlecp.aprendiz.calificacion_asesoria.store');
    Route::get('/control-ecp/aprendiz/pqr', [ControlECPController::class, 'pqrAprendiz'])
        ->name('controlecp.aprendiz.pqr');
    Route::post('/control-ecp/aprendiz/pqr', [AprendizPqrsController::class, 'store'])
        ->name('controlecp.aprendiz.pqr.store');

 });