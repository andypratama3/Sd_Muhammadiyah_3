<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Models\Prestasi;
use Illuminate\Http\Request;
use App\Models\KategoriPrestasi;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PrestasiDataController extends Controller
{
    /**
     * Ambil semua kategori prestasi
     *
     * GET /api/v2/prestasi/categories
     */
    public function categories()
    {
        try {
            $categories = KategoriPrestasi::all();

            return $this->success($categories, 'Kategori prestasi berhasil diambil');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil kategori prestasi: ' . $e->getMessage());
        }
    }

    /**
     * Ambil kategori yang digunakan prestasi siswa saja
     *
     * GET /api/v2/prestasi/categories/siswa
     */
    public function categoriesSiswa()
    {
        try {
            $categories = Prestasi::getSiswaCategories();

            return $this->success($categories, 'Kategori prestasi siswa berhasil diambil');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil kategori prestasi siswa: ' . $e->getMessage());
        }
    }

    /**
     * Ambil kategori yang digunakan prestasi sekolah saja
     *
     * GET /api/v2/prestasi/categories/sekolah
     */
    public function categoriesSekolah()
    {
        try {
            $categories = Prestasi::getSekolahCategories();

            return $this->success($categories, 'Kategori prestasi sekolah berhasil diambil');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil kategori prestasi sekolah: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Get count of prestasi siswa grouped by tingkat
     * Endpoint: GET /api/v2/prestasi/siswa/count-by-tingkat
     */
    public function countSiswaByTingkat()
    {
        try {
            $data = Prestasi::siswaWithCategories()
                ->select('tingkat', DB::raw('count(*) as total'))
                ->groupBy('tingkat')
                ->orderByRaw("FIELD(tingkat, 'Internasional', 'Nasional', 'Provinsi', 'Kota', 'Kecamatan', 'Sekolah')")
                ->get();

            if($data && count($data) > 0){
                return $this->success($data, "OK");
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Get count of prestasi siswa grouped by category
     * Endpoint: GET /api/v2/prestasi/siswa/count-by-category
     */
    public function countSiswaByCategory()
    {
        try {
            // Get all categories with count
            $data = KategoriPrestasi::withCount(['prestasi as total' => function ($query) {
                $query->where('status', 1); // Only siswa prestasi
            }])
            ->having('total', '>', 0)
            ->orderBy('total', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'category_id' => $item->id,
                    'category_name' => $item->name,
                    'total' => $item->total
                ];
            });

            if($data && count($data) > 0){
                return $this->success($data, "OK");
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * ✅ IMPROVED: Ambil semua prestasi siswa dengan kategori + PAGINATION
     *
     * GET /api/v2/prestasi/siswa
     * Query params:
     * - tingkat: 'Sekolah', 'Kota', 'Provinsi', 'Nasional', 'Internasional'
     * - kategori_id: ID dari kategori prestasi
     * - tahun: Filter berdasarkan tahun
     * - search: Search by name or description
     * - per_page: Jumlah per halaman (default 12)
     * - page: Halaman (default 1)
     */
    public function prestasi_siswa(Request $request)
    {
        try {
            $search = $request->input('search', null);
            $tingkat = $request->input('tingkat', null);
            $kategoriId = $request->input('kategori_id', null);
            $tahun = $request->input('tahun', null);
            $perPage = $request->input('per_page', 12);
            $page = $request->input('page', 1);

            $query = Prestasi::siswaWithCategories();

            // Apply search filter
            if ($search && !empty(trim($search))) {
                $searchTerm = trim($search);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('penyelenggara', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filter berdasarkan tingkat (jangan filter jika 'all' atau 'semua')
            if ($tingkat && !empty(trim($tingkat)) && !in_array(strtolower($tingkat), ['all', 'semua'])) {
                $query->where('tingkat', $tingkat);
            }

            // Filter berdasarkan kategori (jangan filter jika 'all' atau 'semua')
            if ($kategoriId && !empty(trim($kategoriId)) && !in_array(strtolower($kategoriId), ['all', 'semua'])) {
                $query->whereHas('prestasi_kategori', function ($q) use ($kategoriId) {
                    $q->where('kategori_prestasi_id', $kategoriId);
                });
            }

            // Filter berdasarkan tahun
            if ($tahun && !empty($tahun)) {
                $query->whereYear('tanggal', $tahun);
            }

            // Sort berdasarkan tanggal terbaru
            $query->orderBy('tanggal', 'desc');

            // Get paginated data
            $prestasi = $query->paginate($perPage, ['*'], 'page', $page);

            // Transform response dengan kategori
            $data = $prestasi->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'name' => $item->name,
                    'description' => $item->description,
                    'foto' => $item->foto,
                    'status' => $item->status,
                    'tingkat' => $item->tingkat,
                    'penyelenggara' => $item->penyelenggara,
                    'tanggal' => $item->tanggal,
                    'juara' => $item->juara,
                    'kategori' => $item->prestasi_kategori->map(fn($cat) => [
                        'id' => $cat->id,
                        'name' => $cat->name,
                    ])->toArray(),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            $prestasi->setCollection($data);

            return $this->paginated($prestasi, 'Prestasi siswa berhasil diambil');
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Get popular prestasi siswa ordered by views (if views column exists)
     * Endpoint: GET /api/v2/prestasi/siswa/popular
     */
    public function prestasiSiswaPopular()
    {
        try {
            $prestasi = Prestasi::siswaWithCategories()
                ->orderBy('tanggal', 'desc') // Sort by newest if no views column
                ->take(10)
                ->get();

            $data = $prestasi->map(function ($item) {
                return [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'name' => $item->name,
                    'description' => $item->description,
                    'foto' => $item->foto,
                    'status' => $item->status,
                    'tingkat' => $item->tingkat,
                    'penyelenggara' => $item->penyelenggara,
                    'tanggal' => $item->tanggal,
                    'juara' => $item->juara,
                    'kategori' => $item->prestasi_kategori->map(fn($cat) => [
                        'id' => $cat->id,
                        'name' => $cat->name,
                    ])->toArray(),
                ];
            });

            if($data && count($data) > 0){
                return $this->success($data, "OK");
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * ✅ IMPROVED: Ambil semua prestasi sekolah dengan kategori + PAGINATION
     *
     * GET /api/v2/prestasi/sekolah
     * Query params:
     * - kategori_id: ID dari kategori prestasi
     * - tahun: Filter berdasarkan tahun
     * - search: Search by name or description
     * - per_page: Jumlah per halaman (default 12)
     * - page: Halaman (default 1)
     */
    public function prestasi_sekolah(Request $request)
    {
        try {
            $search = $request->input('search', null);
            $kategoriId = $request->input('kategori_id', null);
            $tahun = $request->input('tahun', null);
            $perPage = $request->input('per_page', 12);
            $page = $request->input('page', 1);

            $query = Prestasi::sekolahWithCategories();

            // Apply search filter
            if ($search && !empty(trim($search))) {
                $searchTerm = trim($search);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filter berdasarkan kategori (jangan filter jika 'all' atau 'semua')
            if ($kategoriId && !empty(trim($kategoriId)) && !in_array(strtolower($kategoriId), ['all', 'semua'])) {
                $query->whereHas('prestasi_kategori', function ($q) use ($kategoriId) {
                    $q->where('kategori_prestasi_id', $kategoriId);
                });
            }

            // Filter berdasarkan tahun
            if ($tahun && !empty($tahun)) {
                $query->whereYear('tanggal', $tahun);
            }

            // Sort berdasarkan tanggal terbaru
            $query->orderBy('tanggal', 'desc');

            // Get paginated data
            $prestasi = $query->paginate($perPage, ['*'], 'page', $page);

            // Transform response dengan kategori
            $data = $prestasi->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'name' => $item->name,
                    'description' => $item->description,
                    'foto' => $item->foto,
                    'status' => $item->status,
                    'tanggal' => $item->tanggal,
                    'kategori' => $item->prestasi_kategori->map(fn($cat) => [
                        'id' => $cat->id,
                        'name' => $cat->name,
                    ])->toArray(),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            $prestasi->setCollection($data);

            return $this->paginated($prestasi, 'Prestasi sekolah berhasil diambil');
        } catch (\Exception $e) {
            return $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * ✅ IMPROVED: Ambil detail prestasi siswa by slug + INCREMENT VIEWS
     *
     * GET /api/v2/prestasi/siswa/{slug}
     */
    public function prestasi_siswa_detail($slug)
    {
        try {
            $prestasi = Prestasi::where('slug', $slug)
                ->siswa()
                ->with('prestasi_kategori')
                ->firstOrFail();

            $prestasi->incrementClickCount('views');

            $data = [
                'id' => $prestasi->id,
                'slug' => $prestasi->slug,
                'name' => $prestasi->name,
                'description' => $prestasi->description,
                'foto' => $prestasi->foto,
                'status' => $prestasi->status,
                'tingkat' => $prestasi->tingkat,
                'penyelenggara' => $prestasi->penyelenggara,
                'tanggal' => $prestasi->tanggal,
                'juara' => $prestasi->juara,
                'views' => $prestasi->views ?? 0,
                'kategori' => $prestasi->prestasi_kategori->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                ])->toArray(),
                'created_at' => $prestasi->created_at,
                'updated_at' => $prestasi->updated_at,
            ];

            return $this->success(['data' => $data], 'Detail prestasi siswa berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Prestasi siswa tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil detail prestasi siswa: ' . $e->getMessage());
        }
    }

    /**
     * ✅ IMPROVED: Ambil detail prestasi sekolah by slug + INCREMENT VIEWS
     *
     * GET /api/v2/prestasi/sekolah/{slug}
     */
    public function prestasi_sekolah_detail($slug)
    {
        try {
            $prestasi = Prestasi::where('slug', $slug)
                ->sekolah()
                ->with('prestasi_kategori')
                ->firstOrFail();

            // Increment views if column exists
            $prestasi->incrementClickCount('views');

            $data = [
                'id' => $prestasi->id,
                'slug' => $prestasi->slug,
                'name' => $prestasi->name,
                'description' => $prestasi->description,
                'foto' => $prestasi->foto,
                'status' => $prestasi->status,
                'tanggal' => $prestasi->tanggal,
                'views' => $prestasi->views ?? 0,
                'kategori' => $prestasi->prestasi_kategori->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                ])->toArray(),
                'created_at' => $prestasi->created_at,
                'updated_at' => $prestasi->updated_at,
            ];

            return $this->success(['data' => $data], 'Detail prestasi sekolah berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Prestasi sekolah tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil detail prestasi sekolah: ' . $e->getMessage());
        }
    }

    /**
     * Ambil statistik prestasi
     *
     * GET /api/v2/prestasi/statistics
     * Query params:
     * - tahun: Filter berdasarkan tahun (optional)
     */
    public function statistics(Request $request)
    {
        try {
            $tahun = $request->input('tahun');

            // Query builder untuk prestasi siswa
            $siswaQuery = Prestasi::siswaWithCategories();
            $sekolahQuery = Prestasi::sekolahWithCategories();

            if ($tahun) {
                $siswaQuery->whereYear('tanggal', $tahun);
                $sekolahQuery->whereYear('tanggal', $tahun);
            }

            $statistics = [
                'total_prestasi_siswa' => $siswaQuery->count(),
                'total_prestasi_sekolah' => $sekolahQuery->count(),
                'prestasi_siswa_by_level' => [
                    'sekolah' => Prestasi::siswaWithCategories()->where('tingkat', 'Sekolah')->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))->count(),
                    'kecamatan' => Prestasi::siswaWithCategories()->where('tingkat', 'Kecamatan')->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))->count(),
                    'kota' => Prestasi::siswaWithCategories()->where('tingkat', 'Kota')->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))->count(),
                    'provinsi' => Prestasi::siswaWithCategories()->where('tingkat', 'Provinsi')->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))->count(),
                    'nasional' => Prestasi::siswaWithCategories()->where('tingkat', 'Nasional')->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))->count(),
                    'internasional' => Prestasi::siswaWithCategories()->where('tingkat', 'Internasional')->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))->count(),
                ],
            ];

            return $this->success($statistics, 'Statistik prestasi berhasil diambil');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil statistik prestasi: ' . $e->getMessage());
        }
    }
}
