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
        $query = Absensi::with(['karyawan', 'lokasiAbsensi', 'jamKerja'])
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
                ->addColumn('karyawan', function ($row) {
                    return $row->karyawan->name ?? '-';
                })
                ->addColumn('tanggal', function ($row) {
                    return \Carbon\Carbon::parse($row->tanggal)
                        ->locale('id')
                        ->translatedFormat('l, d F Y');
                })
                ->addColumn('status', function ($row) {
                    switch ($row->status_kehadiran) {
                        case 'hadir':
                            return '<span class="badge bg-success"><i class="fas fa-check"></i> Hadir</span>';
                        case 'cuti':
                            return '<span class="badge bg-warning"><i class="fas fa-calendar-check"></i> Cuti</span>';
                        case 'izin':
                            return '<span class="badge bg-info"><i class="fas fa-file-alt"></i> Izin</span>';
                        case 'sakit':
                            return '<span class="badge bg-danger"><i class="fas fa-hospital-alt"></i> Sakit</span>';
                        case 'alpha':
                            return '<span class="badge bg-secondary"><i class="fas fa-ban"></i> Alpha</span>';
                        default:
                            return '<span class="badge bg-secondary"><i class="fas fa-question"></i> Tidak Diketahui</span>';
                    }
                })
                ->addColumn('jam_masuk', fn ($row) =>
                    $row->jam_masuk ? \Carbon\Carbon::parse($row->jam_masuk)->format('H:i') : '-'
                )
                ->addColumn('jam_pulang', fn ($row) =>
                    $row->jam_pulang ? \Carbon\Carbon::parse($row->jam_pulang)->format('H:i') : '-'
                )
                ->addColumn('keterangan', function ($row) {
                    return $row->keterangan ?? '-';
                })
                ->addColumn('rp_masuk', function ($row) {
                    return 'Rp. ' . number_format(floatval($row->rp_masuk ?? 0), 0, '.', '');
                })
                ->addColumn('rp_pulang', function ($row) {
                    return 'Rp. ' . number_format(floatval($row->rp_pulang ?? 0), 0, '.', '');
                })
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
                ->rawColumns(['status', 'aksi'])
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
                return response()->json([
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                if ($absensi->karyawan_id !== Auth::user()->karyawan->id) {
                    return response()->json([
                        'message' => 'Anda tidak memiliki akses untuk melihat data ini'
                    ], 403);
                }
            }

            return response()->json([
                'absensi' => $absensi
            ]);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - show Error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Gagal memuat data absensi'
            ], 500);
        }
    }

    /**
     * Update data absensi
     */
    public function update(Request $request, $id)
    {
        try {
            $absensi = Absensi::find($id);

            if (!$absensi) {
                return response()->json([
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                if ($absensi->karyawan_id !== Auth::user()->karyawan->id) {
                    return response()->json([
                        'message' => 'Anda tidak memiliki akses untuk mengubah data ini'
                    ], 403);
                }
            }

            // FIX: Accept both H:i (HH:MM) and H:i:s (HH:MM:SS) formats
            // The frontend sends HH:MM:00 after stripping & re-appending seconds,
            // so we validate with date_format:H:i:s as primary, H:i as fallback.
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

            // FIX: Normalize time values to H:i:s before saving to the DB.
            // If front-end sends "08:30" we store "08:30:00".
            // If front-end sends "08:30:00" we store "08:30:00" as-is.
            $normalizeTime = function (?string $time): ?string {
                if (empty($time)) return null;
                // Already has seconds
                if (strlen($time) === 8) return $time;
                // Only HH:MM — append seconds
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
                'updated_data' => $validated
            ]);

            return response()->json([
                'message' => 'Data absensi berhasil diperbarui',
                'absensi' => $absensi
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - Update Error', [
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'absensi_id' => $id
            ]);

            return response()->json([
                'message' => 'Gagal mengupdate data absensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete data absensi
     */
    public function destroy($id)
    {
        try {
            $absensi = Absensi::find($id);

            if (!$absensi) {
                return response()->json([
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda tidak memiliki akses untuk menghapus data ini'
                ], 403);
            }

            $absensi->delete();

            \Log::info('RekapAbsensiController - Delete Success', [
                'absensi_id' => $id,
                'user_id'    => Auth::id()
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data absensi berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - Delete Error', [
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'absensi_id' => $id
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus data absensi'
            ], 500);
        }
    }

    /**
     * Export ke PDF dengan data terfilter
     */
    public function exportPdf(Request $request)
    {
        try {
            \Log::info('RekapAbsensiController - exportPdf Started', [
                'request_date' => $request->date ?? 'null',
                'user_id'      => Auth::user()->id ?? 'null'
            ]);

            $karyawans = $this->getRekapKaryawan($request);

            if ($karyawans->isEmpty()) {
                \Log::warning('RekapAbsensiController - No data to export');
                return redirect()->back()->with('warning', 'Tidak ada data absensi untuk diekspor.');
            }

            $dateRange = $request->filled('date') ? $request->date : 'Semua Tanggal';

            $pdf = PDF::loadView('dashboard.absensis.rekap.pdf', [
                'karyawans' => $karyawans,
                'dateRange' => $dateRange
            ])->setPaper('a4', 'landscape');

            $filename = 'rekap-absensi-' . now()->format('d-m-Y-H-i-s') . '.pdf';

            \Log::info('RekapAbsensiController - Exporting PDF', ['filename' => $filename]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - exportPdf Error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export ke Excel dengan data terfilter
     */
    public function exportExcel(Request $request)
    {
        try {
            \Log::info('RekapAbsensiController - exportExcel Started', [
                'request_date' => $request->date ?? 'null',
                'user_id'      => Auth::user()->id ?? 'null'
            ]);

            $filename = 'rekap-absensi-' . now()->format('d-m-Y-H-i-s') . '.xlsx';

            \Log::info('RekapAbsensiController - Exporting Excel', ['filename' => $filename]);

            return Excel::download(new RekapAbsensiExport($request), $filename);

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - exportExcel Error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal mengekspor Excel: ' . $e->getMessage());
        }
    }

    /**
     * Ambil rekap data karyawan dengan filter berdasarkan date range
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

        $query = Karyawan::with(['absensi' => function ($q) use ($applyDateFilter) {
            $applyDateFilter($q);
            $q->orderBy('tanggal', 'asc');
        }]);

        foreach (['hadir', 'cuti', 'izin', 'sakit', 'alpha'] as $status) {
            $query->withCount([
                "absensi as {$status}_count" => function ($q) use ($status, $applyDateFilter) {
                    $q->where('status_kehadiran', $status);
                    $applyDateFilter($q);
                }
            ]);
        }

        if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            $query->where('id', Auth::user()->karyawan->id);
        }

        return $query->get();
    }
}