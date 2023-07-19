<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BerandaController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\BeritaController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\FasilitasController;

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
Route::get('/berita/{slug}',[BerandaController::class,'detail'])->name('berita.detail');
Route::get('/visi&misi',[BerandaController::class,'visi_misi']);


Route::group(['prefix' => 'dashboard'], function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('berita', BeritaController::class, ['names' => 'dashboard.berita']);
    Route::resource('user', UserController::class, ['names' => 'dashboard.user']);
    Route::resource('role', RoleController::class, ['names' => 'dashboard.role']);
    Route::resource('fasilitas', FasilitasController::class, ['names' => 'dashboard.fasilitas']);
});

