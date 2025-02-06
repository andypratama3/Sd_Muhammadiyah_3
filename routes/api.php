<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IpaymuPaymentApi;
use App\Http\Controllers\Api\Dashboard\SiswaApi;
use App\Http\Controllers\Api\Dashboard\WilayahApi;
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


Route::post('midtrans/callback', [MidtransPaymentController::class, 'callback']);
Route::post('midtrans/handling/unfinish', [MidtransPaymentController::class, 'callback_unfinish']);

Route::post('midtrans/handling/error', [MidtransPaymentController::class, 'callback_error']);
