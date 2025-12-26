<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Gallery;
use App\Models\Prestasi;
use App\Models\Fasilitas;
use App\Models\Cooperation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LandingController extends Controller
{
    /**
     * Hitung data utama landing page
     *
     * GET /api/v2/landing/count
     */
    public function count()
    {
        try {
            $guru = Guru::count();
            $fasilitas = Fasilitas::count();
            $prestasis_siswa = Prestasi::where('status', '1')->count();
            $prestasis_sekolah = Prestasi::where('status', '2')->count();

            $siswas = Siswa::whereHas('kelas', function ($q) {
                $q->where('name', '!=', 'Lulus');
            })->count();

            return $this->success([
                'siswa' => $siswas,
                'guru' => $guru,
                'fasilitas' => $fasilitas,
                'prestasis_siswa' => $prestasis_siswa,
                'prestasis_sekolah' => $prestasis_sekolah,
            ], 'OK');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data count landing: ' . $e->getMessage());
        }
    }

    /**
     * Ambil gallery activity untuk landing page
     *
     * GET /api/v2/landing/gallery-activity
     */
    public function gallery_activity()
    {
        try {
            $gallery = Gallery::orderBy('created_at', 'asc')
                ->take(8)
                ->get();

            if ($gallery && $gallery->count() > 0) {
                return $this->success($gallery, 'Berhasil Menerima Data');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil gallery activity: ' . $e->getMessage());
        }
    }

    /**
     * Ambil data dukungan / cooperation
     *
     * GET /api/v2/landing/dukungan
     */
    public function dukungan()
    {
        try {
            $cooperation = Cooperation::orderBy('order', 'asc')->get();

            if ($cooperation && $cooperation->count() > 0) {
                return $this->success($cooperation, 'Berhasil Menerima Data');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data dukungan: ' . $e->getMessage());
        }
    }
}
