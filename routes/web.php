<?php

use App\Models\TenagaPendidikan;

//User Access
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\LikeArtikelController;
use App\Http\Controllers\DetailBeritaController;
use App\Http\Controllers\PrestasiSiswaController;
use App\Http\Controllers\Api\Dashboard\WilayahApi;
use App\Http\Controllers\CommentArtikelController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\TaskController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\CategoryArtikel;
use App\Http\Controllers\Dashboard\KelasController;
use App\Http\Controllers\EkstrakurikulerController;
use App\Http\Controllers\PrestasiSekolahController;
use App\Http\Controllers\TenagaPendidikanController;
use App\Http\Controllers\Dashboard\ActivityController;
//Dashboard Access
use App\Http\Controllers\Dashboard\KaryawanController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\KelasCategoryController;
use App\Http\Controllers\Dashboard\GuruController as DashboardGuruController;
use App\Http\Controllers\Dashboard\SiswaController as DashboardSiswaController;
use App\Http\Controllers\Dashboard\BeritaController as DashboardBeritaController;
use App\Http\Controllers\Dashboard\JadwalController as DashboardJadwalController;
use App\Http\Controllers\Dashboard\ArtikelController as DashboardArtikelController;
use App\Http\Controllers\Dashboard\ProfileController as DashboardProfileController;
use App\Http\Controllers\Dashboard\PrestasiController as DashboardPrestasiController;
use App\Http\Controllers\Dashboard\FasilitasController as DashboardFasilitasController;
use App\Http\Controllers\Dashboard\NilaiSiswaController as DashboardNilaiSiswaController;
use App\Http\Controllers\Dashboard\PembayaranController as DashboardPembayaranController;
use App\Http\Controllers\Dashboard\MataPelajaranController as DashboardMataPelajaranController;
use App\Http\Controllers\Dashboard\EkstrakulikulerController as DashboardEsktrakurikulerController;
use App\Http\Controllers\Dashboard\TenagaPendidikanController as DashboardTenagaPendidikanController;

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
Route::group(['prefix' => '/'], function () {
    Route::get('/', BerandaController::class)->name('index');

    // Berita
    Route::get('berita', [DetailBeritaController::class, 'index'])->name('berita.index');
    Route::get('berita/{slug}', [DetailBeritaController::class, 'show'])->name('berita.show');

    //guru
    Route::get('guru', [GuruController::class, 'index'])->name('guru.index');
    //ekstrakurikuler
    Route::get('ekstrakurikuler', [EkstrakurikulerController::class, 'index'])->name('esktrakurikuler.index');
    Route::get('ekstrakurikuler/{name}', [EkstrakurikulerController::class, 'show'])->name('esktrakurikuler.show');
    // pembayaran
    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('pembayaran/search', [PembayaranController::class, 'getOrder'])->name('pembayaran.search');
    Route::post('pembayaran/pay', [PembayaranController::class, 'pay'])->name('pembayaran.pay');
    //fasilitas
    Route::get('fasilitas', [FasilitasController::class, 'index'])->name('fasilitas.index');
    Route::get('fasilitas/{nama_fasilitas}', [FasilitasController::class, 'show'])->name('fasilitas.show');
    //tenaga pendidikan
    Route::get('tenagapendidikan', [TenagaPendidikanController::class, 'index'])->name('tenagapendidikan.index');
    //jadwal
    Route::resource('jadwal', JadwalController::class, ['names' => 'jadwal']);
    Route::post('jadwal/getjadwal/smester', [JadwalController::class, 'tahun_ajaran'])->name('jadwal.tahun.ajaran');
    //artikel
    Route::resource('artikel', ArtikelController::class, ['names' => 'artikel']);
    //new fiture kontak and prestasi
    Route::get('kontak', [KontakController::class, 'index'])->name('kontal.index');
    Route::get('prestasi-siswa', [PrestasiSiswaController::class, 'index'])->name('prestasi.siswa.index');
    Route::get('prestasi-siswa/{slug}', [PrestasiSiswaController::class, 'show'])->name('prestasi.siswa.show');
    //prestasi sekolah
    Route::get('prestasi-sekolah', [PrestasiSekolahController::class, 'index'])->name('prestasi.sekolah.index');
    Route::get('prestasi-sekolah/{slug}', [PrestasiSekolahController::class, 'show'])->name('prestasi.sekolah.show');

    //login with google
    Route::get('auth/google', [GoogleController::class, 'signGoogle'])->name('login.google');
    Route::get('auth/google/callback', [GoogleController::class, 'callbackToGoogle'])->name('google.callback');

});


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
        Route::get('beritas/records', [DashboardBeritaController::class, 'data_table'])->name('dashboard.news.berita.getBerita');
        Route::resource('artikel', DashboardArtikelController::class, ['names' => 'dashboard.news.artikel']);
        Route::resource('category', CategoryArtikel::class, ['names' => 'dashboard.news.category']);
        Route::get('artikels/records', [DashboardArtikelController::class, 'data_table'])->name('dashboard.news.artikel.getArtikel');
        // Route::post('artikels/destory', [DashboardArtikelController::class, 'destroy'])->name('dashboard.news.artikel.artikelsdestory');

    });

    Route::group(['prefix' => 'datasekolah'], function () {
        Route::resource('fasilitas', DashboardFasilitasController::class, ['names' => 'dashboard.datasekolah.fasilitas']);
        Route::resource('guru', DashboardGuruController::class, ['names' => 'dashboard.datasekolah.guru']);
        Route::resource('tenagapendidikan', DashboardTenagaPendidikanController::class, ['names' => 'dashboard.datasekolah.tenagapendidikan']);
        Route::resource('ekstrakurikuler', DashboardEsktrakurikulerController::class, ['names' => 'dashboard.datasekolah.ekstrakurikuler']);
        Route::resource('matapelajaran', DashboardMataPelajaranController::class, ['names' => 'dashboard.datasekolah.matapelajaran']);
        Route::resource('prestasi', DashboardPrestasiController::class, ['names' => 'dashboard.datasekolah.prestasi']);
        Route::resource('kelas', KelasController::class, ['names' => 'dashboard.datasekolah.kelas']);
        Route::resource('jadwal',  DashboardJadwalController::class, ['names' => 'dashboard.datasekolah.jadwal']);
        Route::post('kelas_category',[ DashboardJadwalController::class, 'getCategoryKelas'])->name('dashboard.datasekolah.jadwal.kelas_category');
        Route::post('getSmester',[ DashboardJadwalController::class, 'getSmester'])->name('dashboard.datasekolah.jadwal.getSmester');
    });
    Route::group(['prefix' => 'datamaster'], function () {
        Route::resource('siswa',  DashboardSiswaController::class, ['names' => 'dashboard.datamaster.siswa']);
        Route::get('siswas/export-excel', [DashboardSiswaController::class,'export_excel'])->name('siswa.export_excel');
        Route::get('siswas/export-pdf', [DashboardSiswaController::class,'export_pdf'])->name('siswa.export_pdf');
        Route::get('siswa/cetak/{slug}', [DashboardSiswaController::class,'cetak_data'])->name('siswa.cetakData');
        Route::post('siswa/nisn', [DashboardSiswaController::class,'checknisn'])->name('siswa.check.nisn');
        Route::get('siswas/records', [DashboardSiswaController::class,'data_table'])->name('siswa.get.records');
        Route::get('nilai', [DashboardNilaiSiswaController::class, 'index'])->name('dashboard.datamaster.nilai.index');
        Route::get('nilai/{name}', [DashboardNilaiSiswaController::class, 'matapelajaran'])->name('nilai.matapelajaran');
        Route::get('nilai/kelas/{kelas}', [DashboardNilaiSiswaController::class, 'kelas'])->name('nilai.matapelajaran.kelas');

        //Pembayaran Spp or invoice
        Route::resource('invoice',  DashboardPembayaranController::class, ['names' => 'dashboard.datamaster.pembayaran']);
        Route::get('invoices/records', [DashboardPembayaranController::class, 'data_table'])->name('dashboard.datamaster.get.records');
        //activity user
        Route::resource('activity',  ActivityController::class, ['names' => 'dashboard.datamaster.activity']);
        Route::get('get/activitys', [ActivityController::class, 'activitys'])->name('dashboard.datamaster.get.activitys');

    });
    Route::group(['prefix' => 'pengaturan'], function () {
        // user settings
        Route::resource('profile', DashboardProfileController::class, ['names' => 'dashboard.pengaturan.profile']);
        Route::post('profiles/crop/image', [DashboardProfileController::class, 'crop_image'])->name('dashboard.pangaturan.profile.crop_image');
        Route::post('profiles/remove/image', [DashboardProfileController::class, 'removeAvatar'])->name('dashboard.pangaturan.profile.removAvatar');

        // akses
        Route::resource('task', TaskController::class, ['names' => 'dashboard.pengaturan.task']);
        Route::resource('role', RoleController::class, ['names' => 'dashboard.pengaturan.role']);
        Route::resource('user', UserController::class, ['names' => 'dashboard.pengaturan.user']);
        Route::resource('karyawan', KaryawanController::class, ['names' => 'dashboard.pengaturan.karyawan']);
    });



});
