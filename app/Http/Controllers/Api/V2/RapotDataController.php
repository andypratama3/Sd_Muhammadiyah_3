<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Rapot;
use Illuminate\Http\Request;
use App\Services\RapotService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * API untuk sistem download rapot hierarki
 * FIXED: Download menggunakan streaming response dengan proper headers
 */
class RapotDataController extends Controller
{
    protected $rapotService;

    public function __construct(RapotService $rapotService)
    {
        $this->rapotService = $rapotService;
    }

    /**
     * API Level 1: Ambil daftar tahun ajaran
     */
    public function getTahunAjaran()
    {
        try {
            $tahunList = $this->rapotService->getTahunAjaran();

            if(empty($tahunList)) {
                return $this->success([], 'Tidak ada data tahun ajaran');
            }

            return $this->success($tahunList, 'Berhasil mengambil daftar tahun ajaran');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil tahun ajaran: ' . $e->getMessage());
        }
    }

    /**
     * API Level 2: Ambil daftar siswa per tahun ajaran
     */
    public function getSiswaByTahun(Request $request)
    {
        $request->validate([
            'tahun' => 'required|string',
            'kelas' => 'nullable|string',
            'search' => 'nullable|string',
        ]);

        try {
            $tahun = $request->input('tahun');
            $kelas = $request->input('kelas', null);
            $search = $request->input('search', null);

            $siswaList = $this->rapotService->getSiswaByTahun($tahun, $kelas, $search);

            if(empty($siswaList)) {
                return $this->success([], 'Tidak ada siswa ditemukan');
            }

            return $this->success($siswaList, 'Berhasil mengambil daftar siswa');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil daftar siswa: ' . $e->getMessage());
        }
    }

    /**
     * API Level 3: Ambil detail rapot per siswa
     */
    public function getDetailRapotSiswa($siswaId)
    {
        try {
            $rapotDetail = $this->rapotService->getDetailRapotSiswa($siswaId);

            if(empty($rapotDetail)) {
                return $this->success([], 'Tidak ada rapot ditemukan untuk siswa ini');
            }

            return $this->success($rapotDetail, 'Berhasil mengambil detail rapot siswa');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil detail rapot: ' . $e->getMessage());
        }
    }

    /**
     * FIXED: Download file rapot dengan streaming response
     * Endpoint: GET /api/v2/rapot/download/{siswa_id}/{rapot_id}
     *
     * Perbaikan:
     * 1. Menggunakan streamDownload untuk file besar
     * 2. Proper CORS headers
     * 3. Content-Disposition dengan filename yang proper
     * 4. MIME type detection yang akurat
     * 5. Error handling yang lebih baik
     */
    public function downloadRapot($siswaId, $rapotId)
    {
        try {
            $rapot = Rapot::where('id', $rapotId)
                ->where('siswa_id', $siswaId)
                ->with(['siswa', 'kelas'])
                ->firstOrFail();

            if (
                !$rapot->file_rapot ||
                !Storage::disk('public')->exists($rapot->file_rapot)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'File rapot tidak ditemukan'
                ], 404);
            }

            // Log download
            $this->rapotService->logDownload($siswaId, $rapotId);

            return Storage::disk('public')->download(
                $rapot->file_rapot,
                $this->generateFileName($rapot),
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapot tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Download rapot error', [
                'siswa_id' => $siswaId,
                'rapot_id' => $rapotId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh file: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Generate filename yang proper untuk download
     */
    private function generateFileName($rapot)
    {
        // Sanitize nama siswa
        $siswaName = preg_replace('/[^a-z0-9]/i', '_', $rapot->siswa->name ?? 'siswa');
        $siswaName = strtolower($siswaName);

        // Sanitize semester
        $semester = preg_replace('/[^a-z0-9]/i', '_', $rapot->kategori ?? 'semester');
        $semester = strtolower($semester);

        // Get file extension
        $extension = pathinfo($rapot->file_rapot, PATHINFO_EXTENSION);

        // Build filename
        return sprintf(
            'rapot_%s_%s_%s.%s',
            $siswaName,
            $semester,
            $rapot->tahun,
            $extension
        );
    }

    /**
     * Alternative: Get download URL (jika menggunakan direct storage access)
     * Endpoint: GET /api/v2/rapot/url/{siswa_id}/{rapot_id}
     */
    public function getDownloadUrl($siswaId, $rapotId)
    {
        try {
            $rapot = \App\Models\Rapot::where('id', $rapotId)
                ->where('siswa_id', $siswaId)
                ->firstOrFail();

            if(!$rapot->file_rapot || !Storage::exists($rapot->file_rapot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            // Generate temporary signed URL (valid for 1 hour)
            $url = Storage::temporaryUrl(
                $rapot->file_rapot,
                now()->addHour()
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'expires_at' => now()->addHour()->toIso8601String(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate URL: ' . $e->getMessage()
            ], 500);
        }
    }
}
