<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSholat;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class RekapAbsensiSholatController extends Controller
{
    public function index(Request $request)
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate   = now()->endOfMonth()->format('Y-m-d');

        if ($request->filled('date')) {
            $dates = explode(' : ', $request->date);
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                    $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                } catch (\Throwable $th) {}
            }
        }

        if ($request->ajax()) {
            // 1. Ambil semua karyawan diurutkan berdasarkan nama
            $karyawanQuery = Karyawan::orderBy('name');
            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                $karyawanQuery->where('id', Auth::user()->karyawan->id);
            }
            $karyawanList = $karyawanQuery->get();

            // 2. Ambil semua data absensi dalam rentang tanggal, group by karyawan_id
            $absensiRecords = AbsensiSholat::whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'desc')
                ->get()
                ->groupBy('karyawan_id');

            $data = [];

            // 3. Loop SEMUA karyawan terlebih dahulu
            foreach ($karyawanList as $karyawan) {

                // 4. Ambil absensi milik karyawan ini, group per tanggal
                $karyawanAbsensi = isset($absensiRecords[$karyawan->id])
                    ? $absensiRecords[$karyawan->id]->groupBy(fn($r) => $r->tanggal->format('Y-m-d'))
                    : collect();

                // 5. Jika karyawan tidak punya absensi di periode ini,
                //    tetap tampilkan 1 baris kosong agar semua karyawan muncul
                if ($karyawanAbsensi->isEmpty()) {
                    $data[] = [
                        'id'              => null,
                        'karyawan_id'     => $karyawan->id,
                        'karyawan'        => $karyawan->name,
                        'tanggal'         => null,
                        'tanggal_display' => '-',
                        'duha'            => '-',
                        'dzuhur'          => '-',
                        'is_izin'         => false,
                    ];
                    continue;
                }

                // 6. Loop per tanggal (sudah di-group per hari)
                foreach ($karyawanAbsensi as $tanggal => $dayRecords) {
                    $duha   = $dayRecords->firstWhere('jenis_sholat', 'duha');
                    $dzuhur = $dayRecords->firstWhere('jenis_sholat', 'dzuhur');
                    $izin   = $dayRecords->firstWhere('jenis_sholat', 'izin');

                    $data[] = [
                        'id'              => $dayRecords->first()->id,
                        'karyawan_id'     => $karyawan->id,
                        'karyawan'        => $karyawan->name,
                        'tanggal'         => $tanggal,
                        'tanggal_display' => Carbon::parse($tanggal)->translatedFormat('l, d F Y'),
                        'duha'            => $izin ? 'Izin' : ($duha   ? Carbon::parse($duha->jam_sholat)->format('H:i:s')   : '-'),
                        'dzuhur'          => $izin ? 'Izin' : ($dzuhur ? Carbon::parse($dzuhur->jam_sholat)->format('H:i:s') : '-'),
                        'is_izin'         => (bool) $izin,
                    ];
                }
            }

            return DataTables::of($data)
                ->addColumn('duha', function ($row) {
                    if ($row['is_izin']) return '<span class="badge bg-info">Izin</span>';
                    return $row['duha'];
                })
                ->addColumn('dzuhur', function ($row) {
                    if ($row['is_izin']) return '<span class="badge bg-info">Izin</span>';
                    return $row['dzuhur'];
                })
                ->addColumn('aksi', function ($row) {
                    $buttons = '<div class="btn-group" role="group">';
                    if (Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                        if ($row['id']) {
                            $buttons .= '<button class="btn btn-sm btn-warning btn-edit" data-id="' . $row['id'] . '" title="Edit data absensi">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>';
                            $buttons .= '<button class="btn btn-sm btn-danger btn-delete" data-id="' . $row['id'] . '" title="Hapus data absensi">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>';
                        } else {
                            $buttons .= '<span class="text-muted small">No Data</span>';
                        }
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['duha', 'dzuhur', 'aksi'])
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

    public function update(Request $request, $id)
    {
        try {
            $absensi = AbsensiSholat::find($id);

            if (!$absensi) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                if ($absensi->karyawan_id !== Auth::user()->karyawan->id) {
                    return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengubah data ini'], 403);
                }
            }

            $validated = $request->validate([
                'tanggal'      => 'required|date',
                'jenis_sholat' => 'required|in:duha,dzuhur,izin',
                'jam_sholat'   => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            ]);

            $jamSholat = $validated['jam_sholat'];
            if ($jamSholat && strlen($jamSholat) === 5) {
                $jamSholat .= ':00';
            }

            $absensi->update([
                'tanggal'      => $validated['tanggal'],
                'jenis_sholat' => $validated['jenis_sholat'],
                'jam_sholat'   => $jamSholat,
            ]);

            return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui data: ' . $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus data ini'
            ], 403);
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

        $total         = $query->distinct('karyawan_id')->count('karyawan_id');
        $duha          = (clone $query)->where('jenis_sholat', 'duha')->distinct('karyawan_id')->count('karyawan_id');
        $dzuhur        = (clone $query)->where('jenis_sholat', 'dzuhur')->distinct('karyawan_id')->count('karyawan_id');
        $karyawanCount = (clone $query)->distinct('karyawan_id')->count('karyawan_id');

        return response()->json([
            'success' => true,
            'data' => [
                'total'          => $total,
                'duha'           => $duha,
                'dzuhur'         => $dzuhur,
                'karyawan_count' => $karyawanCount,
            ]
        ]);
    }
}