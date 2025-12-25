<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Models\KategoriGallery;
use App\Http\Controllers\Controller;

class GalleryDataController extends Controller
{
    public function kategori()
    {
        $kategoriGallery = KategoriGallery::all();

        if($kategoriGallery) {
            return $this->success($kategoriGallery, 'Berhasil Menerima Data');
        }
    }

    public function list_gallery()
    {
        $galleryData = Gallery::select(['name', 'slug','updated_at'])->get();

        if($galleryData) {
            return $this->success($galleryData, 'Berhasil Menerima Data');
        }

        return $this->error('Data tidak ditemukan');
    }

    public function listGallery()
    {
        $gallery = Gallery::with('gallery_kategori')->orderBy('created_at', 'desc')->get();

        if($gallery) {
            return $this->success($gallery, 'Berhasil Menerima Data');
        }

        return $this->error('Data tidak ditemukan');
    }

    public function show($slug)
    {
        $gallery = Gallery::with('gallery_kategori')->where('slug', $slug)->first();

        if($gallery) {
            return $this->success($gallery, 'Berhasil Menerima Data');
        }

        return $this->error('Data tidak ditemukan');
    }
}
