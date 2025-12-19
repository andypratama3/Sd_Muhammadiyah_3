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

        if(!$siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        return $this->success([
            'siswa' => $siswa,
            'list_payment' => $siswa->charges()->get(),
        ]);
    }
}
