<?php

namespace App\Services;

use App\Models\Rapot;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\Storage;

/**
 * Service untuk menangani logika bisnis rapot
 * Fixed: SQL GROUP BY ONLY_FULL_GROUP_BY issue
 */
class RapotService
{
    /**
     * Level 1: Ambil semua tahun ajaran yang unik
     * Query langsung ke database tanpa relasi
     *
     * @return array
     */
    public function getTahunAjaran()
    {
        return Rapot::distinct()
            ->select('tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->map(function($tahun) {
                return [
                    'tahun' => $tahun,
                    'label' => "Tahun Ajaran {$tahun}"
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Level 2: Ambil daftar siswa dengan rapot di tahun tertentu
     * FIXED: Menggunakan DISTINCT ON atau proper GROUP BY dengan aggregates
     * Query dengan relasi siswa dan kelas
     * Support filter berdasarkan kelas
     * Support pencarian berdasarkan name, NIS, atau kelas
     *
     * @param string $tahun
     * @param string|null $kelas
     * @param string|null $search
     * @return array
     */
    public function getSiswaByTahun($tahun, $kelas = null, $search = null)
    {
        // OPSI 1: Menggunakan subquery untuk get distinct siswa
        $query = Rapot::with(['siswa', 'kelas'])
            ->where('tahun', $tahun)
            ->whereIn('id', function($q) use ($tahun) {
                $q->selectRaw('MAX(id)')
                  ->from('rapot')
                  ->where('tahun', $tahun)
                  ->groupBy('siswa_id');
            });

        // Filter kelas
        if($kelas) {
            $query->whereHas('kelas', function($q) use ($kelas) {
                $q->where('name', $kelas);
            });
        }

        // Filter pencarian
        if($search) {
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('nisn', 'LIKE', "%{$search}%");
            });
        }

        $rapots = $query->get();

        // Transform data
        $siswaList = [];
        $processedSiswa = [];

        foreach($rapots as $rapot) {
            if(in_array($rapot->siswa_id, $processedSiswa)) {
                continue;
            }

            $processedSiswa[] = $rapot->siswa_id;

            $siswaList[] = [
                'id' => $rapot->siswa_id,
                'name' => $rapot->siswa->name ?? 'N/A',
                'nis' => $rapot->siswa->nisn ?? 'N/A',
                'class' => $rapot->kelas->name ?? 'N/A',
                'semester' => $rapot->kategori ?? 'N/A',
                'rapot_count' => Rapot::where('siswa_id', $rapot->siswa_id)
                    ->where('tahun', $tahun)
                    ->count()
            ];
        }

        return $siswaList;
    }

    /**
     * ALTERNATIVE: Lebih optimal menggunakan raw query atau subquery yang cleaner
     * Gunakan ini jika method di atas terlalu lambat
     *
     * @param string $tahun
     * @param string|null $kelas
     * @param string|null $search
     * @return array
     */
    public function getSiswaByTahunOptimized($tahun, $kelas = null, $search = null)
    {
        $query = "
            SELECT DISTINCT
                r.siswa_id,
                s.name,
                s.nisn,
                k.name as class,
                r.kategori as semester,
                COUNT(r.id) as rapot_count
            FROM rapot r
            INNER JOIN siswas s ON r.siswa_id = s.id
            INNER JOIN kelas k ON r.kelas_id = k.id
            WHERE r.tahun = ?
        ";

        $bindings = [$tahun];

        if($kelas) {
            $query .= " AND k.name = ?";
            $bindings[] = $kelas;
        }

        if($search) {
            $query .= " AND (s.name LIKE ? OR s.nisn LIKE ?)";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $query .= " GROUP BY r.siswa_id
                   ORDER BY s.name ASC";

        return \DB::select($query, $bindings);
    }

    /**
     * Level 3: Ambil detail semua rapot untuk seorang siswa
     * Mengembalikan daftar rapot dengan status dan metadata file
     *
     * @param string $siswaId
     * @return array
     */
    public function getDetailRapotSiswa($siswaId)
    {
        $rapots = Rapot::where('siswa_id', $siswaId)
            ->with(['siswa', 'kelas'])
            ->orderBy('tahun', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if($rapots->isEmpty()) {
            return [];
        }

        return $rapots->map(function($rapot) {
            return [
                'id' => $rapot->id,
                'siswa_id' => $rapot->siswa_id,
                'siswa_name' => $rapot->siswa->name ?? 'N/A',
                'kelas' => $rapot->kelas->name ?? 'N/A',
                'tahun' => $rapot->tahun,
                'semester' => $rapot->kategori ?? 'N/A',
                'periode' => $this->formatPeriode($rapot->created_at),
                'catatan' => $rapot->catatan ?? '',
                'status' => $this->getStatusRapot($rapot->file_rapot),
                'file' => $rapot->file_rapot,
                'file_size' => $this->getFileSize($rapot->file_rapot),
                'created_at' => $rapot->created_at->format('d-m-Y H:i'),
            ];
        })->toArray();
    }

    /**
     * Download file rapot
     * Validasi keamanan dan log download
     *
     * @param string $siswaId
     * @param string $rapotId
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function downloadRapotFile($siswaId, $rapotId)
    {
        $rapot = Rapot::where('id', $rapotId)
            ->where('siswa_id', $siswaId)
            ->with(['siswa', 'kelas'])
            ->firstOrFail();

        // Validasi file ada
        if(!$rapot->file_rapot || !Storage::exists($rapot->file_rapot)) {
            throw new \Exception('File rapot tidak ditemukan');
        }

        // Log download
        $this->logDownload($siswaId, $rapotId);

        // Return file untuk download
        return response()->download(
            Storage::path($rapot->file_rapot),
            "Rapot_{$rapot->siswa->name}_{$rapot->tahun}.pdf"
        );
    }

    /**
     * Get class count statistics
     *
     * @param string $tahun
     * @return array
     */
    public function getClassCount($tahun)
    {
        return Rapot::where('tahun', $tahun)
            ->with('kelas')
            ->selectRaw('kelas_id, k.name, COUNT(DISTINCT siswa_id) as count')
            ->join('kelas as k', 'rapot.kelas_id', '=', 'k.id')
            ->groupBy('kelas_id', 'k.name')
            ->orderBy('k.name')
            ->get()
            ->map(function($item) {
                return [
                    'class' => $item->kelas->name ?? 'N/A',
                    'count' => $item->count
                ];
            })
            ->toArray();
    }

    /**
     * Helper: Dapatkan status rapot berdasarkan file
     *
     * @param string|null $filename
     * @return string
     */
    private function getStatusRapot($filename)
    {
        if(empty($filename)) {
            return 'Pending';
        }

        return Storage::exists($filename) ? 'Tersedia' : 'Tidak Tersedia';
    }

    /**
     * Helper: Format periode rapot
     *
     * @param \Carbon\Carbon $date
     * @return string
     */
    private function formatPeriode($date)
    {
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $month = $monthNames[$date->month] ?? 'N/A';
        return "{$month} {$date->year}";
    }

    /**
     * Helper: Dapatkan ukuran file
     *
     * @param string|null $filename
     * @return string
     */
    private function getFileSize($filename)
    {
        if(empty($filename) || !Storage::exists($filename)) {
            return '0 KB';
        }

        $bytes = Storage::size($filename);
        return $this->formatBytes($bytes);
    }

    /**
     * Helper: Format bytes menjadi human readable
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Helper: Log download rapot
     *
     * @param string $siswaId
     * @param string $rapotId
     * @return void
     */
    public function logDownload($siswaId, $rapotId)
    {
        // Implementasi logging sesuai kebutuhan
        // Bisa disimpan di database atau file log
        \Log::info('Rapot download', [
            'siswa_id' => $siswaId,
            'rapot_id' => $rapotId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }
}
