<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Hero;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Berita;
use App\Models\Gallery;
use App\Models\Visitor;
use App\Models\Prestasi;
use App\Models\Fasilitas;
use App\Models\Esktrakurikuler;

class BerandaController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {

        $visitor = new Visitor();
        $visitor->save();


        $heroes = Hero::select(['name', 'desc', 'image', 'youtube', 'link', 'slug'])->latest()->take(3)->get();
        $beritas = Berita::select(['judul', 'desc', 'foto', 'slug'])->latest()->take(5)->get();
        $siswas = Siswa::whereHas('kelas', function ($q) {
            $q->where('name', '!=', 'Lulus');
        })->count();
        
        $guru = Guru::count();
        $fasilitas = Fasilitas::count();
        $prestasis_siswa = Prestasi::where('status', '1')->count();
        $prestasis_sekolah = Prestasi::where('status', '2')->count();

        $kelas_name = Kelas::orderBy('name');
        $esktrakurikuler = Esktrakurikuler::count();

        $prestasi_terakhir = Prestasi::Where('status', '1')->orderBy('created_at', 'desc')->take(4)->get();
        $gallerys = Gallery::select('name', 'foto', 'slug')->take(8)->get();
        //loop prestasi sekolah dan prestasi siswa
        return view('beranda', compact(
            'beritas',
            'siswas',
            'guru',
            'esktrakurikuler',
            'fasilitas',
            'kelas_name',
            'prestasis_siswa',
            'prestasis_sekolah',
            'heroes',
            'gallerys',
            'prestasi_terakhir'
        ));
    }
}
