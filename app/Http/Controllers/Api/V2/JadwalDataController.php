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
     *
     * GET /api/v2/jadwal/tahun-ajaran
     */
    public function tahunAjaran()
    {
        try {
            $jadwal = Jadwal::groupBy('tahun_ajaran')->pluck('tahun_ajaran');

            if ($jadwal && $jadwal->count() > 0) {
                return $this->success($jadwal, 'OK');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil tahun ajaran: ' . $e->getMessage());
        }
    }

    /**
     * Ambil daftar kelas (kecuali Lulus)
     *
     * GET /api/v2/jadwal/kelas
     */
    public function kelas()
    {
        try {
            $kelas = Kelas::where('name', '!=', 'Lulus')
                ->orderBy('name', 'asc')
                ->get();

            if ($kelas && $kelas->count() > 0) {
                return $this->success($kelas, 'OK');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data kelas: ' . $e->getMessage());
        }
    }

    /**
     * Ambil kategori kelas berdasarkan kelas_id
     *
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

            $raw = $kelas->category_kelas;

            if (! $raw) {
                return $this->success([], 'OK');
            }

            $decoded = json_decode($raw, true);

            if (! is_array($decoded)) {
                return $this->success([], 'OK');
            }

            $categories = collect($decoded)
                ->values()
                ->filter(fn ($v) => trim($v) !== '')
                ->values();

            return $this->success($categories, 'OK');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil kategori kelas: ' . $e->getMessage());
        }
    }

    /**
     * Ambil jadwal berdasarkan filter
     *
     * GET /api/v2/jadwal
     * Query params:
     * - tahun_ajaran
     * - kelas_id
     * - category_kelas
     */
    public function list_jadwal(Request $request)
    {
        try {
            $request->validate([
                'tahun_ajaran' => 'required|string',
                'kelas_id' => 'required|string',
                'category_kelas' => 'required|string',
            ]);

            $request->merge([
                'category_kelas' => strtolower($request->category_kelas)
            ]);

            $jadwal = Jadwal::with(['jadwal_details', 'kelas'])
                ->where('tahun_ajaran', $request->tahun_ajaran)
                ->where('kelas_id', $request->kelas_id)
                ->where('category_kelas', $request->category_kelas)
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

            if ($jadwal && $jadwal->count() > 0) {
                return $this->success($jadwal, 'OK');
            }

            return $this->error('Data tidak ditemukan');
        } catch (ValidationException $e) {
            return $this->validationError('Validasi gagal', $e->errors());
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil jadwal: ' . $e->getMessage());
        }
    }
}
