<?php

use App\Http\Controllers\BerandaController;
use App\Http\Controllers\Dashboard\BeritaController as DashboardBeritaController;
// use App\Http\Controllers\BeritaController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\FasilitasController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\VisiMisiController;
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
Route::get('berita', [BeritaController::class, 'index'])->name('berita.index');
Route::get('berita/{slug}', [BeritaController::class, 'show'])->name('berita.show');

// Route::get('visi-misi', VisiMisiController::class)->name('visi_misi');

Route::group(['prefix' => 'dashboard'], function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('berita', DashboardBeritaController::class, ['names' => 'dashboard.berita']);
    Route::resource('fasilitas', FasilitasController::class, ['names' => 'dashboard.fasilitas']);

    Route::group(['prefix' => 'pengaturan'], function () {
        Route::resource('user', UserController::class, ['names' => 'dashboard.pengaturan.user']);
        Route::resource('role', RoleController::class, ['names' => 'dashboard.pengaturan.role']);
    });
});
