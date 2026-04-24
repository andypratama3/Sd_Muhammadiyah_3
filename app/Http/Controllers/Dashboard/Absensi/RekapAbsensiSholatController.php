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
        $startDate = now()->format('Y-m-d');
        $endDate   = now()->format('Y-m-d');

        if ($request->filled('date')) {
            $dates = explode(' : ', $request->date);
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                    $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                } catch (\Throwable $th) {
                    // Biarkan default (hari ini)
                }
            }
        }

        if ($request->ajax()) {
            // 1. Ambil semua karyawan
            $karyawanQuery = Karyawan::orderBy('name');
            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                $karyawanQuery->where('id', Auth::user()->karyawan->id);
            }
            $karyawanList = $karyawanQuery->get();

            // 2. Ambil semua absensi dalam range, group by karyawan_id → tanggal
            $absensiRecords = AbsensiSholat::whereBetween('tanggal', [$startDate, $endDate])
                ->get()
                ->groupBy([
                    fn($r) => $r->karyawan_id,
                    fn($r) => Carbon::parse($r->tanggal)->format('Y-m-d'),
                ]);

            // 3. Daftar tanggal terbaru duluan
            $dateRange = [];
            $current   = Carbon::parse($endDate);
            $start     = Carbon::parse($startDate);
            while ($current->gte($start)) {
                $dateRange[] = $current->format('Y-m-d');
                $current->subDay();
            }

            $data    = [];
            $isAdmin = Auth::user()->hasAnyRole(['admin', 'superadmin']);

            foreach ($dateRange as $tanggal) {
                foreach ($karyawanList as $karyawan) {

                    $dayRecords = $absensiRecords[$karyawan->id][$tanggal] ?? collect();

                    $duha   = $dayRecords->firstWhere('jenis_sholat', 'duha');
                    $dzuhur = $dayRecords->firstWhere('jenis_sholat', 'dzuhur');
                    $izin   = $dayRecords->firstWhere('jenis_sholat', 'izin');

                    $data[] = [
                        'karyawan_id'     => $karyawan->id,
                        'karyawan'        => $karyawan->name,
                        'tanggal'         => $tanggal,
                        'tanggal_display' => Carbon::parse($tanggal)
                                                ->locale('id')
                                                ->translatedFormat('l, d F Y'),

                        // ID per jenis — null = belum ada
                        'duha_id'         => $duha   ? $duha->id   : null,
                        'dzuhur_id'       => $dzuhur ? $dzuhur->id : null,
                        'izin_id'         => $izin   ? $izin->id   : null,

                        // Tampilan jam
                        'duha_jam'        => $duha   ? Carbon::parse($duha->jam_sholat)->format('H:i')   : null,
                        'dzuhur_jam'      => $dzuhur ? Carbon::parse($dzuhur->jam_sholat)->format('H:i') : null,

                        'is_izin'         => (bool) $izin,
                        'is_admin'        => $isAdmin,
                    ];
                }
            }

            return DataTables::of($data)

                // ── Kolom DHUHA ──────────────────────────────────────────────
                ->addColumn('duha', function ($row) {
                    // Jika sedang izin, tidak perlu tombol apapun di kolom ini
                    if ($row['is_izin']) {
                        return '<span class="badge bg-info text-white p-1" style="font-size: 0.85rem; min-width: 50px;">Izin</span>';
                    }

                    $html = '';

                    if ($row['duha_id']) {
                        // Sudah ada → tampilkan jam + tombol Edit & Hapus
                        $html .= '<div class="d-flex flex-column align-items-center gap-1">';
                        $html .= '<span class="badge bg-success p-1" style="font-size: 0.85rem; min-width: 50px;">' . e($row['duha_jam']) . '</span>';

                        if ($row['is_admin']) {
                            $html .= '<div class="d-flex gap-1">';
                            $html .= $this->btnEdit($row['duha_id'], 'sm');
                            $html .= $this->btnDelete($row['duha_id'], 'sm');
                            $html .= '</div>';
                        }

                        $html .= '</div>';
                    } else {
                        // Belum ada → tombol Tambah (admin saja)
                        $html .= '<span class="text-muted d-block mb-1">-</span>';

                        if ($row['is_admin']) {
                            $html .= $this->btnAdd(
                                $row['karyawan_id'],
                                $row['karyawan'],
                                $row['tanggal'],
                                'duha',
                                'btn-primary'
                            );
                        }
                    }

                    return $html;
                })

                // ── Kolom DZUHUR ─────────────────────────────────────────────
                ->addColumn('dzuhur', function ($row) {
                    if ($row['is_izin']) {
                        return '<span class="badge bg-info text-white p-1" style="font-size: 0.85rem; min-width: 50px;">Izin</span>';
                    }

                    $html = '';

                    if ($row['dzuhur_id']) {
                        $html .= '<div class="d-flex flex-column align-items-center gap-1">';
                        $html .= '<span class="badge bg-success p-1" style="font-size: 0.85rem; min-width: 50px;">' . e($row['dzuhur_jam']) . '</span>';

                        if ($row['is_admin']) {
                            $html .= '<div class="d-flex gap-1">';
                            $html .= $this->btnEdit($row['dzuhur_id'], 'sm');
                            $html .= $this->btnDelete($row['dzuhur_id'], 'sm');
                            $html .= '</div>';
                        }

                        $html .= '</div>';
                    } else {
                        $html .= '<span class="text-muted d-block mb-1">-</span>';

                        if ($row['is_admin']) {
                            $html .= $this->btnAdd(
                                $row['karyawan_id'],
                                $row['karyawan'],
                                $row['tanggal'],
                                'dzuhur',
                                'btn-primary'
                            );
                        }
                    }

                    return $html;
                })

                // ── Kolom AKSI (khusus Izin) ─────────────────────────────────
                ->addColumn('aksi', function ($row) {
                    if (!$row['is_admin']) {
                        return '<span class="text-muted">-</span>';
                    }

                    $html = '<div class="d-flex gap-1 justify-content-center">';

                    if ($row['izin_id']) {
                        // Sudah izin → Edit + Hapus izin
                        $html .= $this->btnEdit($row['izin_id'], 'sm', 'Izin');
                        $html .= $this->btnDelete($row['izin_id'], 'sm');
                    } else {
                        // Belum izin → Tambah Izin
                        $html .= $this->btnAdd(
                            $row['karyawan_id'],
                            $row['karyawan'],
                            $row['tanggal'],
                            'izin',
                            'btn-secondary'
                        );
                    }

                    $html .= '</div>';
                    return $html;
                })

                ->rawColumns(['duha', 'dzuhur', 'aksi'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('dashboard.absensis.rekap-sholat.index');
    }

    // ── Helpers tombol ───────────────────────────────────────────────────────

    /**
     * Tombol Edit (orange) — dipakai di kolom Duha, Dzuhur, dan Aksi
     */
    private function btnEdit(string $id, string $size = 'sm', string $label = ''): string
    {
        $text = $label ? '<i class="fas fa-edit"></i> ' . e($label) : '<i class="fas fa-edit"></i>';
        $classSize = $size ? 'btn-' . $size : 'btn-sm';
        return '<button class="btn ' . $classSize . ' btn-warning btn-edit"
                    data-id="' . $id . '"
                    style="font-size: 0.85rem; padding: 0.25rem 0.5rem;"
                    title="Edit">
                    ' . $text . '
                </button>';
    }

    /**
     * Tombol Hapus (merah)
     */
    private function btnDelete(string $id, string $size = 'sm'): string
    {
        $classSize = $size ? 'btn-' . $size : 'btn-sm';
        return '<button class="btn ' . $classSize . ' btn-danger btn-delete"
                    data-id="' . $id . '"
                    style="font-size: 0.85rem; padding: 0.25rem 0.5rem;"
                    title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>';
    }

    /**
     * Tombol Tambah — membawa data-jenis agar modal langsung tahu jenis yang ditambah
     *
     * @param string $jenisSholat duha | dzuhur | izin
     * @param string $btnClass    Bootstrap btn color class
     */
    private function btnAdd(
        string    $karyawanId,
        string $karyawanNama,
        string $tanggal,
        string $jenisSholat,
        string $btnClass = 'btn-primary'
    ): string {
        return '<button class="btn btn-sm ' . $btnClass . ' btn-add"
                    data-karyawan-id="'   . e($karyawanId)   . '"
                    data-karyawan-nama="' . e($karyawanNama) . '"
                    data-tanggal="'       . e($tanggal)       . '"
                    data-jenis="'         . e($jenisSholat)   . '"
                    style="font-size: 0.85rem; padding: 0.25rem 0.6rem; font-weight: 500;"
                    title="Tambah ' . ucfirst($jenisSholat) . '">
                    <i class="fas fa-plus"></i> ' . ucfirst($jenisSholat) . '
                </button>';
    }

    // ── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menambah data',
                ], 403);
            }

            $validated = $request->validate([
                'karyawan_id'  => 'required|exists:karyawans,id',
                'tanggal'      => 'required|date',
                'jenis_sholat' => 'required|in:duha,dzuhur,izin',
                'jam_sholat'   => [
                    $request->jenis_sholat === 'izin' ? 'nullable' : 'required',
                    'regex:/^\d{2}:\d{2}(:\d{2})?$/'
                ],
            ], [
                'karyawan_id.required'  => 'Pilih karyawan terlebih dahulu.',
                'karyawan_id.exists'    => 'Karyawan yang dipilih tidak valid.',
                'tanggal.required'      => 'Tanggal absensi harus diisi.',
                'tanggal.date'          => 'Format tanggal tidak valid.',
                'jenis_sholat.required' => 'Jenis sholat/izin harus dipilih.',
                'jenis_sholat.in'       => 'Jenis sholat tidak valid.',
                'jam_sholat.required'   => 'Jam sholat harus diisi jika bukan izin.',
                'jam_sholat.regex'      => 'Format jam tidak valid (Gunakan format HH:MM).',
            ]);

            $jamSholat = $validated['jam_sholat'] ?? null;
            if ($jamSholat && strlen($jamSholat) === 5) {
                $jamSholat .= ':00';
            }

            if($request->jenis_sholat == 'izin') {
                $jamSholat = "00:00";
            }

            $absensi = AbsensiSholat::updateOrCreate(
                [
                    'jenis_sholat' => $validated['jenis_sholat'],
                    'karyawan_id'  => $validated['karyawan_id'],
                    'tanggal'      => $validated['tanggal'],
                ],
                ['jam_sholat' => $jamSholat]
            );

            \Log::info('RekapAbsensiSholatController@store success', [
                'absensi_id' => $absensi->id,
                'user_id'    => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data absensi sholat berhasil ditambahkan',
                'data'    => $absensi,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->validator->errors(),
            ], 422);

        } catch (\Throwable $th) {
            \Log::error('RekapAbsensiSholatController@store error', [
                'error' => $th->getMessage(),
                'file'  => $th->getFile(),
                'line'  => $th->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data: ' . $th->getMessage(),
            ], 500);
        }
    }

    // ── Show ─────────────────────────────────────────────────────────────────

    public function show($id)
    {
        $absensi = AbsensiSholat::with('karyawan')->find($id);

        if (!$absensi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $absensi->id,
                'karyawan'     => $absensi->karyawan->name ?? '-',
                'tanggal'      => Carbon::parse($absensi->tanggal)->format('d-m-Y'),
                'jenis_sholat' => $absensi->jenis_sholat,
                'jam_sholat'   => $absensi->jam_sholat
                                    ? Carbon::parse($absensi->jam_sholat)->format('H:i')
                                    : null,
            ],
        ]);
    }

    // ── Update ───────────────────────────────────────────────────────────────

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
                        'message' => 'Anda tidak memiliki akses untuk mengubah data ini',
                    ], 403);
                }
            }

            $validated = $request->validate([
                'tanggal'      => 'required|date',
                'jenis_sholat' => 'required|in:duha,dzuhur,izin',
                'jam_sholat'   => [
                    $request->jenis_sholat === 'izin' ? 'nullable' : 'required',
                    'regex:/^\d{2}:\d{2}(:\d{2})?$/'
                ],
            ], [
                'tanggal.required'      => 'Tanggal absensi harus diisi.',
                'tanggal.date'          => 'Format tanggal tidak valid.',
                'jenis_sholat.required' => 'Jenis sholat/izin harus dipilih.',
                'jenis_sholat.in'       => 'Jenis sholat tidak valid.',
                'jam_sholat.required'   => 'Jam sholat harus diisi jika bukan izin.',
                'jam_sholat.regex'      => 'Format jam tidak valid (Gunakan format HH:MM).',
            ]);

            $jamSholat = $validated['jam_sholat'] ?? null;
            if ($jamSholat && strlen($jamSholat) === 5) {
                $jamSholat .= ':00';
            }

            $absensi->update([
                'tanggal'      => $validated['tanggal'],
                'jenis_sholat' => $validated['jenis_sholat'],
                'jam_sholat'   => $jamSholat,
            ]);

            \Log::info('RekapAbsensiSholatController@update success', [
                'absensi_id' => $id,
                'user_id'    => Auth::id(),
            ]);

            return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->validator->errors(),
            ], 422);

        } catch (\Throwable $th) {
            \Log::error('RekapAbsensiSholatController@update error', [
                'error'      => $th->getMessage(),
                'absensi_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $th->getMessage(),
            ], 500);
        }
    }

    // ── Destroy ──────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus data ini',
            ], 403);
        }

        $absensi = AbsensiSholat::find($id);

        if (!$absensi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $absensi->delete();

        \Log::info('RekapAbsensiSholatController@destroy success', [
            'absensi_id' => $id,
            'user_id'    => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
    }

    // ── Statistik ────────────────────────────────────────────────────────────

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

        return response()->json([
            'success' => true,
            'data'    => [
                'total'          => (clone $query)->distinct('karyawan_id')->count('karyawan_id'),
                'duha'           => (clone $query)->where('jenis_sholat', 'duha')->distinct('karyawan_id')->count('karyawan_id'),
                'dzuhur'         => (clone $query)->where('jenis_sholat', 'dzuhur')->distinct('karyawan_id')->count('karyawan_id'),
                'karyawan_count' => (clone $query)->distinct('karyawan_id')->count('karyawan_id'),
            ],
        ]);
    }
}