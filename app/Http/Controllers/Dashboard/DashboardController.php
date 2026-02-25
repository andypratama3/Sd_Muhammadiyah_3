<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Rating;
use App\Models\Absensi;
use App\Models\Artikel;
use App\Models\Visitor;
use App\Models\Karyawan;
use App\Models\Prestasi;
use App\Charts\SiswaChart;
use App\Models\Pembayaran;
use App\Charts\ChargeChart;
use App\Models\KritikSaran;
use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use App\Charts\ChargeCountMount;
use App\Models\TenagaPendidikan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(
        SiswaChart $siswaChart,
        ChargeChart $chargeChart,
        ChargeCountMount $chargeCountMount,
        Request $request
    ) {
        $guru = Guru::count();
        $prestasi = Prestasi::count();
        $tenagakependidikan = TenagaPendidikan::count();
        // chart
        // $ArtikelChart = $ArtikelChart->build();
        $siswaChart = $siswaChart->build();

        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        // $chargeCountMount_date = $request->input('chargeCountMount_date', Carbon::now()->format('Y-m'));
        // $chargeCountMount_Category = $request->input('category', null);
        // // Set tanggal ke objek chart
        // $chargeCountMount->setChargeCountMountDate($chargeCountMount_date);
        // $chargeCountMount->setCategory($chargeCountMount_Category);
        // // Bangun chart
        // $chargeCountMount = $chargeCountMount->build();

        $chargeChart->setYear($year);
        $chargeChart->setMonth($month);

        $chargeChart = $chargeChart->build();

        // count artikel data
        $artikel_sum_total_klik = Artikel::sum('jumlah_klik');
        $artikel_like_max = Artikel::orderBy('jumlah_klik', 'desc')->first();

        $artikels = Artikel::orderBy('jumlah_klik', 'desc')->take(5)->get();
        $category_payments = JudulPembayaran::orderBy('name', 'asc')->get();

        // convert to percent
        // $percent_artikel = ($artikel_like_max->jumlah_klik  / $artikel_sum_total_klik) * 100;

        $invoice_list = Pembayaran::orderBy('updated_at')->take(10)->get();

        $kritis = KritikSaran::orderBy('created_at', 'desc')->take(5)->get();

        $artikel_publish = Artikel::where('user_id', Auth::user()->id)->where('status', 'publish')->count();

        if ($request->ajax()) {
            return response()->json([
                'chargeCountMount' => $chargeCountMount->toJson(),
                'chargeChart' => $chargeChart->toJson(),
            ]);
        }

        $visitor_by_day = Visitor::whereDate('created_at', Carbon::now()->format('Y-m-d'))->count();
        $visitor_by_month = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->count();
        $visitor_by_year = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfYear()->format('Y-m-d'))->count();

        $kelasLulus = Kelas::where('name', 'Lulus')->first();


        $siswas = Siswa::whereHas('kelas', function ($q) use ( $kelasLulus ) {
            $q->where('id', '!=', $kelasLulus->id);
        })->count();

        $averageRating = Rating::avg('rating');
        $totalVotes = Rating::count();
        $latestRatings = Rating::latest()->take(10)->get();
        // define visitor data
        //  View::composer('layouts.landing.footer', function ($view) {
        // $visitor_by_day = Visitor::whereDate('created_at', Carbon::now()->format('Y-m-d'))->count();
        // $visitor_by_month = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->count();
        // $visitor_by_year = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfYear()->format('Y-m-d'))->count();

        //     $view->with(compact('visitor_by_day', 'visitor_by_month', 'visitor_by_year'));
        // });
        // dd($chagreChart)

        // Absensi
        $today = Carbon::today();

        // Query dasar absensi hari ini
        $absensiHariIni = Absensi::whereDate('tanggal', $today);

        // 🔐 user biasa hanya data sendiri
        if (!Auth::user()->hasAnyRole(['admin','superadmin'])) {
            $absensiHariIni->where(
                'karyawan_id',
                Auth::user()->karyawan->id
            );
        }

        $totalKaryawan = Karyawan::count();

        $hadirHariIni = (clone $absensiHariIni)
            ->where('status_masuk', 'tepat_waktu')
            ->count();

        $terlambatHariIni = (clone $absensiHariIni)
            ->where('status_masuk', 'terlambat')
            ->count();

        $tidakHadirHariIni = $totalKaryawan - $hadirHariIni;

        $cutiAktif = PengajuanCuti::where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->count();

        // 📈 grafik 7 hari terakhir
        $grafik = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);

            $grafik[] = [
                'tanggal' => $tanggal->format('d M'),
                'hadir' => Absensi::whereDate('tanggal', $tanggal)
                    ->where('status_masuk', ['tepat_waktu','terlambat'])
                    ->count()
            ];
        }


        return view('dashboard.index', compact(
            'siswas',
            'guru',
            'prestasi',
            'tenagakependidikan',
            // 'ArtikelChart',
            'siswaChart',
            'chargeChart',
            'artikels',
            'category_payments',
            'artikel_sum_total_klik',
            // 'percent_artikel',
            'invoice_list',
            'kritis',
            'artikel_publish',
            // 'chargeCountMount',
            'averageRating',
            'totalVotes',
            'latestRatings',
            'totalKaryawan',
            'hadirHariIni',
            'terlambatHariIni',
            'tidakHadirHariIni',
            'cutiAktif',
            'grafik'
        ));
    }
}
