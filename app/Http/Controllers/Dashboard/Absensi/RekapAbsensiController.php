<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use PDF;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
                    $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                    $query = $query->whereBetween('tanggal', [$startDate, $endDate]);
                }
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
                ->addColumn('jam_masuk', function ($row) {
                    return $row->jam_masuk ?? '-';
                })
                ->addColumn('jam_pulang', function ($row) {
                    return $row->jam_pulang ?? '-';
                })
                ->addColumn('keterangan', function ($row) {
                    return $row->keterangan ?? '-';
                })
                ->rawColumns(['status'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('dashboard.absensis.rekap.index');
    }

    /**
     * Export ke PDF dengan data terfilter
     */
    public function exportPdf(Request $request)
    {
        try {
            \Log::info('RekapAbsensiController - exportPdf Started', [
                'request_date' => $request->date ?? 'null',
                'user_id' => Auth::user()->id ?? 'null'
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

            \Log::info('RekapAbsensiController - Exporting PDF', [
                'filename' => $filename
            ]);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - exportPdf Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
                'user_id' => Auth::user()->id ?? 'null'
            ]);

            $filename = 'rekap-absensi-' . now()->format('d-m-Y-H-i-s') . '.xlsx';

            \Log::info('RekapAbsensiController - Exporting Excel', [
                'filename' => $filename
            ]);

            return Excel::download(
                new RekapAbsensiExport($request),
                $filename
            );
        } catch (\Exception $e) {
            \Log::error('RekapAbsensiController - exportExcel Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal mengekspor Excel: ' . $e->getMessage());
        }
    }

    /**
     * Ambil rekap data karyawan dengan filter berdasarkan date range
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRekapKaryawan($request)
    {
        $query = Karyawan::with(['absensi' => function ($q) use ($request) {
            // Filter berdasarkan date range jika ada
            if ($request->filled('date')) {
                $dates = explode(' : ', $request->date);

                if (count($dates) === 2) {
                    $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                    $end = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                    $q->whereBetween('tanggal', [$start, $end]);
                }
            }

            // Sort by date ascending
            $q->orderBy('tanggal', 'asc');
        }]);

        // Load counts dengan filter date yang sama
        $query->withCount([
            'absensi as hadir_count' => function ($q) use ($request) {
                $q->where('status_kehadiran', 'hadir');
                if ($request->filled('date')) {
                    $dates = explode(' : ', $request->date);
                    if (count($dates) === 2) {
                        $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                        $end = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                }
            },
            'absensi as cuti_count' => function ($q) use ($request) {
                $q->where('status_kehadiran', 'cuti');
                if ($request->filled('date')) {
                    $dates = explode(' : ', $request->date);
                    if (count($dates) === 2) {
                        $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                        $end = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                }
            },
            'absensi as izin_count' => function ($q) use ($request) {
                $q->where('status_kehadiran', 'izin');
                if ($request->filled('date')) {
                    $dates = explode(' : ', $request->date);
                    if (count($dates) === 2) {
                        $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                        $end = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                }
            },
            'absensi as sakit_count' => function ($q) use ($request) {
                $q->where('status_kehadiran', 'sakit');
                if ($request->filled('date')) {
                    $dates = explode(' : ', $request->date);
                    if (count($dates) === 2) {
                        $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                        $end = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                }
            },
            'absensi as alpha_count' => function ($q) use ($request) {
                $q->where('status_kehadiran', 'alpha');
                if ($request->filled('date')) {
                    $dates = explode(' : ', $request->date);
                    if (count($dates) === 2) {
                        $start = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                        $end = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                }
            }
        ]);

        // Filter berdasarkan role - jika bukan admin, hanya tampilkan data sendiri
        if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            $query->where('id', Auth::user()->karyawan->id);
        }

        return $query->get();
    }
}
