<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BeritaDataController extends Controller
{
    /**
     * Get count of berita grouped by category
     * Endpoint: GET /api/v2/berita-count-data
     */
    public function countData()
    {
        try {
            $berita = Berita::where('deleted_at', null)
                ->groupBy('category')
                ->select('category', DB::raw('count(*) as total'))
                ->orderBy('total', 'desc')
                ->get();

            if($berita && count($berita) > 0){
                return $this->success($berita, "OK");
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get popular berita ordered by views
     * Endpoint: GET /api/v2/berita-popular
     */
    public function beritaPopuler()
    {
        try {
            $berita = Berita::where('deleted_at', null)
                ->orderBy('views', 'desc')
                ->take(10)
                ->get();

            if($berita && count($berita) > 0){
                return $this->success($berita, "OK");
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get list of berita dengan filter kategori, search, dan pagination
     * Endpoint: GET /api/v2/berita?page=1&category=pengumuman&search=kolam
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search', null);
            $category = $request->input('category', null);
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            // Start building query
            $query = Berita::where('deleted_at', null);

            // Apply search filter
            if ($search && !empty(trim($search))) {
                $searchTerm = trim($search);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('judul', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('desc', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply category filter
            // PENTING: hanya filter jika category ada dan bukan 'semua'
            if ($category && !empty(trim($category)) && $category !== 'semua') {
                $categoryTerm = strtolower(trim($category));
                $query->where('category', $categoryTerm);
            }

            // Order by created_at descending
            $query->orderBy('created_at', 'desc');

            // Get paginated data
            $data = $query->paginate($perPage);

            // Return response dengan format yang benar
            return $this->paginated($data, "OK");
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get single berita by slug and increment views
     * Endpoint: GET /api/v2/berita/{slug}
     */
    public function show($slug)
    {
        try {
            $data = Berita::where('slug', $slug)
                ->where('deleted_at', null)
                ->firstOrFail();

            // Increment views count
            $data->increment('views');

            return $this->success([
                "data" => [
                    'id' => $data->id,
                    'judul' => $data->judul,
                    'desc' => $data->desc,
                    'views' => $data->views,
                    'slug' => $data->slug,
                    'category' => $data->category,
                    'foto' => $data->foto,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                ]
            ], "OK");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Berita tidak ditemukan', 404);
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Search berita (deprecated - gunakan list dengan parameter search)
     * Endpoint: GET /api/v2/berita/search?search=kolam&category=pengumuman
     */
    public function search(Request $request)
    {
        try {
            $search = $request->input('search');
            $category = $request->input('category', null);
            $perPage = $request->input('per_page', 10);

            if (!$search || empty(trim($search))) {
                return $this->error('Search query tidak boleh kosong');
            }

            $query = Berita::where('deleted_at', null)
                ->where(function($q) use ($search) {
                    $q->where('judul', 'LIKE', "%{$search}%")
                      ->orWhere('desc', 'LIKE', "%{$search}%");
                });

            // Filter category jika ada
            if ($category && !empty(trim($category)) && $category !== 'semua') {
                $query->where('category', strtolower(trim($category)));
            }

            $data = $query->orderBy('created_at', 'desc')->paginate($perPage);

            if($data && count($data) > 0){
                return $this->paginated($data, "OK");
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    public function relatedNews()
    {
        
    }
}
