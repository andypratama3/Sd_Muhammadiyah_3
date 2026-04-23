<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSholat;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RekapAbsensiSholatController extends Controller
{
    public function index(Request $request)
    {
        $startDate = now()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        if ($request->filled('date')) {
            $dates = explode(' : ', $request->date);
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                    $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                } catch (\Throwable $th) {}
            }
        }

        $allDates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $allDates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        $absensiData = AbsensiSholat::select(
            'karyawan_id',
            'tanggal',
            DB::raw("MAX(CASE WHEN jenis_sholat = 'duha' THEN jam_sholat END) as duha"),
            DB::raw("MAX(CASE WHEN jenis_sholat = 'dzuhur' THEN jam_sholat END) as dzuhur"),
            DB::raw("MAX(CASE WHEN jenis_sholat = 'izin' THEN jam_sholat END) as izin")
        )
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->groupBy('karyawan_id', 'tanggal')
        ->get()
        ->groupBy('karyawan_id');

        $karyawans = Karyawan::orderBy('name');

        if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            $karyawans->where('id', Auth::user()->karyawan->id);
        }

        $karyawans = $karyawans->get();

        if ($request->ajax()) {
            return DataTables::of($karyawans)
                ->addColumn('karyawan', fn ($row) => $row->name ?? '-')

                ->addColumn('absensi_list', function ($row) use ($absensiData, $allDates) {
                    $userAbsensi = $absensiData
                        ->get($row->id, collect())
                        ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

                    $html = '<div class="gap-1 d-flex flex-column">';

                    foreach ($allDates as $tanggal) {
                        $dayData = $userAbsensi->get($tanggal);
                        $tanggalDisplay = Carbon::parse($tanggal)->format('d/m/Y');

                        if ($dayData) {
                            if ($dayData->izin) {
                                $html .= '<div class="d-flex justify-content-between" style="font-size: 0.85rem; padding: 2px 4px; background: rgba(0,0,0,0.03); border-radius: 3px;">
                                    <span>' . $tanggalDisplay . '</span>
                                    <div class="gap-2 d-flex">
                                        <span class="badge bg-info"><i class="fas fa-info-circle"></i> Izin</span>
                                    </div>
                                </div>';
                                continue;
                            }
                            $duha = $dayData->duha
                                ? '<span class="text-success">✔ ' . Carbon::parse($dayData->duha)->format('H:i') . '</span>'
                                : '<span class="text-danger">✖</span>';
                            $dzuhur = $dayData->dzuhur
                                ? '<span class="text-success">✔ ' . Carbon::parse($dayData->dzuhur)->format('H:i') . '</span>'
                                : '<span class="text-danger">✖</span>';
                        } else {
                            $duha = '<span class="text-muted">-</span>';
                            $dzuhur = '<span class="text-muted">-</span>';
                        }

                        $html .= '<div class="d-flex justify-content-between" style="font-size: 0.85rem; padding: 2px 4px; background: rgba(0,0,0,0.03); border-radius: 3px;">
                            <span>' . $tanggalDisplay . '</span>
                            <div class="gap-2 d-flex">
                                <span>Duha: ' . $duha . '</span>
                                <span>Dzuhur: ' . $dzuhur . '</span>
                            </div>
                        </div>';
                    }
                    $html .= '</div>';
                    return $html;
                })

                ->addColumn('aksi', function ($row) {
                    $buttons = '<div class="btn-group" role="group">';
                    if (Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                        $buttons .= '<button class="btn btn-sm btn-warning btn-edit" data-id="' . $row->id . '" title="Edit data">
                                        <i class="fas fa-edit"></i>
                                    </button>';
                        $buttons .= '<button class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus data">
                                        <i class="fas fa-trash"></i>
                                    </button>';
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })

                ->rawColumns(['absensi_list', 'aksi'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('dashboard.absensis.rekap-sholat.index')->with('dateRange', "$startDate : $endDate");
    }

    public function show($id)
    {
        $absensi = AbsensiSholat::with(['karyawan'])->where('id', $id)->first();

        if (!$absensi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'           => $absensi->id,
                'karyawan'     => $absensi->karyawan->name ?? '-',
                'tanggal'      => Carbon::parse($absensi->tanggal)->format('d-m-Y'),
                'jenis_sholat' => $absensi->jenis_sholat,
                'jam_sholat'   => Carbon::parse($absensi->jam_sholat)->format('H:i'),
                'area'         => $absensi->area_name ?? '-',
            ]
        ]);
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus data ini'], 403);
        }

        $absensi = AbsensiSholat::find($id);

        if (!$absensi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $absensi->delete();

        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
    }

    public function statistik(Request $request)
    {
        $query = AbsensiSholat::query();

        if ($request->filled('date')) {
            $dates = explode(' : ', $request->date);
            if (count($dates) === 2) {
                $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                $query     = $query->whereBetween('tanggal', [$startDate, $endDate]);
            }
        } else {
            $query->whereMonth('tanggal', now()->month)
                  ->whereYear('tanggal', now()->year);
        }

        $total = $query->distinct('karyawan_id')->count('karyawan_id');
        $duha = (clone $query)->where('jenis_sholat', 'duha')->distinct('karyawan_id')->count('karyawan_id');
        $dzuhur = (clone $query)->where('jenis_sholat', 'dzuhur')->distinct('karyawan_id')->count('karyawan_id');
        $karyawanCount = (clone $query)->distinct('karyawan_id')->count('karyawan_id');

        return response()->json([
            'success' => true,
            'data' => [
                'total'          => $total,
                'duha'          => $duha,
                'dzuhur'        => $dzuhur,
                'karyawan_count' => $karyawanCount,
            ]
        ]);
    }
}
