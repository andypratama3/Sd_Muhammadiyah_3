<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BeritaDataController extends Controller
{
    /**
     * Ambil daftar berita (limit)
     *
     * GET /api/v2/berita/list
     */
    public function list_berita(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 500);

            // Safety limit
            if ($limit > 1000) {
                $limit = 1000;
            }

            $data = Berita::query()
                ->whereNull('deleted_at')
                ->select('judul', 'desc', 'foto', 'slug', 'category', 'updated_at')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            return $this->success($data ?? [], 'OK');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil list berita: ' . $e->getMessage());
        }
    }

    /**
     * Hitung jumlah berita berdasarkan kategori
     *
     * GET /api/v2/berita/count-by-category
     */
    public function countData()
    {
        try {
            $data = Berita::whereNull('deleted_at')
                ->groupBy('category')
                ->select('category', DB::raw('count(*) as total'))
                ->orderByDesc('total')
                ->get();

            if ($data->count() > 0) {
                return $this->success($data, 'OK');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil count berita: ' . $e->getMessage());
        }
    }

    /**
     * Ambil berita populer berdasarkan views
     *
     * GET /api/v2/berita/popular
     */
    public function beritaPopuler()
    {
        try {
            $data = Berita::whereNull('deleted_at')
                ->orderByDesc('views')
                ->take(10)
                ->get();

            if ($data->count() > 0) {
                return $this->success($data, 'OK');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil berita populer: ' . $e->getMessage());
        }
    }

    /**
     * Ambil semua berita dengan filter + pagination
     *
     * GET /api/v2/berita
     * Query params:
     * - search
     * - category
     * - per_page
     * - page
     */
    public function list(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'category' => 'nullable|string',
            'per_page' => 'nullable|integer',
        ]);

        
        try {
            $search   = $request->input('search');
            $category = $request->input('category');
            $perPage  = $request->input('per_page', 10);

            $query = Berita::whereNull('deleted_at');

            // Search
            if ($search && trim($search) !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('judul', 'LIKE', "%{$search}%")
                      ->orWhere('desc', 'LIKE', "%{$search}%");
                });
            }

            // Category filter (ignore semua/all)
            if ($category && !in_array(strtolower($category), ['semua', 'all'])) {
                $query->where('category', strtolower($category));
            }

            $query->orderByDesc('created_at');

            $data = $query->paginate($perPage);

            return $this->paginated($data, 'OK');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil berita: ' . $e->getMessage());
        }
    }

    /**
     * Ambil detail berita + increment views
     *
     * GET /api/v2/berita/{slug}
     */
    public function show($slug)
    {
        try {
            $berita = Berita::where('slug', $slug)
                ->whereNull('deleted_at')
                ->firstOrFail();

            // Increment views
            $berita->increment('views');

            $data = [
                'id' => $berita->id,
                'judul' => $berita->judul,
                'desc' => $berita->desc,
                'slug' => $berita->slug,
                'category' => $berita->category,
                'foto' => $berita->foto,
                'views' => $berita->views ?? 0,
                'created_at' => $berita->created_at,
                'updated_at' => $berita->updated_at,
            ];

            return $this->success(['data' => $data], 'OK');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->success([], 'Data Tidak Di Temukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil detail berita: ' . $e->getMessage());
        }
    }

    /**
     * Statistik berita
     *
     * GET /api/v2/berita/statistics
     */
    public function statistics()
    {
        try {
            $statistics = [
                'total_berita' => Berita::whereNull('deleted_at')->count(),
                'total_views' => Berita::whereNull('deleted_at')->sum('views'),
                'by_category' => Berita::whereNull('deleted_at')
                    ->groupBy('category')
                    ->select('category', DB::raw('count(*) as total'))
                    ->orderByDesc('total')
                    ->get(),
            ];

            return $this->success($statistics, 'Statistik berita berhasil diambil');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil statistik berita: ' . $e->getMessage());
        }
    }
}
