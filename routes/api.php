<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\IpaymuPaymentApi;
use App\Http\Controllers\Api\V2\DataController;
use App\Http\Controllers\Api\Dashboard\SiswaApi;
use App\Http\Controllers\Api\Dashboard\WilayahApi;
use App\Http\Controllers\Api\V2\LandingController;
use App\Http\Controllers\Api\V2\ViewsDataController;
use App\Http\Controllers\Api\V2\BeritaDataController;
use App\Http\Controllers\Api\V2\JadwalDataController;
use App\Http\Controllers\Api\V2\FasilitasDataController;
use App\Http\Controllers\Api\V2\PembayaranDataController;
use App\Http\Controllers\Api\V1\MidtransPaymentController;
use App\Http\Controllers\Dashboard\Api\FacebookController;
use App\Http\Controllers\Api\Dashboard\SendOrderIDWhatsAppApi;

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
    Route::post('/whatsapp/test/foonte', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'testMessage']);
    Route::post('/whatsapp/test/foonte/image', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'testImage']);
    Route::get('/whatsapp/template', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'getTemplate']);
    // Route::get('/whatsapp/config', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'debugConfig']);

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


    Route::prefix('auth')->group(function () {
        Route::post('/token', [AuthController::class, 'generateToken']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/revoke', [AuthController::class, 'revoke']);
        Route::post('/validate', [AuthController::class, 'validateToken']);
    });

    Route::group(['middleware' => 'jwt'], function () {


        Route::get('count-landing',[LandingController::class, 'count']);
        Route::get('gallery-landing',[LandingController::class, 'gallery_activity']);
        Route::get('dukungan-kerja-sama', [LandingController::class, 'dukungan']);
        Route::get('/views', [ViewsDataController::class, 'viewData']);
        // Pembayaran
        Route::get('/siswa/search',[PembayaranDataController::class, 'search']);
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

        Route::get('fasilitas', [FasilitasDataController::class, 'fasilitasData']);
        Route::get('gallery', [GalleryDataController::class, 'galleryData']);
    });



});
