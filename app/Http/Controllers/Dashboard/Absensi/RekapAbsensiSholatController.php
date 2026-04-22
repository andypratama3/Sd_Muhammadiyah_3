<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSholat;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RekapAbsensiSholatController extends Controller
{
    public function index(Request $request)
    {
        $query = AbsensiSholat::select(
                'karyawan_id',
                'tanggal',

                DB::raw("MAX(CASE WHEN jenis_sholat = 'duha' THEN jam_sholat END) as duha"),
                DB::raw("MAX(CASE WHEN jenis_sholat = 'dzuhur' THEN jam_sholat END) as dzuhur")
            )
            ->with('karyawan')
            ->groupBy('karyawan_id', 'tanggal');

        if ($request->ajax()) {

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                $query->where('karyawan_id', Auth::user()->karyawan->id);
            }

            if ($request->filled('date')) {
                $dates = explode(' : ', $request->date);
                if (count($dates) === 2) {
                    try {
                        $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                        $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                        $query->whereBetween('tanggal', [$startDate, $endDate]);
                    } catch (\Throwable $th) {}
                }
            }

            $query->orderBy('karyawan_id');

            return DataTables::of($query)
                ->addColumn('karyawan', fn ($row) => $row->karyawan->name ?? '-')

                ->editColumn('tanggal', function ($row) {
                    return Carbon::parse($row->tanggal)
                        ->locale('id')
                        ->translatedFormat('l, d F Y');
                })

                // ✅ DUHA
                ->editColumn('duha', function ($row) {
                    if ($row->duha) {
                        return '<span class="text-success">✔</span> ' . $row->duha;
                    }
                    return '<span class="text-danger">✖</span> -';
                })

                // ✅ DZUHUR
                ->editColumn('dzuhur', function ($row) {
                    if ($row->dzuhur) {
                        return '<span class="text-success">✔</span> ' . $row->dzuhur;
                    }
                    return '<span class="text-danger">✖</span> -';
                })

                ->filterColumn('karyawan', function ($query, $keyword) {
                    $query->whereHas('karyawan', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->rawColumns(['duha', 'dzuhur']) // ⚠️ penting biar HTML ke render
                ->addIndexColumn()
                ->make(true);
        }

        return view('dashboard.absensis.rekap-sholat.index');
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
