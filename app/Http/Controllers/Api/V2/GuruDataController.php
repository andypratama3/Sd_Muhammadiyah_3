<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Guru;
use App\Models\Pelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class GuruDataController extends Controller
{
    /**
     * Ambil daftar pelajaran
     *
     * GET /api/guru/pelajaran
     */
    public function pelajaran()
    {
        try {
            $pelajaran = Pelajaran::orderBy('name', 'asc')->get();

            if ($pelajaran->count() > 0) {
                return $this->success($pelajaran, 'OK');
            }
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->success([],'Data Tidak Di Temukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data pelajaran: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Ambil semua guru dengan filter & search
     *
     * GET /api/guru
     * Query params:
     * - search
     * - pelajaran
     */
    public function listGuru(Request $request)
    {
        try {
            $search    = $request->input('search');
            $pelajaran = $request->input('pelajaran');

            $query = Guru::with([
                    'karyawan',
                    'pelajarans'
                ])
                ->select(
                    'id', // WAJIB ada
                    'name',
                    'karyawan_id',
                    'description',
                    'foto',
                    'slug',
                    'lulusan',
                    'updated_at'
                );

            // Search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('lulusan', 'LIKE', "%{$search}%");
                });
            }

            // Filter pelajaran
            if ($pelajaran) {
                $query->whereHas('pelajarans', fn ($q) =>
                    $q->where('pelajarans.slug', $pelajaran)
                );
            }

            $data = $query
                ->orderBy('name', 'asc')
                ->get()
                ->each(function ($guru) {

                    // ❌ hide id guru
                    $guru->makeHidden(['id', 'karyawan_id']);

                    // ❌ hide id karyawan
                    if ($guru->karyawan) {
                        $guru->karyawan->makeHidden([
                            'id',
                            'user_id',
                            'created_at',
                            'updated_at',
                            'deleted_at'
                        ]);
                    }

                    // ❌ hide id pelajarans + pivot
                    $guru->pelajarans->each(function ($pelajaran) {
                        $pelajaran->makeHidden([
                            'id',
                            'created_at',
                            'updated_at',
                            'pivot'
                        ]);
                    });
                });

            return $this->success($data, 'OK');

        } catch (\Exception $e) {
            return $this->serverError(
                'Gagal mengambil guru: ' . $e->getMessage(),
                500
            );
        }
    }


    /**
     * Ambil detail guru berdasarkan slug
     *
     * GET /api/guru/{slug}
     */
    public function show($slug)
    {
        try {
            $guru = Guru::where('slug', $slug)
                ->with(['karyawan', 'pelajarans'])
                ->select('id','name', 'description', 'slug', 'lulusan', 'foto', 'karyawan_id', 'created_at', 'updated_at')
                ->firstOrFail();

            $data = [
                'name' => $guru->name,
                'description' => $guru->description,
                'slug' => $guru->slug,
                'lulusan' => $guru->lulusan,
                'foto' => $guru->foto,
                'karyawan_id' => $guru->karyawan_id,
                'karyawan' => $guru->karyawan,
                'pelajarans' => $guru->pelajarans->map(function($pelajaran) {
                    return [
                        'name' => $pelajaran->name,
                        'slug' => $pelajaran->slug
                    ];
                })->toArray(),
                'created_at' => $guru->created_at,
                'updated_at' => $guru->updated_at,
            ];

            return $this->success(['data' => $data], 'OK');
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->success([], 'Data Tidak Di Temukan');
        } catch (\Exception $e) {
            return $this->error('Gagal mengambil detail guru: ', $e->getMessage(), 500);
        }
    }

    /**
     * Hitung jumlah guru berdasarkan pelajaran
     *
     * GET /api/guru/count-by-pelajaran
     */
    public function countByPelajaran()
    {
        try {
            $data = Pelajaran::select('name', 'slug')
                ->withCount('gurus')
                ->orderByDesc('gurus_count')
                ->get();

            if ($data->count() > 0) {
                return $this->success($data, 'OK');
            }

            return $this->success([], 'OK');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil count guru: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Statistik guru
     *
     * GET /api/guru/statistics
     */
    public function statistics()
    {
        try {
            $statistics = [
                'total_guru' => Guru::count(),
                'total_pelajaran' => Pelajaran::count(),
                'by_pelajaran' => Pelajaran::select('name', 'slug')
                    ->withCount('gurus')
                    ->orderByDesc('gurus_count')
                    ->get(),
            ];

            return $this->success($statistics, 'Statistik guru berhasil diambil');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil statistik guru: ' . $e->getMessage(), 500);
        }
    }
}
