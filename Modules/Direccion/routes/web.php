<?php

use Illuminate\Support\Facades\Route;
use Modules\Direccion\Http\Controllers\DireccionController;
use Modules\Direccion\Http\Controllers\PoliticaController;

Route::prefix('direccion')->name('direccion.')->group(function () {
    // Página Principal / Welcome Institucional de Dirección (Pública)
    Route::get('/', [DireccionController::class, 'welcome'])->name('welcome');
    Route::get('/index', [DireccionController::class, 'welcome'])->name('index');

    // Dashboard / Tablero de Control de Gestión Estratégica
    Route::get('/dashboard', [DireccionController::class, 'dashboard'])->name('dashboard');

    // Gestión de Políticas y Directrices (CRUD)
    Route::prefix('politicas')->name('politicas.')->group(function () {
        Route::get('/', [PoliticaController::class, 'index'])->name('index');
        Route::get('/create', [PoliticaController::class, 'create'])->name('create');
        Route::post('/', [PoliticaController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PoliticaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PoliticaController::class, 'update'])->name('update');
        Route::delete('/{id}', [PoliticaController::class, 'destroy'])->name('destroy');
    });

    // Alias legacy de compatibilidad
    Route::get('/create', [PoliticaController::class, 'create'])->name('create');
    Route::post('/store', [PoliticaController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PoliticaController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PoliticaController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PoliticaController::class, 'destroy'])->name('destroy');
});
