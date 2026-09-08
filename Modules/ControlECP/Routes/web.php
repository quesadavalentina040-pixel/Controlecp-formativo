<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlECP\Http\Controllers\ControlECPController;
use Modules\ControlECP\Http\Controllers\ElementoController;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::resource('controlecps',ControlECPController::class)->names('controlecp');

    // Listar elementos
    Route::get('/control-ecp', [ElementoController::class, 'index'])
        ->name('index');

    // Formulario para crear
    Route::get('/elementos/create', [ElementoController::class, 'create'])
        ->name('create');

    // Guardar elemento
    Route::post('/elementos', [ElementoController::class, 'store'])
        ->name('store');

    // Formulario para editar
    Route::get('/elementos/{id}/edit', [ElementoController::class, 'edit'])
        ->name('edit');

    // Actualizar elemento
    Route::put('/elementos/{id}', [ElementoController::class, 'update'])
        ->name('update');

    // Eliminar elemento
    Route::delete('/elementos/{id}', [ElementoController::class, 'destroy'])
        ->name('destroy');
});