<?php

use Illuminate\Support\Facades\Route;
use Modules\Direccion\Http\Controllers\DireccionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('direccions', DireccionController::class)->names('direccion');
});
