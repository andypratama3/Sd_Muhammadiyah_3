<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Siswa;
use App\Models\Prestasi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DataController extends Controller
{
    public function siswa(Request $request)
    {
        $siswa = Siswa::with('kelas')->get();

        if(empty($siswa)) {
            return response()->json(['error', 'Data Siswa Tidak Ditemukan'], 404);
        }

        return response()->json(['data' => $siswa, 'success', 'Data Siswa Sukses Di Ambil'], 200);
    }

    public function prestasi(Request $request)
    {
        $prestasi = Prestasi::all();
    }
}
