<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Artikel;
use App\Models\Prestasi;
use App\Charts\ArtikelView;
use App\Models\TenagaPendidikan;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function __invoke(ArtikelView $ArtikelChart)
    {
        $siswa = Siswa::count();
        $guru = Guru::count();
        $prestasi = Prestasi::count();
        $tenagakependidikan = TenagaPendidikan::count();
        //count artikel data

        $artikels = Artikel::select(['name','jumlah_klik'])->take(5)->get();
        // $like_artikel = Artikel::where('jumlah_klik');

        return view('dashboard.index', ['ArtikelChart' => $ArtikelChart->build()], compact(
            'siswa',
            'guru',
            'prestasi',
            'tenagakependidikan',
            'artikels',

        ));
    }

}
