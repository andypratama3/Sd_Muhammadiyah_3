<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Models\KategoriGallery;
use App\Http\Controllers\Controller;

class GalleryDataController extends Controller
{
    /**
     * Ambil semua kategori gallery
     *
     * GET /api/v2/gallery/categories
     */
    public function kategori()
    {
        try {
            $kategoriGallery = KategoriGallery::all();

            if ($kategoriGallery && $kategoriGallery->count() > 0) {
                return $this->success($kategoriGallery, 'Berhasil Menerima Data');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil kategori gallery: ' . $e->getMessage());
        }
    }

    /**
     * Ambil list gallery (ringkas)
     *
     * GET /api/v2/gallery/list
     */
    public function list_gallery()
    {
        try {
            $galleryData = Gallery::select(['name', 'slug', 'updated_at'])->get();

            if ($galleryData && $galleryData->count() > 0) {
                return $this->success($galleryData, 'Berhasil Menerima Data');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil list gallery: ' . $e->getMessage());
        }
    }

    /**
     * Ambil semua gallery dengan kategori
     *
     * GET /api/v2/gallery
     */
    public function listGallery()
    {
        try {
            $gallery = Gallery::with('gallery_kategori')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($gallery && $gallery->count() > 0) {
                return $this->success($gallery, 'Berhasil Menerima Data');
            }

            return $this->error('Data tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data gallery: ' . $e->getMessage());
        }
    }

    /**
     * Ambil detail gallery berdasarkan slug
     *
     * GET /api/v2/gallery/{slug}
     */
    public function show($slug)
    {
        try {
            $gallery = Gallery::with('gallery_kategori')
                ->where('slug', $slug)
                ->first();

            if ($gallery) {
                return $this->success($gallery, 'Berhasil Menerima Data');
            }

            return $this->notFound('Gallery tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil detail gallery: ' . $e->getMessage());
        }
    }
}
