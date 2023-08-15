<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DetailBeritaController;
use App\Http\Controllers\Dashboard\GuruController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\TaskController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\KaryawanController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\FasilitasController;
use App\Http\Controllers\Dashboard\BeritaController as DashboardBeritaController;
use App\Http\Controllers\Dashboard\ArtikelController as DashboardArtikelController;

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


//artikel
Route::get('artikel', [ArtikelController::class, 'index'])->name('artikel');

//login with google
Route::get('auth/google', [GoogleController::class, 'signGoogle'])->name('login.google');
Route::get('auth/google/callback', [GoogleController::class, 'callbackToGoogle'])->name('google.callback');

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('berita', DashboardBeritaController::class, ['names' => 'dashboard.berita']);
    Route::resource('fasilitas', FasilitasController::class, ['names' => 'dashboard.fasilitas']);
    Route::resource('guru', GuruController::class, ['names' => 'dashboard.guru']);
    Route::resource('artikel', ArtikelController::class, ['names' => 'dashboard.artikel']);

    Route::group(['prefix' => 'pengaturan'], function () {
        Route::resource('task', TaskController::class, ['names' => 'dashboard.pengaturan.task']);
        Route::resource('role', RoleController::class, ['names' => 'dashboard.pengaturan.role']);
        Route::resource('user', UserController::class, ['names' => 'dashboard.pengaturan.user']);
        Route::resource('karyawan', KaryawanController::class, ['names' => 'dashboard.pengaturan.karyawan']);
    });

});
