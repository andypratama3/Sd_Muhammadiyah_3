<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\GalleryData;
use App\Actions\Dashboard\Gallery\GalleryAction;

class GalleryActivityController extends Controller
{
    public function index()
    {
        $limit = 10;
        $gallerys = Gallery::select('name', 'foto', 'slug')->paginate($limit);
        $count = $gallerys->count();
        $no = $limit * ($gallerys->currentPage() - 1);
        return view('dashboard.data.gallery.index', compact('gallerys','count', 'no'));
    }

    public function create()
    {
        return view('dashboard.data.gallery.create');
    }

    public function store(GalleryData $galleryData, GalleryAction $galleryAction)
    {
        $galleryAction->execute($galleryData);
        return redirect()->route('dashboard.datasekolah.gallery.index')->with('success','Berhasil Menambhakan Data Gallery');
    }

    public function edit(Gallery $gallery)
    {
        return view('dashboard.data.gallery.edit', compact('gallery'));
    }

    public function update(GalleryData $galleryData, GalleryAction $galleryAction)
    {
        $galleryAction->execute($galleryData);
        return redirect()->route('dashboard.datasekolah.gallery.index')->with('success','Berhasil Update Data Gallery');
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->delete();
        return redirect()->route('dashboard.datasekolah.gallery.index')->with('success','Berhasil Hapus Data Gallery');

    }
}
