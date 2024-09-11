<?php

namespace App\Http\Controllers\Api;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MidtransApi extends Controller
{
    public function charge(Request $request)
    {
        // make change and after make store to database
        // charge from table siswa refrence by id and pembayaran_id
        // give fee to user 5000 for admin 
        $request->validate([
            'pembayaran_id' => 'required',
        ]);

        $fee = 5000;
        $siswas = Siswa::all();
        try {
            foreach ($siswas as $siswa) {
                $pembayaran = Pembayaran::create([
                    'siswa_id' => $siswa->id,
                    'pembayaran_id' => $request->pembayaran_id,
                    ''
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e
            ], 500);
        }
    }
}
