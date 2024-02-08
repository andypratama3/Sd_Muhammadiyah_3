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
        $sessionID    = $request->sid;
        $status    = $request->status;
        $pembayaran = Pembayaran::where('sessionID', $sessionID)->first();

        if($status == 'success'){
            $pembayaran->status = $status;
        }elseif($status == 'pending'){

        }else{

        }
        $pembayaran->update();
        dd($pembayaran);
    }
}
