<?php

use App\Models\JudulPembayaran;
//User Access
use App\Models\TenagaPendidikan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SpmbController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\VisiMisiController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\NaikKelasController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SiswaAktifController;
use App\Http\Controllers\SiswaLulusController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\KritikSaranController;
use App\Http\Controllers\LikeArtikelController;
use App\Http\Controllers\DetailBeritaController;
use App\Http\Controllers\PrestasiSiswaController;

//Dashboard Access
use App\Http\Controllers\ProfilSekolahController;
use App\Http\Controllers\Api\Dashboard\WilayahApi;
use App\Http\Controllers\CommentArtikelController;
use App\Http\Controllers\Dashboard\RoleController;

use App\Http\Controllers\Dashboard\TaskController;
use App\Http\Controllers\Dashboard\UserController;

use App\Http\Controllers\Dashboard\CategoryArtikel;
use App\Http\Controllers\Dashboard\ChartController;
use App\Http\Controllers\Dashboard\KelasController;
use App\Http\Controllers\EkstrakurikulerController;
use App\Http\Controllers\PrestasiSekolahController;

use App\Http\Controllers\TenagaPendidikanController;
use App\Http\Controllers\PengisianOrangTuaController;
use App\Http\Controllers\Dashboard\ActivityController;
use App\Http\Controllers\Dashboard\KaryawanController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\AchivementController;
use App\Http\Controllers\Dashboard\UrlVisitorController;
use App\Http\Controllers\Dashboard\AttendancesController;
use App\Http\Controllers\Dashboard\CooperationController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\KelasCategoryController;
use App\Http\Controllers\Dashboard\SpmbController as DashboardSpmController;
use App\Http\Controllers\Dashboard\GuruController as DashboardGuruController;
use App\Http\Controllers\Dashboard\HeroController as DashboardHeroController;
use App\Http\Controllers\Dashboard\SiswaController as DashboardSiswaController;
use App\Http\Controllers\Dashboard\BeritaController as DashboardBeritaController;
use App\Http\Controllers\Dashboard\ChargeController as DashboardChargeController;
use App\Http\Controllers\Dashboard\JadwalController as DashboardJadwalController;
use App\Http\Controllers\Dashboard\ArtikelController as DashboardArtikelController;
use App\Http\Controllers\Dashboard\ProfileController as DashboardProfileController;
use App\Http\Controllers\Dashboard\PrestasiController as DashboardPrestasiController;
use App\Http\Controllers\Dashboard\FasilitasController as DashboardFasilitasController;
use App\Http\Controllers\Dashboard\NilaiSiswaController as DashboardNilaiSiswaController;
use App\Http\Controllers\Dashboard\PembayaranController as DashboardPembayaranController;
use App\Http\Controllers\Dashboard\KritikSaranController as DashboardKritiSaranController;
use App\Http\Controllers\Dashboard\MataPelajaranController as DashboardMataPelajaranController;
use App\Http\Controllers\Dashboard\EkstrakulikulerController as DashboardEsktrakurikulerController;
use App\Http\Controllers\Dashboard\GalleryActivityController as DashboardGalleryActivityController;
use App\Http\Controllers\Dashboard\JudulPembayaranController as DashboardJudulPembayaranController;
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
// Route::get('/loading-data', function () {
//     return view('layouts.user.loading-data');
// });

Route::get('/asset/{any}', function () {
    abort(404);
})->where('any', '.*');


Route::group(['prefix' => '/',], function () {

    Route::get('/', BerandaController::class)->name('index');

    Route::post('/rating',[RatingController::class, 'store'])->name('rating.store');

    Route::get('/pengisian-formulir/whatsaap', [PengisianOrangTuaController::class, 'index'])->name('pengisian.index');
    Route::get('/pengisian-formulir/whatsaap/{nisn}', [PengisianOrangTuaController::class, 'show'])->name('pengisian.show');
    Route::post('/pengisian-formulir/whatsaap/verifikasi', [PengisianOrangTuaController::class, 'verifikasi'])->name('pengisian.verifikasi');
    Route::PUT('/pengisian-formulir/whatsaap/update/{nisn}', [PengisianOrangTuaController::class, 'update'])->name('pengisian.update');
    // Tentang Sekolah
    Route::get('profil-sekolah', [ProfilSekolahController::class , 'index'])->name('profil.index');


    // Berita
    Route::get('berita', [BeritaController::class, 'index'])->name('berita.index');
    Route::get('berita/{slug}', [BeritaController::class, 'show'])->name('berita.show');

    // Siswa Lulus & Siswa Aktif
    // Route::get('siswa-aktif', [SiswaAktifController::class, 'index'])->name('siswa-aktif.index');
    // Route::get('siswa-lulus', [SiswaLulusController::class, 'index'])->name('siswa-lulus.index');
    //guru
    Route::get('guru', [GuruController::class, 'index'])->name('guru.index');
    //visi misi
    // Route::get('visimisi', VisiMisiController::class)->name('visimisi.index');
    //ekstrakurikuler
    Route::get('ekstrakurikuler', [EkstrakurikulerController::class, 'index'])->name('esktrakurikuler.index');
    // Route::get('ekstrakurikuler/{name}', [EkstrakurikulerController::class, 'show'])->name('esktrakurikuler.show');
    // pembayaran
    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('pembayaran/pay', [PembayaranController::class, 'searchOrder'])->name('pembayaran.searchOrder');
    Route::get('pembayaran/detail', [PembayaranController::class, 'searchOrderDetail'])->name('pembayaran.searchOrderDetail');
    Route::get('pembayaran/snap/{order_id}', [PembayaranController::class, 'snap_url'])->name('snap_url');
    Route::get('pembayaran/downloadQr/{order_id}', [PembayaranController::class, 'downloadQr'])->name('pembayaran.downloadQr');
    Route::get('pembayaran/cara-melakukan-pembayaran', [PembayaranController::class,'howToPay'])->name('howToPay');
    //fasilitas
    Route::get('fasilitas', [FasilitasController::class, 'index'])->name('fasilitas.index');
    Route::get('fasilitas/{nama_fasilitas}', [FasilitasController::class, 'show'])->name('fasilitas.show');
    //tenaga pendidikan
    Route::get('tenaga-pendidikan', [TenagaPendidikanController::class, 'index'])->name('tenagapendidikan.index');

    Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('gallery/{slug}', [GalleryController::class, 'show'])->name('gallery.show');

    //jadwal
    Route::resource('jadwal', JadwalController::class, ['names' => 'jadwal']);
    Route::post('jadwal/getjadwal/smester', [JadwalController::class, 'tahun_ajaran'])->name('jadwal.tahun.ajaran');
    //artikel
    // Route::resource('artikel', ArtikelController::class, ['names' => 'artikel']);
    //new fiture kontak and prestasi
    Route::get('kontak', [KontakController::class, 'index'])->name('kontak.index');
    Route::get('kontak/success', [KontakController::class, 'success'])->name('kontak.success');

    //kritik saran
    Route::post('kritik-saran/post', [KritikSaranController::class, 'store'])->name('kritik.saran.store');
    Route::get('prestasi-siswa', [PrestasiSiswaController::class, 'index'])->name('prestasi.siswa.index');
    Route::get('prestasi-siswa/{slug}', [PrestasiSiswaController::class, 'show'])->name('prestasi.siswa.show');
    //prestasi sekolah
    Route::get('prestasi-sekolah', [PrestasiSekolahController::class, 'index'])->name('prestasi.sekolah.index');
    Route::get('prestasi-sekolah/{slug}', [PrestasiSekolahController::class, 'show'])->name('prestasi.sekolah.show');

    // PPDB
    Route::get('spmb', [SpmbController::class, 'index'])->name('spmb.index');

    Route::get('spmb/pay',[SpmbController::class, 'pay'])->name('spmb.pay');
    Route::post('spmb/store',[SpmbController::class, 'store'])->name('spmb.store');
    Route::get('spmb/success/{orderID}',[SpmbController::class, 'success'])->name('spmb.success');
    Route::get('spmb/detail/{orderID}',[SpmbController::class, 'formDetail'])->name('formDetail');
    Route::post('spmb/detail/store', [SpmbController::class ,'formDetailStore'])->name('formDetail.store');
    //login with google
    Route::get('auth/google', [GoogleController::class, 'signGoogle'])->name('login.google');
    Route::get('auth/google/callback', [GoogleController::class, 'callbackToGoogle'])->name('google.callback');

});


//users after login
Route::group(['prefix' => 'artikel', 'middleware'], function () {
    //CommentArtikel
    Route::resource('comment', CommentArtikelController::class, ['names' => 'comment']);
    Route::post('like', [LikeArtikelController::class, 'like'])->name('like.comment');

});

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/visitors/data', [VisitorController::class, 'getVisitorData'])->name('visitors.data');

    Route::resource('notifikasi', NotificationController::class, ['names' => 'dashboard.notifikasi']);
    Route::get('notifikasis/data', [NotificationController::class, 'data_table'])->name('dashboard.notifikasi.data');

    Route::resource('kritik-saran', DashboardKritiSaranController::class, ['names' => 'dashboard.kritik.saran']);

    Route::group(['prefix' => 'news'], function () {
        Route::resource('berita', DashboardBeritaController::class, ['names' => 'dashboard.news.berita']);
        Route::post('berita/upload/image/', [DashboardBeritaController::class, 'uploadImage'])->name('dashboard.news.berita.uploadImage');
        Route::resource('hero', DashboardHeroController::class, ['names' => 'dashboard.news.hero']);

        Route::get('beritas/records', [DashboardBeritaController::class, 'data_table'])->name('dashboard.news.berita.getBerita');
        Route::resource('artikel', DashboardArtikelController::class, ['names' => 'dashboard.news.artikel']);
        Route::resource('category', CategoryArtikel::class, ['names' => 'dashboard.news.category']);
        Route::get('artikels/records', [DashboardArtikelController::class, 'data_table'])->name('dashboard.news.artikel.getArtikel');
        Route::get('artikels/status/{slug}', [DashboardArtikelController::class, 'status'])->name('dashboard.news.artikel.status');

    });

    Route::resource('absensi', AttendancesController::class, ['names'=> 'dashboard.attendances']);

    Route::resource('spmb', DashboardSpmController::class, ['names' => 'dashboard.spmb']);
    Route::get('spmbs/datas', [DashboardSpmController::class, 'data_table'])->name('dashboard.spmb.data_table');

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

        Route::resource('gallery', DashboardGalleryActivityController::class, ['names' => 'dashboard.datasekolah.gallery']);
        Route::get('gallerys/data', [DashboardGalleryActivityController::class,'data_table'])->name('dashboard.datasekolah.gallery.data');

        Route::resource('cooperation', CooperationController::class, ['names' => 'dashboard.datasekolah.cooperation']);
        Route::get('cooperations/data', [CooperationController::class,'data_table'])->name('dashboard.datasekolah.cooperation.data');
        //
        Route::resource('achivement', AchivementController::class, ['names' => 'dashboard.datasekolah.achivement']);
        Route::get('achivements/data', [AchivementController::class,'data_table'])->name('dashboard.datasekolah.achivement.data');
    });

    Route::get('pengunjung-halaman', [UrlVisitorController::class, 'index'])->name('dashboard.url.visitor.index');

    Route::group(['prefix' => 'datamaster'], function () {
        Route::resource('siswa',  DashboardSiswaController::class, ['names' => 'dashboard.datamaster.siswa']);
        Route::resource('kategori-pembayaran',  DashboardJudulPembayaranController::class, ['names' => 'dashboard.datamaster.judul.pembayaran']);
        Route::get('siswas/export-excel', [DashboardSiswaController::class,'export_excel'])->name('siswa.export_excel');
        Route::get('siswas/export-pdf', [DashboardSiswaController::class,'export_pdf'])->name('siswa.export_pdf');
        Route::post('siswas/export-excel-kelas', [DashboardSiswaController::class,'exportExcelKelas'])->name('siswa.export_excel_kelas');
        Route::get('siswa/cetak/{slug}', [DashboardSiswaController::class,'cetak_data'])->name('siswa.cetakData');
        Route::get('siswas/naik/kelas', [NaikKelasController::class,'naik'])->name('siswa.naik.kelas');

        Route::post('siswa/nisn', [DashboardSiswaController::class,'checknisn'])->name('siswa.check.nisn');
        Route::get('siswas/records', [DashboardSiswaController::class,'data_table'])->name('siswa.get.records');
        Route::post('siswas/import', [DashboardSiswaController::class,'import'])->name('siswa.import');
        Route::get('nilai', [DashboardNilaiSiswaController::class, 'index'])->name('dashboard.datamaster.nilai.index');
        Route::get('nilai/{name}', [DashboardNilaiSiswaController::class, 'matapelajaran'])->name('nilai.matapelajaran');
        Route::get('nilai/kelas/{kelas}', [DashboardNilaiSiswaController::class, 'kelas'])->name('nilai.matapelajaran.kelas');

        //Pembayaran Spp or invoice
        Route::resource('invoice',  DashboardPembayaranController::class, ['names' => 'dashboard.datamaster.pembayaran']);
        Route::get('invoices/records', [DashboardPembayaranController::class, 'data_table'])->name('dashboard.datamaster.get.records');
        Route::post('invoices/export', [DashboardPembayaranController::class, 'exportExcel'])->name('dashboard.datamaster.pembayaran.exportExcel');

        // charge payment
        Route::resource('charge',  DashboardChargeController::class, ['names' => 'dashboard.datamaster.charge']);
        Route::get('charges/records', [DashboardChargeController::class, 'data_table'])->name('dashboard.datamaster.charge.get.records');
        Route::post('charges/export', [DashboardChargeController::class, 'exportExcel'])->name('dashboard.datamaster.charge.exportExcel');
        Route::get('charges/export_excel', [DashboardChargeController::class, 'export_excel'])->name('dashboard.datamaster.charge.export_excel');
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
        Route::post('get/email/karyawan',[KaryawanController::class, 'getEmailUser'])->name('dashboard.pengaturan.get.email');
    });


    // Chart Data
    Route::group(['prefix' => 'chart'], function () {
        Route::get('charge-count', [ChartController::class, 'chargeCount'])->name('chart.charge.count');

    });


    // Refrence Data
    Route::get('provinsi',[WilayahApi::class, 'provinsi'])->name('provinsi.api');
    Route::post('kabupaten',[WilayahApi::class, 'kabupaten'])->name('kabupaten.api');
    Route::post('kecamatan',[WilayahApi::class, 'kecamatan'])->name('kecamatan.api');
    Route::post('kelurahan',[WilayahApi::class, 'kelurahan'])->name('kelurahan.api');
    Route::post('get/provinsi',[WilayahApi::class, 'getProvinsi'])->name('getprovinsi.api');
    Route::post('get/kabupaten',[WilayahApi::class, 'getKabupaten'])->name('getkabupaten.api');



});
