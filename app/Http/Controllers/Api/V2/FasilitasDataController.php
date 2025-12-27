<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FasilitasDataController extends Controller
{
    /**
     * Ambil semua data fasilitas beserta kelengkapan
     *
     * GET /api/v2/fasilitas
     */
    public function fasilitasData()
    {
        try {
            $fasilitas = Fasilitas::with('kelengkapan')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($fasilitas && $fasilitas->count() > 0) {
                return $this->success($fasilitas, 'OK');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data fasilitas: ' . $e->getMessage());
        }
    }

    /**
     * Ambil detail fasilitas berdasarkan ID
     *
     * GET /api/v2/fasilitas/{id}
     */
    public function show($id)
    {
        try {
            $fasilitas = Fasilitas::with('kelengkapan')->find($id);

            if ($fasilitas) {
                return $this->success($fasilitas, 'OK');
            }

            return $this->success([], 'Data Tidak Di Temukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil detail fasilitas: ' . $e->getMessage());
        }
    }
}
