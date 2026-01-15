<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\IpaymuPaymentApi;
use App\Http\Controllers\Api\V2\DataController;
use App\Http\Controllers\Api\V2\SPMBController;
use App\Http\Controllers\Api\Dashboard\SiswaApi;
use App\Http\Controllers\Api\Dashboard\WilayahApi;
use App\Http\Controllers\Api\V2\LandingController;
use App\Http\Controllers\Api\V2\GuruDataController;
use App\Http\Controllers\Api\V2\RapotDataController;
use App\Http\Controllers\Api\V2\ViewsDataController;
use App\Http\Controllers\Api\V2\BeritaDataController;
use App\Http\Controllers\Api\V2\JadwalDataController;
use App\Http\Controllers\Api\V2\GalleryDataController;
use App\Http\Controllers\Api\V2\TentangDataController;
use App\Http\Controllers\Api\V2\PrestasiDataController;
use App\Http\Controllers\Api\V2\FasilitasDataController;
use App\Http\Controllers\Api\V2\PembayaranDataController;
use App\Http\Controllers\Api\V1\MidtransPaymentController;
use App\Http\Controllers\Dashboard\Api\FacebookController;
use App\Http\Controllers\Api\Dashboard\SendOrderIDWhatsAppApi;
use App\Http\Controllers\Api\V2\EkstrakurikulerDataController;
use App\Http\Controllers\Api\V2\TenagaKependidikanDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Api Payment
Route::post('ipaymu/callback',[IpaymuPaymentApi::class, 'callback'])->name('ipaymu.api.callback');
Route::get('facebook/data',[FacebookController::class, 'getData'])->name('api.facebook.data');
Route::post('send/message/whatsapp', [SendOrderIDWhatsAppApi::class, 'sendMessage']);
Route::match(['GET', 'POST'], 'midtrans/callback', [MidtransPaymentController::class, 'callback']);
// Route::post('midtrans/callback', [MidtransPaymentController::class, 'callback']);
Route::post('midtrans/handling/unfinish', [MidtransPaymentController::class, 'callback_unfinish']);
Route::post('midtrans/handling/error', [MidtransPaymentController::class, 'callback_error']);

Route::prefix('v1')->group(function () {
    // WhatsApp Webhook
    Route::post('/whatsapp/test', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'test']);
    Route::get('/whatsapp/template', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'getTemplate']);
    Route::get('/whatsapp/config', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'debugConfig']);

    Route::get('/webhook/whatsapp', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'verify']);
    Route::post('/webhook/whatsapp', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'handle']);
});

// Whatsaap Callback
Route::post('/whatsapp/callback', [SendOrderIDWhatsAppApi::class, 'webhook'])->name('whatsapp.webhook');

// Route::prefix('v2')->group(function () {
//     // Login → hanya butuh signature
//     Route::post('login', [AuthController::class, 'login'])
//         ->middleware('verify.signature');

//     // Semua route lain → butuh auth + signature
//     Route::middleware(['auth:api'])->group(function () {
//         Route::get('me', [AuthController::class, 'me']);
//         Route::get('profile', [DataController::class, 'profile']);
//         Route::post('logout', [AuthController::class, 'logout']);
//         Route::post('siswa', [DataController::class, 'siswa']);

//         Route::prefix('payment')->group(function () {
//             Route::post('list', [DataController::class, 'list_payment']);
//         });
//     });
// });


Route::group(['prefix' => 'v2'], function () {

    // For Site Map
    Route::get('list/berita', [BeritaDataController::class, 'list_berita']);
    Route::get('list/gallery', [GalleryDataController::class, 'list_gallery']);
    Route::get('list/prestasi-siswa', [PrestasiDataController::class, 'list_prestasi_siswa']);
    Route::get('list/prestasi-sekolah', [PrestasiDataController::class, 'list_prestasi_sekolah']);
    // End For Site Map

    Route::group(['prefix' => 'auth', 'middleware' => ['verify.signature']], function () {

        Route::post('/token', [AuthController::class, 'generateToken']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/revoke', [AuthController::class, 'revoke']);
        Route::post('/validate', [AuthController::class, 'validateToken']);
    });

    // Fetch Front End
    Route::group(['middleware' => 'jwt'], function () {

        // Route::post('/spmb/store', [SPMBController::class, 'store']);

        Route::post('visitor/store', [ViewsDataController::class, 'store']);
        Route::get('count-landing',[LandingController::class, 'count']);
        Route::get('gallery-landing',[LandingController::class, 'gallery_activity']);
        Route::get('prestasi-landing',[LandingController::class, 'prestasi_landing']);

        Route::get('dukungan-kerja-sama', [LandingController::class, 'dukungan']);
        Route::get('/views', [ViewsDataController::class, 'viewData']);
        Route::get('/tentang/foto-sekolah', [TentangDataController::class, 'fotoSekolah']);

        // Pembayaran
        Route::prefix('pembayaran')->group(function () {
            Route::get('/search', [PembayaranDataController::class, 'search']);
            Route::get('/{siswa_id}/statistics', [PembayaranDataController::class, 'statistics']);
        });

        // Jadwal
        Route::get('jadwal/tahun-ajaran',[JadwalDataController::class, 'tahunAjaran']);
        Route::get('jadwal/kelas',[JadwalDataController::class, 'kelas']);
        Route::get('jadwal/category-kelas',[JadwalDataController::class, 'categoryKelas']);
        Route::get('jadwal/list-jadwal',[JadwalDataController::class, 'list_jadwal']);

        // Data Core
        Route::get('berita-count-data', [BeritaDataController::class, 'countData']);
        Route::get('berita-popular', [BeritaDataController::class, 'beritaPopuler']);
        Route::get('berita', [BeritaDataController::class, 'list']);
        Route::get('berita/{slug}', [BeritaDataController::class, 'show']);

        // Ekstrakurikuler
        Route::get('ekstrakurikuler', [EkstrakurikulerDataController::class, 'list']);

        // Tenaga Kependidikan
        Route::get('tenaga-kependidikan', [TenagaKependidikanDataController::class, 'list']);

        Route::get('fasilitas', [FasilitasDataController::class, 'fasilitasData']);
        Route::get('gallery', [GalleryDataController::class, 'listGallery']);
        Route::get('gallery/{slug}', [GalleryDataController::class, 'show']);
        Route::get('kategori-gallery', [GalleryDataController::class, 'kategori']);

        Route::prefix('guru')->group(function () {
            Route::get('/pelajaran', [GuruDataController::class, 'pelajaran']);
            Route::get('/count-by-pelajaran', [GuruDataController::class, 'countByPelajaran']);
            Route::get('/statistics', [GuruDataController::class, 'statistics']);
            Route::get('/', [GuruDataController::class, 'listGuru']);
            Route::get('/{slug}', [GuruDataController::class, 'show']);
        });



        Route::group(['prefix' => 'prestasi'], function () {
            Route::get('/categories', [PrestasiDataController::class, 'categories']);
            Route::get('/categories/siswa', [PrestasiDataController::class, 'categoriesSiswa']);
            Route::get('/categories/sekolah', [PrestasiDataController::class, 'categoriesSekolah']);


            // Prestasi Siswa endpoints
            Route::prefix('siswa')->group(function () {
                Route::get('/', [PrestasiDataController::class, 'prestasi_siswa']);
                Route::get('/count-by-tingkat', [PrestasiDataController::class, 'countSiswaByTingkat']);
                Route::get('/count-by-category', [PrestasiDataController::class, 'countSiswaByCategory']);
                Route::get('/popular', [PrestasiDataController::class, 'prestasiSiswaPopular']);
                Route::get('/{slug}', [PrestasiDataController::class, 'prestasi_siswa_detail']);
            });

            // Prestasi Sekolah endpoints
            Route::prefix('sekolah')->group(function () {
                Route::get('/', [PrestasiDataController::class, 'prestasi_sekolah']);
                Route::get('/{slug}', [PrestasiDataController::class, 'prestasi_sekolah_detail']);
            });
        });

        Route::group(['prefix' => 'rapot'], function () {
            Route::get('/tahun', [RapotDataController::class, 'getTahunAjaran']);
            Route::get('/siswa', [RapotDataController::class, 'getSiswaByTahun']);
            Route::get('/detail/{siswaId}', [RapotDataController::class, 'getDetailRapotSiswa']);

            // FIXED: Download endpoint dengan proper streaming
            Route::get('/download/{siswaId}/{rapotId}', [RapotDataController::class, 'downloadRapot']);

            // Alternative: Get temporary signed URL
            Route::get('/url/{siswaId}/{rapotId}', [RapotDataController::class, 'getDownloadUrl']);
        });

        // Statistics endpoint
        Route::get('/statistics', [PrestasiDataController::class, 'statistics']);
    });

    // END Fetch

    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

});

