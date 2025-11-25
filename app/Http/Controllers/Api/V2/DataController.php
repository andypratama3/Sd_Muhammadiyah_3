<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Charge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DataController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }


    public function siswa(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
        ]);

        $siswa = Siswa::where('nisn', $request->nisn)->first();


        return response()->json([
            'siswa' => $siswa
        ]);

        if (!$siswa) {
            return response()->json(['error' => 'Siswa tidak ditemukan'], 404);
        }

        return response()->json(['siswa' => $siswa], 200);
    }

    public function profile(Request $request)
    {
        $user = auth()->user();
        return response()->json([
            'user' => $user
        ]);
    }

    public function list_payment(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
        ]);

        if(! $request->has('nisn')) {
            return response()->json([
                'status' => 'error',
                'message' => 'NISN Tidak Ditemukan',
            ]);
        }

        $siswa = Siswa::where('nisn', $request->nisn)->firstOrFail();

        $charges = Charge::where('siswa_id', $siswa->id)->get();

        if($charges->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak Ada Data Pembayaran',
            ]);
        }

        return response()->json([
            'charges' => $charges
        ]);

    }

    // public function

    public function spmb(Request $request)
    {
        
    }

}
