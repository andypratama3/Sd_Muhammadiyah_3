<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Models\LokasiAbsensi;
use App\Services\AbsensiService;
use App\Http\Controllers\Controller;

class AbsensiController extends Controller
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    /**
     * Tampilkan form absensi
     */
    public function index()
    {
        $lokasi = LokasiAbsensi::where('status', 'aktif')->get();
        return view('dashboard.absensis.index', compact('lokasi'));
    }

    /**
     * Proses absensi masuk
     */
    public function absenMasuk(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'lokasi_id' => 'nullable|exists:lokasi_absensi,id'
        ]);
        // ip addreess
        

        $result = $this->absensiService->absenMasuk(
            $request->nip,
            $request->latitude,
            $request->longitude,
            $request->lokasi_id ?? 1

        );

        return response()->json($result);
    }

    /**
     * Proses absensi pulang
     */
    public function absenPulang(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'lokasi_id' => 'nullable|exists:lokasi_absensi,id'
        ]);

        $result = $this->absensiService->absenPulang(
            $request->nip,
            $request->latitude,
            $request->longitude,
            $request->lokasi_id ?? 1
        );

        return response()->json($result);
    }

    /**
     * Tampilkan riwayat absensi
     */
    public function riwayat(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020'
        ]);

        $result = $this->absensiService->getRiwayatAbsensi(
            $request->nip,
            $request->bulan,
            $request->tahun
        );

        return response()->json($result);
    }
}
