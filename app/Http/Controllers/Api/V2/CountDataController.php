<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Prestasi;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountDataController extends Controller
{
    public function count_siswa()
    {
        $guru = Guru::count();
        $fasilitas = Fasilitas::count();
        $prestasis_siswa = Prestasi::where('status', '1')->count();
        $prestasis_sekolah = Prestasi::where('status', '2')->count();

        $siswas = Siswa::whereHas('kelas', function ($q) {
            $q->where('name', '!=', 'Lulus');
        })->count();

        if($guru && $fasilitas && $prestasis_siswa && $prestasis_sekolah) {
            return $this->success([
                'siswa' => $siswas,
                'guru' => $guru,
                'fasilitas' => $fasilitas,
                'prestasis_siswa' => $prestasis_siswa,
                'prestasis_sekolah' => $prestasis_sekolah,
            ]);
        } else {
            return $this->error([
                'message' => 'Data Tidak Ditemukan',
            ]);
        }

    }
}
