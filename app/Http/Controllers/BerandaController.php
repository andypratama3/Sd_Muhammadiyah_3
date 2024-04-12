<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Berita;
use App\Models\Fasilitas;
use App\Models\Esktrakurikuler;

class BerandaController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $beritas = Berita::select(['judul', 'desc', 'foto', 'slug'])->latest()->take(5)->get();
        $siswas = Siswa::count();
        $guru = Guru::count();
        $fasilitas = Fasilitas::count();
        $kelas_name = Kelas::orderBy('name');
        $esktrakurikuler = Esktrakurikuler::count();
        return view('beranda', compact(
            'beritas',
            'siswas',
            'guru',
            'esktrakurikuler',
            'fasilitas',
            'kelas_name',

        ));
    }
}
