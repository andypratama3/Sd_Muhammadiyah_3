<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Artikel;
use App\Models\Visitor;
use App\Models\Prestasi;
use App\Charts\SiswaChart;
use App\Models\Pembayaran;
use App\Charts\ArtikelView;
use App\Charts\ChargeChart;
use App\Models\KritikSaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\TenagaPendidikan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(SiswaChart $siswaChart, ChargeChart $chagreChart, Request $request)
    {
        $siswa = Siswa::count();
        $guru = Guru::count();
        $prestasi = Prestasi::count();
        $tenagakependidikan = TenagaPendidikan::count();
        //chart
        // $ArtikelChart = $ArtikelChart->build();
        $siswaChart = $siswaChart->build();
        // Set year for ChargeChart, default to current year if not provided
        $year = $request->input('year', Carbon::now()->year);
        $chagreChart->setYear($year);
        $chagreChart = $chagreChart->build();
        //count artikel data
        $artikel_sum_total_klik = Artikel::sum('jumlah_klik');
        $artikel_like_max = Artikel::orderBy('jumlah_klik','desc')->first();

        $artikels = Artikel::orderBy('jumlah_klik','desc')->take(5)->get();

        // convert to percent
        // $percent_artikel = ($artikel_like_max->jumlah_klik  / $artikel_sum_total_klik) * 100;

        $invoice_list = Pembayaran::orderBy('updated_at')->take(10)->get();

        $kritis = KritikSaran::orderBy('created_at', 'desc')->take(5)->get();

        $artikel_publish = Artikel::where('user_id', Auth::user()->id)->where('status','publish')->count();

        if ($request->ajax()) {
            return response()->json([
                'chagreChart' => $chagreChart->toJson(),
            ]);
        }

        $visitor_by_day = Visitor::whereDate('created_at', Carbon::now()->format('Y-m-d'))->count();
        $visitor_by_month = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->count();
        $visitor_by_year = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfYear()->format('Y-m-d'))->count();


         // define visitor data
        //  View::composer('layouts.landing.footer', function ($view) {
            // $visitor_by_day = Visitor::whereDate('created_at', Carbon::now()->format('Y-m-d'))->count();
            // $visitor_by_month = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->count();
            // $visitor_by_year = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfYear()->format('Y-m-d'))->count();

        //     $view->with(compact('visitor_by_day', 'visitor_by_month', 'visitor_by_year'));
        // });


        return view('dashboard.index', compact(
            'siswa',
            'guru',
            'prestasi',
            'tenagakependidikan',
            // 'ArtikelChart',
            'siswaChart',
            'chagreChart',
            'artikels',
            'artikel_sum_total_klik',
            // 'percent_artikel',
            'invoice_list',
            'kritis',
            'artikel_publish',
        ));
    }

}
