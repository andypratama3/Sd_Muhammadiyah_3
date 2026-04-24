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

            // 3. Loop SEMUA karyawan
            foreach ($karyawanList as $karyawan) {

                // 4. Ambil absensi milik karyawan ini, group per tanggal
                $karyawanAbsensi = isset($absensiRecords[$karyawan->id])
                    ? $absensiRecords[$karyawan->id]->groupBy(fn($r) => $r->tanggal->format('Y-m-d'))
                    : collect();

                // 5. Jika karyawan tidak punya absensi di periode ini,
                //    tampilkan 1 baris kosong agar semua karyawan muncul + ada tombol Tambah
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
                        'duha'            => $izin ? 'Izin' : ($duha   ? Carbon::parse($duha->jam_sholat)->format('H:i')   : '-'),
                        'dzuhur'          => $izin ? 'Izin' : ($dzuhur ? Carbon::parse($dzuhur->jam_sholat)->format('H:i') : '-'),
                        'is_izin'         => (bool) $izin,
                    ];
                }
            }

            return DataTables::of($data)
                ->addColumn('duha', function ($row) {
                    if ($row['is_izin']) return '<span class="badge bg-info">Izin</span>';
                    if ($row['duha'] === '-') return '<span class="text-muted">-</span>';
                    return $row['duha'];
                })
                ->addColumn('dzuhur', function ($row) {
                    if ($row['is_izin']) return '<span class="badge bg-info">Izin</span>';
                    if ($row['dzuhur'] === '-') return '<span class="text-muted">-</span>';
                    return $row['dzuhur'];
                })
                ->addColumn('aksi', function ($row) {
                    $buttons = '<div class="btn-group" role="group">';

                    if (Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                        if ($row['id']) {
                            // Ada data → tombol Edit & Hapus
                            $buttons .= '<button class="btn btn-sm btn-warning btn-edit"
                                            data-id="' . $row['id'] . '"
                                            title="Edit data absensi">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>';
                            $buttons .= '<button class="btn btn-sm btn-danger btn-delete"
                                            data-id="' . $row['id'] . '"
                                            title="Hapus data absensi">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>';
                        } else {
                            // Tidak ada data → tombol Tambah
                            $buttons .= '<button class="btn btn-sm btn-primary btn-add"
                                            data-karyawan-id="' . $row['karyawan_id'] . '"
                                            data-karyawan-nama="' . e($row['karyawan']) . '"
                                            title="Tambah data absensi sholat">
                                            <i class="fas fa-plus"></i> Tambah
                                        </button>';
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

    /**
     * Simpan data absensi sholat baru (mode Tambah)
     */
    public function store(Request $request)
    {
        try {
            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menambah data'
                ], 403);
            }

            $validated = $request->validate([
                'karyawan_id'  => 'required|exists:karyawans,id',
                'tanggal'      => 'required|date',
                'jenis_sholat' => 'required|in:duha,dzuhur,izin',
                'jam_sholat'   => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            ], [
                'karyawan_id.required'  => 'Karyawan harus dipilih',
                'karyawan_id.exists'    => 'Karyawan tidak ditemukan',
                'tanggal.required'      => 'Tanggal harus diisi',
                'tanggal.date'          => 'Format tanggal tidak valid',
                'jenis_sholat.required' => 'Jenis sholat harus dipilih',
                'jenis_sholat.in'       => 'Jenis sholat tidak valid',
                'jam_sholat.regex'      => 'Format jam tidak valid (HH:MM)',
            ]);

            // Normalisasi jam ke H:i:s
            $jamSholat = $validated['jam_sholat'] ?? null;
            if ($jamSholat && strlen($jamSholat) === 5) {
                $jamSholat .= ':00';
            }

            // Gunakan updateOrCreate agar tidak duplikat
            $absensi = AbsensiSholat::updateOrCreate(
                [
                    'karyawan_id'  => $validated['karyawan_id'],
                    'tanggal'      => $validated['tanggal'],
                    'jenis_sholat' => $validated['jenis_sholat'],
                ],
                [
                    'jam_sholat' => $jamSholat,
                ]
            );

            \Log::info('RekapAbsensiSholatController - Store Success', [
                'absensi_id' => $absensi->id,
                'user_id'    => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data absensi sholat berhasil ditambahkan',
                'data'    => $absensi
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->validator->errors()
            ], 422);

        } catch (\Throwable $th) {
            \Log::error('RekapAbsensiSholatController - Store Error', [
                'error' => $th->getMessage(),
                'file'  => $th->getFile(),
                'line'  => $th->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil detail data absensi sholat (untuk modal edit)
     */
    public function show($id)
    {
        $absensi = AbsensiSholat::with(['karyawan'])->where('id', $id)->first();

        if (!$absensi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'           => $absensi->id,
                'karyawan'     => $absensi->karyawan->name ?? '-',
                'tanggal'      => Carbon::parse($absensi->tanggal)->format('d-m-Y'),
                'jenis_sholat' => $absensi->jenis_sholat,
                'jam_sholat'   => $absensi->jam_sholat
                    ? Carbon::parse($absensi->jam_sholat)->format('H:i')
                    : null,
                'area'         => $absensi->area_name ?? '-',
            ]
        ]);
    }

    /**
     * Update data absensi sholat
     */
    public function update(Request $request, $id)
    {
        try {
            $absensi = AbsensiSholat::find($id);

            if (!$absensi) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                if ($absensi->karyawan_id !== Auth::user()->karyawan->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah data ini'
                    ], 403);
                }
            }

            $validated = $request->validate([
                'tanggal'      => 'required|date',
                'jenis_sholat' => 'required|in:duha,dzuhur,izin',
                'jam_sholat'   => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            ], [
                'tanggal.required'      => 'Tanggal harus diisi',
                'tanggal.date'          => 'Format tanggal tidak valid',
                'jenis_sholat.required' => 'Jenis sholat harus dipilih',
                'jenis_sholat.in'       => 'Jenis sholat tidak valid',
                'jam_sholat.regex'      => 'Format jam tidak valid (HH:MM)',
            ]);

            // Normalisasi jam ke H:i:s
            $jamSholat = $validated['jam_sholat'] ?? null;
            if ($jamSholat && strlen($jamSholat) === 5) {
                $jamSholat .= ':00';
            }

            $absensi->update([
                'tanggal'      => $validated['tanggal'],
                'jenis_sholat' => $validated['jenis_sholat'],
                'jam_sholat'   => $jamSholat,
            ]);

            \Log::info('RekapAbsensiSholatController - Update Success', [
                'absensi_id' => $id,
                'user_id'    => Auth::id(),
            ]);

            return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->validator->errors()
            ], 422);

        } catch (\Throwable $th) {
            \Log::error('RekapAbsensiSholatController - Update Error', [
                'error'      => $th->getMessage(),
                'absensi_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus data absensi sholat
     */
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

        \Log::info('RekapAbsensiSholatController - Delete Success', [
            'absensi_id' => $id,
            'user_id'    => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
    }

    /**
     * Statistik absensi sholat
     */
    public function statistik(Request $request)
    {
        $query = AbsensiSholat::query();

        if ($request->filled('date')) {
            $dates = explode(' : ', $request->date);
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                    $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                    $query->whereBetween('tanggal', [$startDate, $endDate]);
                } catch (\Throwable $th) {}
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