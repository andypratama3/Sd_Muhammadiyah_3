<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Prestasi;
use App\Charts\ArtikelView;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function __invoke(ArtikelView $ArtikelChart)
    {
        $siswa = Siswa::count();
        $guru = Guru::count();
        $prestasi = Prestasi::count();
        return view('dashboard.index', ['ArtikelChart' => $ArtikelChart->build()], compact(
            'siswa',
            'guru',
            'prestasi',
        ));
    }

}
