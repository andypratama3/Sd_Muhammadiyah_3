<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IpaymuPaymentApi;
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\DataController;
use App\Http\Controllers\Api\Dashboard\SiswaApi;
use App\Http\Controllers\Api\Dashboard\WilayahApi;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// //get data from api wilayah github
// Route::get('provinsi',[WilayahApi::class, 'provinsi'])->name('provinsi.api');
// Route::post('kabupaten',[WilayahApi::class, 'kabupaten'])->name('kabupaten.api');
// Route::post('kecamatan',[WilayahApi::class, 'kecamatan'])->name('kecamatan.api');
// Route::post('kelurahan',[WilayahApi::class, 'kelurahan'])->name('kelurahan.api');
// Route::post('get/provinsi',[WilayahApi::class, 'getProvinsi'])->name('getprovinsi.api');
// Route::post('get/kabupaten',[WilayahApi::class, 'getKabupaten'])->name('getkabupaten.api');
//siswa Data Json
// Route::get('siswas',[SiswaApi::class, 'siswa'])->name('siswa.api');
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
    Route::post('/webhook/whatsapp', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'handle']);
    Route::get('/webhook/whatsapp', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'verify']);
    // Route::post('/webhook/test', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'test']);
    // Route::get('/webhook/template', [App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'getTemplate']);

});

// Whatsaap Callback
Route::post('/whatsapp/callback', [SendOrderIDWhatsAppApi::class, 'webhook'])->name('whatsapp.webhook');


Route::prefix('v2')->group(function () {
    // Login → hanya butuh signature
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('verify.signature');

    // Semua route lain → butuh auth + signature
    Route::middleware(['auth:api'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::get('profile', [DataController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('siswa', [DataController::class, 'siswa']);

        Route::prefix('payment')->group(function () {
            Route::post('list', [DataController::class, 'list_payment']);
        });
    });
});
