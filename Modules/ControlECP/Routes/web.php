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

// --- 2. RUTAS PROTEGIDAS (Gestión interna / CRUD de Elementos) ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Resource del controlador principal (sin 'index' para evitar colisión con la ruta pública)
    Route::resource('controlecps', ControlECPController::class)
        ->except(['index'])
        ->names([
            'create'  => 'controlecp.resource.create',
            'store'   => 'controlecp.resource.store',
            'show'    => 'controlecp.resource.show',
            'edit'    => 'controlecp.resource.edit',
            'update'  => 'controlecp.resource.update',
            'destroy' => 'controlecp.resource.destroy',
        ]);

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
});