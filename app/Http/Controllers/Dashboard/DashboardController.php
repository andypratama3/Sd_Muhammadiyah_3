<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Artikel;
use App\Models\Prestasi;
use App\Charts\SiswaChart;
use App\Models\Pembayaran;
use App\Charts\ArtikelView;
use App\Models\TenagaPendidikan;
use App\Http\Controllers\Controller;
use App\Models\KritikSaran;

class DashboardController extends Controller
{
    public function __construct()
    {
        // $this->middleware('role:admin,superadmin');
    }

    public function __invoke(ArtikelView $ArtikelChart, SiswaChart $siswaChart)
    {
        $siswa = Siswa::count();
        $guru = Guru::count();
        $prestasi = Prestasi::count();
        $tenagakependidikan = TenagaPendidikan::count();
        //chart
        $ArtikelChart = $ArtikelChart->build();
        $siswaChart = $siswaChart->build();

        //count artikel data
        $artikel_sum_total_klik = Artikel::sum('jumlah_klik');
        $artikel_like_max = Artikel::orderBy('jumlah_klik','desc')->first();

        $artikels = Artikel::orderBy('jumlah_klik','desc')->take(5)->get();

        // convert to percent
        // $percent_artikel = ($artikel_like_max->jumlah_klik  / $artikel_sum_total_klik) * 100;

        $invoice_list = Pembayaran::orderBy('updated_at')->take(10)->get();

        $kritis = KritikSaran::orderBy('created_at', 'desc')->take(5)->get();


        return view('dashboard.index', compact(
            'siswa',
            'guru',
            'prestasi',
            'tenagakependidikan',
            'ArtikelChart',
            'siswaChart',
            'artikels',
            'artikel_sum_total_klik',
            // 'percent_artikel',
            'invoice_list',
            'kritis',
        ));
    }

}
