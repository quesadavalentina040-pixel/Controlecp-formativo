<?php

use Illuminate\Support\Facades\Route;
use Modules\SENAEMPRESA\Http\Controllers\AsistenciaController;

Route::middleware(['web', 'lang'])->prefix('senaempresa')->name('senaempresa.')->group(function () {

    // Dashboard del módulo
    Route::get('/', function () {
        return view('senaempresa::index', [
            'title' => 'SENA Empresa - Gestión de Asistencias',
        ]);
    })->name('dashboard');

    // -------------- Rutas de Asistencia ---------------------------------
    Route::get('asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
    Route::get('asistencia/create', [AsistenciaController::class, 'create'])->name('asistencia.create');
    Route::post('asistencia', [AsistenciaController::class, 'store'])->name('asistencia.store');
    Route::get('asistencia/{asistencia}', [AsistenciaController::class, 'show'])->name('asistencia.show');
    Route::delete('asistencia/{asistencia}', [AsistenciaController::class, 'destroy'])->name('asistencia.destroy');
    Route::post('asistencia/{asistencia}/close', [AsistenciaController::class, 'close'])->name('asistencia.close');

    // Registro de aprendices en una sesión
    Route::post('asistencia/{asistencia}/apprentices', [AsistenciaController::class, 'registerApprentices'])->name('asistencia.apprentices.register');
    Route::put('asistencia/{asistencia}/apprentices', [AsistenciaController::class, 'updateApprenticeAttendance'])->name('asistencia.apprentices.update');
    Route::delete('asistencia/{asistencia}/apprentices', [AsistenciaController::class, 'removeApprentice'])->name('asistencia.apprentices.remove');

    // -------------- Integración con SICEFA API REST ---------------------
    Route::get('sincronizacion', [AsistenciaController::class, 'syncView'])->name('asistencia.sync');
    Route::post('sincronizacion/apprentices', [AsistenciaController::class, 'syncApprentices'])->name('asistencia.sync.apprentices');
    Route::post('sincronizacion/attendance', [AsistenciaController::class, 'syncAttendance'])->name('asistencia.sync.attendance');
    Route::post('asistencia/{asistencia}/send-sicefa', [AsistenciaController::class, 'sendToSicefa'])->name('asistencia.send-sicefa');
});
