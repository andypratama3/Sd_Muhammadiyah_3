<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use PDF;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\RekapAbsensiExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class RekapAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with(['karyawan.user.roles', 'lokasiAbsensi', 'jamKerja'])
            ->orderBy('tanggal', 'desc');

        if ($request->ajax()) {
            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                $query->where('karyawan_id', Auth::user()->karyawan->id);
            }

            if ($request->filled('date')) {
                $dates = explode(' : ', $request->date);
                if (count($dates) === 2) {
                    $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                    $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                    $query     = $query->whereBetween('tanggal', [$startDate, $endDate]);
                }
            }

            if ($request->status_kehadiran) {
                $query->where('status_kehadiran', $request->status_kehadiran);
            }

            return DataTables::of($query)
                ->addColumn('karyawan', fn ($row) => $row->karyawan->name ?? '-')
                ->addColumn('tanggal', function ($row) {
                    return \Carbon\Carbon::parse($row->tanggal)
                        ->locale('id')
                        ->translatedFormat('d F Y');
                })
                ->addColumn('hari', function ($row) {
                    return \Carbon\Carbon::parse($row->tanggal)
                        ->locale('id')
                        ->translatedFormat('l');
                })
                ->addColumn('status', function ($row) {
                    return match($row->status_kehadiran) {
                        'hadir'  => '<span class="badge bg-success"><i class="fas fa-check"></i> Hadir</span>',
                        'cuti'   => '<span class="badge bg-warning"><i class="fas fa-calendar-check"></i> Cuti</span>',
                        'izin'   => '<span class="badge bg-info"><i class="fas fa-file-alt"></i> Izin</span>',
                        'sakit'  => '<span class="badge bg-danger"><i class="fas fa-hospital-alt"></i> Sakit</span>',
                        'libur'  => '<span class="badge bg-purple"><i class="fas fa-bed"></i> Libur</span>',
                        'alpha'  => '<span class="badge bg-secondary"><i class="fas fa-ban"></i> Alpha</span>',
                        default  => '<span class="badge bg-secondary"><i class="fas fa-question"></i> Tidak Diketahui</span>',
                    };
                })
                ->addColumn('jenis_pegawai', fn ($row) =>
                    $row->karyawan?->jenis_pegawai_from_role ?? '-'
                )
                ->addColumn('jam_masuk', fn ($row) =>
                    $row->jam_masuk ? \Carbon\Carbon::parse($row->jam_masuk)->format('H:i') : '-'
                )
                ->addColumn('jam_pulang', fn ($row) =>
                    $row->jam_pulang ? \Carbon\Carbon::parse($row->jam_pulang)->format('H:i') : '-'
                )
                ->addColumn('keterangan', fn ($row) => $row->keterangan ?? '-')
                ->addColumn('rp_masuk', fn ($row) =>
                    'Rp. ' . number_format(floatval($row->rp_masuk ?? 0), 0, '.', '')
                )
                ->addColumn('rp_pulang', fn ($row) =>
                    'Rp. ' . number_format(floatval($row->rp_pulang ?? 0), 0, '.', '')
                )
                ->addColumn('aksi', function ($row) {
                    $buttons = '<div class="btn-group" role="group">';
                    if (Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                        $buttons .= '<button class="btn btn-sm btn-warning btn-edit" data-id="' . $row->id . '" title="Edit data absensi">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>';
                        $buttons .= '<button class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus data absensi">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>';
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['status', 'libur', 'aksi'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('dashboard.absensis.rekap.index');
    }

    public function show($id)
    {
        try {
            $absensi = Absensi::with(['karyawan'])->where('id', $id)->first();

            if (!$absensi) {
                return response()->json(['message' => 'Data absensi tidak ditemukan'], 404);
            }

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                if ($absensi->karyawan_id !== Auth::user()->karyawan->id) {
                    return response()->json(['message' => 'Anda tidak memiliki akses untuk melihat data ini'], 403);
                }
            }

            return response()->json(['absensi' => $absensi]);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - show Error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            return response()->json(['message' => 'Gagal memuat data absensi'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $absensi = Absensi::find($id);

            if (!$absensi) {
                return response()->json(['message' => 'Data absensi tidak ditemukan'], 404);
            }

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                if ($absensi->karyawan_id !== Auth::user()->karyawan->id) {
                    return response()->json(['message' => 'Anda tidak memiliki akses untuk mengubah data ini'], 403);
                }
            }

            $validated = $request->validate([
                'tanggal'          => 'required|date',
                'status_kehadiran' => 'required|in:hadir,cuti,izin,sakit,alpha',
                'rp_masuk'         => 'nullable|numeric|min:0',
                'rp_pulang'        => 'nullable|numeric|min:0',
                'jam_masuk'        => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
                'jam_pulang'       => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
                'keterangan'       => 'nullable|string|max:500',
            ], [
                'tanggal.required'          => 'Tanggal harus diisi',
                'tanggal.date'              => 'Format tanggal tidak valid',
                'status_kehadiran.required' => 'Status kehadiran harus dipilih',
                'status_kehadiran.in'       => 'Status kehadiran tidak valid',
                'rp_masuk.numeric'          => 'Rp. masuk harus berupa angka',
                'rp_pulang.numeric'         => 'Rp. pulang harus berupa angka',
                'rp_masuk.min'              => 'Rp. masuk tidak boleh negatif',
                'rp_pulang.min'             => 'Rp. pulang tidak boleh negatif',
                'jam_masuk.regex'           => 'Format jam masuk tidak valid (HH:MM atau HH:MM:SS)',
                'jam_pulang.regex'          => 'Format jam pulang tidak valid (HH:MM atau HH:MM:SS)',
                'keterangan.max'            => 'Keterangan maksimal 500 karakter',
            ]);

            $normalizeTime = function (?string $time): ?string {
                if (empty($time)) return null;
                if (strlen($time) === 8) return $time;
                return $time . ':00';
            };

            $updateData = [
                'tanggal'          => $validated['tanggal'],
                'status_kehadiran' => $validated['status_kehadiran'],
                'keterangan'       => $validated['keterangan'] ?? null,
                'jam_masuk'        => $normalizeTime($validated['jam_masuk'] ?? null),
                'jam_pulang'       => $normalizeTime($validated['jam_pulang'] ?? null),
                'updated_by'       => Auth::id(),
            ];

            if (isset($validated['rp_masuk']) && $validated['rp_masuk'] !== null) {
                $updateData['rp_masuk'] = $validated['rp_masuk'];
            }
            if (isset($validated['rp_pulang']) && $validated['rp_pulang'] !== null) {
                $updateData['rp_pulang'] = $validated['rp_pulang'];
            }

            $absensi->update($updateData);

            \Log::info('RekapAbsensiController - Update Success', [
                'absensi_id'   => $id,
                'user_id'      => Auth::id(),
                'updated_data' => $validated,
            ]);

            return response()->json([
                'message' => 'Data absensi berhasil diperbarui',
                'absensi' => $absensi,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - Update Error', [
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'absensi_id' => $id,
            ]);
            return response()->json(['message' => 'Gagal mengupdate data absensi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $absensi = Absensi::find($id);

            if (!$absensi) {
                return response()->json(['message' => 'Data absensi tidak ditemukan'], 404);
            }

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda tidak memiliki akses untuk menghapus data ini',
                ], 403);
            }

            $absensi->delete();

            \Log::info('RekapAbsensiController - Delete Success', [
                'absensi_id' => $id,
                'user_id'    => Auth::id(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data absensi berhasil dihapus',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - Delete Error', [
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'absensi_id' => $id,
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus data absensi',
            ], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');

            \Log::info('RekapAbsensiController - exportPdf Started', [
                'request_date' => $request->date ?? 'null',
                'user_id'      => Auth::user()->id ?? 'null',
            ]);

            // ----------------------------------------------------------------
            // Resolusi label date range & nama file/zip
            // ----------------------------------------------------------------
            $dateRangeLabel = $this->resolveDateRangeLabel($request);
            $safeDateLabel  = $this->safeDateLabel($request);

            $ttdRusminiBase64 = $this->getTtdBase64(public_path('asset/img/ttd_bu_rusmini.png'));
            $ttdKepalaBase64  = $this->getTtdBase64(public_path('asset/img/tanda_tangan_kepala_sekolah.png'));

            $karyawanQuery = Karyawan::with('user.roles');
            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                $karyawanQuery->where('id', Auth::user()->karyawan->id);
            }
            $karyawanIds = $karyawanQuery->pluck('id');

            if ($karyawanIds->isEmpty()) {
                return redirect()->back()->with('warning', 'Tidak ada data karyawan untuk diekspor.');
            }

            // Buat folder temp
            $tempDir = storage_path('app/temp/rekap_pdf_' . now()->format('YmdHis') . '_' . uniqid());
            \File::makeDirectory($tempDir, 0755, true);

            $summaryData = collect();
            $fileCount   = 0;

            foreach ($karyawanIds as $karyawanId) {
                $karyawan = $this->getSingleKaryawanRekap($karyawanId, $request);

                if (!$karyawan || $karyawan->absensi->isEmpty()) {
                    unset($karyawan);
                    continue;
                }

                $totalMasuk  = $karyawan->absensi->sum(fn ($a) => floatval($a->rp_masuk  ?? 0));
                $totalPulang = $karyawan->absensi->sum(fn ($a) => floatval($a->rp_pulang ?? 0));

                $summaryData->push([
                    'name'  => $karyawan->name ?? '-',
                    'total' => $totalMasuk + $totalPulang,
                ]);

                $pdf = PDF::loadView('dashboard.absensis.rekap.pdf', [
                    'karyawans'        => collect([$karyawan]),
                    'dateRange'        => $dateRangeLabel,
                    'ttdRusminiBase64' => $ttdRusminiBase64,
                    'ttdKepalaBase64'  => $ttdKepalaBase64,
                    'summaryData'      => collect([]),
                    'grandTotal'       => 0,
                    'petugasName'      => Auth::user()->name ?? '-',
                ])->setPaper('a4', 'landscape');

                // Nama file: NamaKaryawan_DateRange.pdf
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $karyawan->name ?? 'karyawan');
                $pdfPath  = $tempDir . '/' . $safeName . '_' . $safeDateLabel . '.pdf';
                $pdf->save($pdfPath);

                unset($pdf, $karyawan);
                $fileCount++;
            }

            if ($fileCount === 0) {
                \File::deleteDirectory($tempDir);
                return redirect()->back()->with('warning', 'Tidak ada data absensi untuk diekspor.');
            }

            // Jika hanya 1 karyawan, langsung return PDF tanpa ZIP
            if ($fileCount === 1) {
                $pdfFile  = collect(\File::files($tempDir))->first();
                $content  = file_get_contents($pdfFile->getPathname());
                $filename = $pdfFile->getFilename();
                \File::deleteDirectory($tempDir);

                return response($content, 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }

            // Buat ZIP — nama ZIP mengikuti date range
            $zipFilename = 'Rekap_Absensi_' . $safeDateLabel . '.zip';
            $zipPath     = storage_path('app/temp/' . $zipFilename);

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                \File::deleteDirectory($tempDir);
                return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
            }

            foreach (\File::files($tempDir) as $file) {
                $zip->addFile($file->getPathname(), $file->getFilename());
            }
            $zip->close();

            \File::deleteDirectory($tempDir);

            \Log::info('RekapAbsensiController - exportPdf (ZIP) Success', [
                'filename'        => $zipFilename,
                'jumlah_karyawan' => $fileCount,
            ]);

            return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - exportPdf Error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal mengekspor PDF: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            \Log::info('RekapAbsensiController - exportExcel Started', [
                'request_date' => $request->date ?? 'null',
                'user_id'      => Auth::user()->id ?? 'null',
            ]);

            $safeDateLabel = $this->safeDateLabel($request);

            $karyawanQuery = Karyawan::with('user.roles');
            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                $karyawanQuery->where('id', Auth::user()->karyawan->id);
            }
            $karyawanIds = $karyawanQuery->pluck('id');

            if ($karyawanIds->isEmpty()) {
                return redirect()->back()->with('warning', 'Tidak ada data karyawan untuk diekspor.');
            }

            // Buat folder temp
            $tempDir = storage_path('app/temp/rekap_excel_' . now()->format('YmdHis') . '_' . uniqid());
            \File::makeDirectory($tempDir, 0755, true);

            $fileCount = 0;

            foreach ($karyawanIds as $karyawanId) {
                $karyawan = $this->getSingleKaryawanRekap($karyawanId, $request);

                if (!$karyawan || $karyawan->absensi->isEmpty()) {
                    unset($karyawan);
                    continue;
                }

                // Nama file: NamaKaryawan_DateRange.xlsx
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $karyawan->name ?? 'karyawan');
                $xlsxPath = $tempDir . '/' . $safeName . '_' . $safeDateLabel . '.xlsx';

                Excel::store(
                    new RekapAbsensiExport($request, $karyawanId),
                    $xlsxPath,
                    'local_absolute'
                );

                unset($karyawan);
                $fileCount++;
            }

            if ($fileCount === 0) {
                \File::deleteDirectory($tempDir);
                return redirect()->back()->with('warning', 'Tidak ada data absensi untuk diekspor.');
            }

            // Jika hanya 1 karyawan, langsung return XLSX tanpa ZIP
            if ($fileCount === 1) {
                $xlsxFile = collect(\File::files($tempDir))->first();
                $content  = file_get_contents($xlsxFile->getPathname());
                $filename = $xlsxFile->getFilename();
                \File::deleteDirectory($tempDir);

                return response($content, 200, [
                    'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }

            // Buat ZIP — nama ZIP mengikuti date range
            $zipFilename = 'Rekap_Absensi_' . $safeDateLabel . '.zip';
            $zipPath     = storage_path('app/temp/' . $zipFilename);

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                \File::deleteDirectory($tempDir);
                return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
            }

            foreach (\File::files($tempDir) as $file) {
                $zip->addFile($file->getPathname(), $file->getFilename());
            }
            $zip->close();

            \File::deleteDirectory($tempDir);

            \Log::info('RekapAbsensiController - exportExcel (ZIP) Success', [
                'filename'        => $zipFilename,
                'jumlah_karyawan' => $fileCount,
            ]);

            return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - exportExcel Error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal mengekspor Excel: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    /**
     * Label date range yang ditampilkan di dokumen (human-readable).
     * Contoh: "01 Januari 2025 - 31 Januari 2025" atau "April 2025"
     */
    private function resolveDateRangeLabel(Request $request): string
    {
        if ($request->filled('date')) {
            $dates = explode(' : ', $request->date);
            if (count($dates) === 2) {
                $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]));
                $end   = Carbon::createFromFormat('d-m-Y', trim($dates[1]));

                return $start->locale('id')->translatedFormat('d F Y')
                    . ' - '
                    . $end->locale('id')->translatedFormat('d F Y');
            }
        }

        return now()->locale('id')->translatedFormat('F Y');
    }

    /**
     * Label date range yang aman dipakai sebagai bagian nama file/zip.
     * Spasi & karakter khusus diganti underscore/strip.
     * Contoh: "01-01-2025_sd_31-01-2025" atau "April_2025"
     */
    private function safeDateLabel(Request $request): string
    {
        if ($request->filled('date')) {
            $dates = explode(' : ', $request->date);
            if (count($dates) === 2) {
                $start = trim($dates[0]); // sudah format d-m-Y
                $end   = trim($dates[1]);

                // Ganti - dengan _ agar lebih clean di nama file
                $start = str_replace('-', '_', $start);
                $end   = str_replace('-', '_', $end);

                return $start . '_sd_' . $end;
            }
        }

        // Fallback: bulan & tahun saat ini
        return now()->locale('id')->translatedFormat('F_Y');
    }

    /**
     * Resize gambar TTD ke max lebar tertentu lalu encode ke base64.
     */
    private function getTtdBase64(string $path, int $maxWidth = 200): string
    {
        if (!file_exists($path)) return '';

        [$origWidth, $origHeight, $type] = getimagesize($path);

        if ($origWidth <= $maxWidth) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        $ratio     = $maxWidth / $origWidth;
        $newWidth  = $maxWidth;
        $newHeight = (int) round($origHeight * $ratio);

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);

        $src = match($type) {
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default        => null,
        };

        if (!$src) {
            imagedestroy($dst);
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        ob_start();
        imagepng($dst, null, 6);
        $imageData = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Load satu karyawan + absensi + count per status dengan date filter.
     */
    private function getSingleKaryawanRekap($karyawanId, $request)
    {
        $applyDateFilter = function ($q) use ($request) {
            if ($request->filled('date')) {
                $dates = explode(' : ', $request->date);
                if (count($dates) === 2) {
                    $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                    $end   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                    $q->whereBetween('tanggal', [$start, $end]);
                }
            }
        };

        $query = Karyawan::with([
            'user.roles',
            'absensi' => function ($q) use ($applyDateFilter) {
                $applyDateFilter($q);
                $q->orderBy('tanggal', 'asc');
                $q->with('jamKerja:id,hari,is_hari_kerja');
            },
        ])->where('id', $karyawanId);

        foreach (['hadir', 'cuti', 'izin', 'sakit', 'alpha'] as $status) {
            $query->withCount([
                "absensi as {$status}_count" => function ($q) use ($status, $applyDateFilter) {
                    $q->where('status_kehadiran', $status);
                    $applyDateFilter($q);
                },
            ]);
        }

        return $query->first();
    }

    /**
     * Load semua karyawan sekaligus — dipertahankan untuk keperluan lain.
     */
    private function getRekapKaryawan($request)
    {
        $applyDateFilter = function ($q) use ($request) {
            if ($request->filled('date')) {
                $dates = explode(' : ', $request->date);
                if (count($dates) === 2) {
                    $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                    $end   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                    $q->whereBetween('tanggal', [$start, $end]);
                }
            }
        };

        $query = Karyawan::with(['user.roles', 'absensi' => function ($q) use ($applyDateFilter) {
            $applyDateFilter($q);
            $q->orderBy('tanggal', 'asc');
        }]);

        foreach (['hadir', 'cuti', 'izin', 'sakit', 'alpha'] as $status) {
            $query->withCount([
                "absensi as {$status}_count" => function ($q) use ($status, $applyDateFilter) {
                    $q->where('status_kehadiran', $status);
                    $applyDateFilter($q);
                },
            ]);
        }

        if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            $query->where('id', Auth::user()->karyawan->id);
        }

        return $query->get();
    }
}
