<?php

use Illuminate\Support\Facades\Route;

//User Access
use App\Http\Controllers\GuruController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\Auth\GoogleController;

//Dashboard Access
use App\Http\Controllers\DetailBeritaController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\TaskController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\CategoryArtikel;
use App\Http\Controllers\Dashboard\KaryawanController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\KelasController;
use App\Http\Controllers\Dashboard\GuruController as DashboardGuruController;
use App\Http\Controllers\Dashboard\BeritaController as DashboardBeritaController;
use App\Http\Controllers\Dashboard\ArtikelController as DashboardArtikelController;
use App\Http\Controllers\Dashboard\FasilitasController as DashboardFasilitasController;

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
//gurur
Route::get('guru', [GuruController::class, 'index'])->name('guru.index');
//fasilitas
Route::get('fasilitas', [FasilitasController::class, 'index'])->name('fasilitas.index');
//artikel
Route::resource('artikel', ArtikelController::class, ['names' => 'artikel']);
//login with google
Route::get('auth/google', [GoogleController::class, 'signGoogle'])->name('login.google');
Route::get('auth/google/callback', [GoogleController::class, 'callbackToGoogle'])->name('google.callback');

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/kelas', [KelasController::class, 'index']);

    Route::resource('fasilitas', DashboardFasilitasController::class, ['names' => 'dashboard.fasilitas']);
    Route::resource('guru', DashboardGuruController::class, ['names' => 'dashboard.guru']);

    Route::group(['prefix' => 'news'], function () {
        Route::resource('berita', DashboardBeritaController::class, ['names' => 'dashboard.news.berita']);
        Route::resource('artikel', DashboardArtikelController::class, ['names' => 'dashboard.news.artikel']);
        Route::resource('category', CategoryArtikel::class, ['names' => 'dashboard.news.category']);
        Route::get('artikel/records', [DashboardArtikelController::class, 'dashboard.news.artikel.getArtikel']);

    });
    Route::group(['prefix' => 'pengaturan'], function () {
        Route::resource('task', TaskController::class, ['names' => 'dashboard.pengaturan.task']);
        Route::resource('role', RoleController::class, ['names' => 'dashboard.pengaturan.role']);
        Route::resource('user', UserController::class, ['names' => 'dashboard.pengaturan.user']);
        Route::resource('karyawan', KaryawanController::class, ['names' => 'dashboard.pengaturan.karyawan']);
    });



});
