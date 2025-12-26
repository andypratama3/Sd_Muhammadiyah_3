<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Kelas;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class JadwalDataController extends Controller
{
    /**
     * Ambil daftar tahun ajaran
     * GET /api/v2/jadwal/tahun-ajaran
     */
    public function tahunAjaran()
    {
        try {
            $jadwal = Jadwal::groupBy('tahun_ajaran')
                ->pluck('tahun_ajaran');

            return $this->success($jadwal ?? [], 'OK');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil tahun ajaran');
        }
    }

    /**
     * Ambil daftar kelas (kecuali Lulus)
     * GET /api/v2/jadwal/kelas
     */
    public function kelas()
    {
        try {
            $kelas = Kelas::where('name', '!=', 'Lulus')
                ->orderBy('name', 'asc')
                ->get();

            return $this->success($kelas ?? [], 'OK');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data kelas');
        }
    }

    /**
     * Ambil kategori kelas berdasarkan kelas_id
     * GET /api/v2/jadwal/category-kelas
     */
    public function categoryKelas(Request $request)
    {
        try {
            $kelasId = $request->kelas_id;

            $kelas = Kelas::find($kelasId);

            if (! $kelas) {
                return $this->notFound('Kelas tidak ditemukan');
            }

            if (! $kelas->category_kelas) {
                return $this->success([], 'OK');
            }

            $decoded = json_decode($kelas->category_kelas, true);

            if (! is_array($decoded)) {
                return $this->success([], 'OK');
            }

            $categories = collect($decoded)
                ->filter(fn ($v) => trim($v) !== '')
                ->values();

            return $this->success($categories, 'OK');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil kategori kelas');
        }
    }

    /**
     * Ambil jadwal berdasarkan filter
     * GET /api/v2/jadwal/list-jadwal
     */
    public function list_jadwal(Request $request)
    {
        try {
            $request->validate([
                'tahun_ajaran' => 'required|string',
                'kelas_id' => 'required|string',
                'category_kelas' => 'required|string',
            ]);

            $category = strtolower($request->category_kelas);

            $jadwal = Jadwal::with(['jadwal_details', 'kelas'])
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('kelas_id', $request->kelas_id)
                ->where('category_kelas', $category)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'tahun_ajaran' => $item->tahun_ajaran ?? '-',
                        'kelas' => $item->kelas->name ?? '-',
                        'category_kelas' => $item->category_kelas ?? '-',
                        'jadwal' => $item->jadwal ?? '-',
                        'jadwal_details' => $item->jadwal_details->map(function ($detail) {
                            return [
                                'id' => $detail->id,
                                'kelas_id' => $detail->kelas->name ?? '-',
                                'hari' => $detail->hari ?? '-',
                                'time_start' => $detail->time_start
                                    ? date('H:i', strtotime($detail->time_start))
                                    : '-',
                                'time_end' => $detail->time_end
                                    ? date('H:i', strtotime($detail->time_end))
                                    : '-',
                                'jadwal' => $detail->jadwal->tahun_ajaran ?? '-',
                                'pelajaran_id' => $detail->pelajaran->name ?? '-',
                                'guru_id' => $detail->guru->name ?? '-',
                                'color' => $detail->color ?? '-',
                            ];
                        }),
                    ];
                });

            // DATA KOSONG = BUKAN ERROR
            return $this->success($jadwal ?? [], 'OK');
        } catch (ValidationException $e) {
            return $this->validationError('Validasi gagal', $e->errors());
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil jadwal');
        }
    }
}
