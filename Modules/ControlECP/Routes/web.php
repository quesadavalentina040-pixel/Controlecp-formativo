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