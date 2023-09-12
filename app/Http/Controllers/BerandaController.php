<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Berita;
use App\Models\Fasilitas;

class BerandaController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $beritas = Berita::select(['judul', 'desc', 'foto', 'slug'])->latest()->get();
        // $siswa = Siswa::count();
        $guru = Guru::count();
        $fasilitas = Fasilitas::count();
        return view('beranda', compact(
            'beritas',
            'guru',
            'fasilitas',
        ));
    }
}
