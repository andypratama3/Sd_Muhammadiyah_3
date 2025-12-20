<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Gallery;
use App\Models\Prestasi;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LandingController extends Controller
{
    public function count()
    {
        $guru = Guru::count();
        $fasilitas = Fasilitas::count();
        $prestasis_siswa = Prestasi::where('status', '1')->count();
        $prestasis_sekolah = Prestasi::where('status', '2')->count();

        $siswas = Siswa::whereHas('kelas', function ($q) {
            $q->where('name', '!=', 'Lulus');
        })->count();

        return $this->success([
            'siswa' => $siswas,
            'guru' => $guru,
            'fasilitas' => $fasilitas,
            'prestasis_siswa' => $prestasis_siswa,
            'prestasis_sekolah' => $prestasis_sekolah,
        ]);
    }

    public function gallery_activity()
    {
        $gallery = Gallery::orderBy('created_at', 'asc')->take(8)->get();

        if($gallery) {
            return $this->success($gallery, 'Berhasil Menerima Data');
        }

        return $this->error('Data tidak ditemukan');

    }
}
