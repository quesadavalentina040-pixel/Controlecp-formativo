<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\SICA\Entities\Bloque;

use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    $bloques = collect();
    if (Schema::hasTable('bloques')) {
        $bloques = Bloque::with(['apps' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('order_index')->get();
    }
    return view('welcome', compact('bloques'));
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');
