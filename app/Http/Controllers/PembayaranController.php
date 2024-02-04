<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('profil.pembayaran.index');
    }
    public function getOrder(Request $request)
    {
        $kode_pembayaran = $request->kode_pembayaran;
        $pembayaran = Pembayaran::where('order_id', $kode_pembayaran)->first();

        if($pembayaran){
            return response()->json(['data' => $pembayaran, 200, 'Berhasil Mendapatkan Order ID']);
        }else{
            return response('Tidak Ada Order', 500);
        }

    }
}
