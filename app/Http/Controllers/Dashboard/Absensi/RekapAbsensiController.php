<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Models\Absensi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapAbsensiExport;

class RekapAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with(['karyawan','lokasiAbsensi','jamKerja'])
            ->orderBy('tanggal', 'desc');

        // 🔐 user biasa hanya lihat data sendiri
        if (!Auth::user()->hasAnyRole(['admin','superadmin'])) {
            $query->where('karyawan_id', Auth::user()->karyawan->id);
        }

        // 🔍 filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        $rekapAbsensi = $query->paginate(20);

        return view('dashboard.absensis.rekap.index', compact('rekapAbsensi'));
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getData($request);

        $pdf = PDF::loadView('dashboard.absensis.rekap.pdf', [
            'rekapAbsensi' => $data
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-absensi.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new RekapAbsensiExport($request),
            'rekap-absensi.xlsx'
        );
    }

    private function getData($request)
    {
        $query = Absensi::with(['karyawan','lokasiAbsensi','jamKerja'])
            ->orderBy('tanggal','desc');

        if (!Auth::user()->hasAnyRole(['admin','superadmin'])) {
            $query->where('karyawan_id', Auth::user()->karyawan->id);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        return $query->get();
    }
}
