<?php

use Illuminate\Support\Facades\Route;

//User Access
use App\Http\Controllers\GuruController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\JadwalController;

//Dashboard Access
use App\Http\Controllers\LikeArtikelController;
use App\Http\Controllers\DetailBeritaController;
use App\Http\Controllers\CommentArtikelController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\TaskController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\CategoryArtikel;
use App\Http\Controllers\Dashboard\KelasController;
use App\Http\Controllers\Dashboard\KaryawanController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\KelasCategoryController;
use App\Http\Controllers\Dashboard\GuruController as DashboardGuruController;
use App\Http\Controllers\Dashboard\JadwalController as DashboardJadwalController;
use App\Http\Controllers\Dashboard\BeritaController as DashboardBeritaController;
use App\Http\Controllers\Dashboard\ArtikelController as DashboardArtikelController;
use App\Http\Controllers\Dashboard\FasilitasController as DashboardFasilitasController;
use App\Http\Controllers\Dashboard\EkstrakulikulerController as DashboardEsktrakurikulerController;

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
Route::get('jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
//artikel
Route::resource('artikel', ArtikelController::class, ['names' => 'artikel']);
//login with google
Route::get('auth/google', [GoogleController::class, 'signGoogle'])->name('login.google');
Route::get('auth/google/callback', [GoogleController::class, 'callbackToGoogle'])->name('google.callback');

//users after login
Route::group(['prefix' => 'artikel', 'middleware' => ['auth', 'verified']], function () {
    //CommentArtikel
    Route::resource('comment', CommentArtikelController::class, ['names' => 'comment']);
    Route::post('like', [LikeArtikelController::class, 'like'])->name('like.comment');

});

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::group(['prefix' => 'news'], function () {
        Route::resource('berita', DashboardBeritaController::class, ['names' => 'dashboard.news.berita']);
        Route::resource('artikel', DashboardArtikelController::class, ['names' => 'dashboard.news.artikel']);
        Route::resource('category', CategoryArtikel::class, ['names' => 'dashboard.news.category']);
        Route::get('artikels/records', [DashboardArtikelController::class, 'data_table'])->name('dashboard.news.artikel.getArtikel');
        // Route::post('artikels/destory', [DashboardArtikelController::class, 'destroy'])->name('dashboard.news.artikel.artikelsdestory');

    });

    Route::resource('fasilitas', DashboardFasilitasController::class, ['names' => 'dashboard.fasilitas']);
    Route::resource('guru', DashboardGuruController::class, ['names' => 'dashboard.guru']);
    Route::resource('ekstrakurikuler', DashboardEsktrakurikulerController::class, ['names' => 'dashboard.ekstrakurikuler']);

    Route::group(['prefix' => 'datamaster'], function () {
        Route::resource('kelas', KelasController::class, ['names' => 'dashboard.datamaster.kelas']);
        Route::resource('jadwal',  DashboardJadwalController::class, ['names' => 'dashboard.datamaster.jadwal']);
        Route::post('kelas_category',[ DashboardJadwalController::class, 'getCategoryKelas'])->name('dashboard.datamaster.jadwal.kelas_category');
        Route::post('getSmester',[ DashboardJadwalController::class, 'getSmester'])->name('dashboard.datamaster.jadwal.getSmester');
    });

    Route::group(['prefix' => 'pengaturan'], function () {
        Route::resource('task', TaskController::class, ['names' => 'dashboard.pengaturan.task']);
        Route::resource('role', RoleController::class, ['names' => 'dashboard.pengaturan.role']);
        Route::resource('user', UserController::class, ['names' => 'dashboard.pengaturan.user']);
        Route::resource('karyawan', KaryawanController::class, ['names' => 'dashboard.pengaturan.karyawan']);
    });



});
