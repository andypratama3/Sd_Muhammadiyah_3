<?php

use App\Http\Controllers\BerandaController;
use App\Http\Controllers\Dashboard\BeritaController as DashboardBeritaController;
use App\Http\Controllers\Dashboard\DashboardController;
// use App\Http\Controllers\BeritaController;
use App\Http\Controllers\Dashboard\FasilitasController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\TaskController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\DetailBeritaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', BerandaController::class)->name('index');

// Berita
Route::get('berita', [DetailBeritaController::class, 'index'])->name('berita.index');
Route::get('berita/{slug}', [DetailBeritaController::class, 'show'])->name('berita.show');

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('berita', DashboardBeritaController::class, ['names' => 'dashboard.berita']);
    Route::resource('fasilitas', FasilitasController::class, ['names' => 'dashboard.fasilitas']);

    Route::group(['prefix' => 'pengaturan'], function () {
        Route::resource('task', TaskController::class, ['names' => 'dashboard.pengaturan.task']);
        Route::resource('role', RoleController::class, ['names' => 'dashboard.pengaturan.role']);
        Route::resource('user', UserController::class, ['names' => 'dashboard.pengaturan.user']);
    });

});
