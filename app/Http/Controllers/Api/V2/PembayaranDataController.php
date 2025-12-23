<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Siswa;
use App\Models\Charge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PembayaranDataController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
        ]);

        $siswa = Siswa::where('nisn', $request->nisn)->first();

        if($siswa) {
            $charges = Charge::with('kategori_pembayaran')
                ->where('siswa_id', $siswa->id)
                ->get()
                ->groupBy('category_payment_id');

            return $this->success([
                'siswa' => $siswa,
                'charges' => $charges
            ], "ok");
        }
    }

    public function pay($charge_id)
    {
        $charge = Charge::find($charge_id);


        // do here for bank payment
    }
}
