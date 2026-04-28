<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Models\RekapAbsensiHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class RekapAbsensiHistoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = RekapAbsensiHistory::with('user')->latest();

            return DataTables::of($query)
                ->addColumn('user_name', fn ($row) => $row->user->name ?? '-')
                ->addColumn('periode', fn ($row) => $row->date_range_label)
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'published') {
                        return '<span class="badge bg-success"><i class="fas fa-check"></i> Published</span>';
                    }
                    return '<span class="badge bg-warning"><i class="fas fa-clock"></i> Draft</span>';
                })
                ->addColumn('file_count', fn ($row) => count($row->file_per_karyawan) . ' file')
                ->addColumn('aksi', function ($row) {
                    $buttons = '<div class="btn-group" role="group">';
                    $buttons .= '<a href="' . route('dashboard.rekap-absensi-history.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat detail">
                                    <i class="fas fa-eye"></i>
                                </a>';
                    if ($row->status === 'draft') {
                        $buttons .= '<button class="btn btn-sm btn-success btn-publish" data-id="' . $row->id . '" title="Publish ke karyawan">
                                    <i class="fas fa-paper-plane"></i> Publish
                                </button>';
                    } else {
                        $buttons .= '<button class="btn btn-sm btn-warning btn-unpublish" data-id="' . $row->id . '" title="Batalkan publish">
                                    <i class="fas fa-undo"></i> Unpublish
                                </button>';
                    }
                    $buttons .= '<a href="' . route('dashboard.rekap-absensi-history.download', $row->id) . '" class="btn btn-sm btn-primary" title="Download ZIP">
                                    <i class="fas fa-download"></i> ZIP
                                </a>';
                    $buttons .= '<button class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus history">
                                    <i class="fas fa-trash"></i>
                                </button>';
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['status_badge', 'aksi'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('dashboard.absensis.rekap.history.index');
    }

    public function show($id)
    {
        $rekap = RekapAbsensiHistory::with('user')->findOrFail($id);
        return view('dashboard.absensis.rekap.history.show', compact('rekap'));
    }

    public function publish($id)
    {
        try {
            $rekap = RekapAbsensiHistory::findOrFail($id);
            $rekap->update(['status' => 'published']);

            return response()->json([
                'success' => true,
                'message' => 'Rekap absensi berhasil dipublish ke karyawan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal publish: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unpublish($id)
    {
        try {
            $rekap = RekapAbsensiHistory::findOrFail($id);
            $rekap->update(['status' => 'draft']);

            return response()->json([
                'success' => true,
                'message' => 'Rekap absensi dikembalikan ke draft'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal unpublish: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        $rekap = RekapAbsensiHistory::findOrFail($id);

        if (!Storage::disk('public')->exists($rekap->zip_file_path)) {
            return redirect()->back()->with('error', 'File ZIP tidak ditemukan');
        }

        return Storage::disk('public')->download($rekap->zip_file_path, $rekap->zip_filename);
    }

    public function destroy($id)
    {
        try {
            $rekap = RekapAbsensiHistory::findOrFail($id);

            // Delete ZIP file
            if (Storage::disk('public')->exists($rekap->zip_file_path)) {
                Storage::disk('public')->delete($rekap->zip_file_path);
            }

            $rekap->delete();

            return response()->json([
                'success' => true,
                'message' => 'History rekap absensi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    // Karyawan: Lihat rekap yang published untuk diri sendiri
    public function karyawanIndex(Request $request)
    {
        $user = Auth::user();

        if (!$user->karyawan) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $karyawanId = $user->karyawan->id;

        if ($request->ajax()) {
            $query = RekapAbsensiHistory::where('status', 'published')
                ->latest();

            return DataTables::of($query)
                ->addColumn('periode', fn ($row) => $row->date_range_label)
                ->addColumn('dibuat_oleh', fn ($row) => $row->user->name ?? '-')
                ->addColumn('aksi', function ($row) use ($karyawanId) {
                    $filePerKaryawan = $row->file_per_karyawan ?? [];

                    if (isset($filePerKaryawan[$karyawanId])) {
                        return '<a href="' . route('dashboard.rekap-absensi-history.karyawan-download', [$row->id, $karyawanId]) . '" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download File Saya
                                </a>';
                    }

                    return '<span class="text-muted">Tidak ada file</span>';
                })
                ->rawColumns(['aksi'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('dashboard.absensis.rekap.history.karyawan_index');
    }

    // Karyawan: Download file individual
    public function karyawanDownload($historyId, $karyawanId)
    {
        $user = Auth::user();

        if (!$user->karyawan || $user->karyawan->id != $karyawanId) {
            abort(403, 'Anda tidak memiliki akses ke file ini');
        }

        $rekap = RekapAbsensiHistory::where('status', 'published')->findOrFail($historyId);
        $filePerKaryawan = $rekap->file_per_karyawan ?? [];

        if (!isset($filePerKaryawan[$karyawanId])) {
            return redirect()->back()->with('error', 'File tidak ditemukan');
        }

        $filename = $filePerKaryawan[$karyawanId];

        // Extract ZIP and get individual file
        $zipPath = Storage::disk('public')->path($rekap->zip_file_path);

        if (!file_exists($zipPath)) {
            return redirect()->back()->with('error', 'ZIP file tidak ditemukan');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $content = $zip->getFromName($filename);
            $zip->close();

            if ($content) {
                return response($content)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }
        }

        return redirect()->back()->with('error', 'File tidak ditemukan dalam ZIP');
    }
}
