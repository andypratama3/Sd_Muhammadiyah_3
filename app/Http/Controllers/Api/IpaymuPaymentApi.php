<?php

namespace App\Http\Controllers\Api;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IpaymuPaymentApi extends Controller
{
    public function callback(Request $request)
    {

        /*
            ! Payment Callback From Ipaymu And Update data at database
        */
        $trx_id = $request->trx_id;
        $sessionID    = $request->id;
        $status    = $request->status;
        $pembayaran = Pembayaran::where('sessionID', $sessionID)->first();

        if($status == 'berhasil'){
            $pembayaran->trx_id = $trx_id;
            $pembayaran->status = 'Berhasil';

        }elseif($status == 'pending'){
            $pembayaran->trx_id = $trx_id;
            $pembayaran->status = 'Pending';
        }else{
            $pembayaran->trx_id = $trx_id;
            $pembayaran->status = 'Expired';
        }
        $pembayaran->update();
    }
}
