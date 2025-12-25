<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\KategoriGallery;
use App\Http\Controllers\Controller;

class KategoriGalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = 15;

        $kategoriGallery = KategoriGallery::select(['id', 'name'])
            ->paginate($limit);

        $count = $kategoriGallery->count();
        $no = $limit * ($kategoriGallery->currentPage() - 1);

        return view('dashboard.data.gallery.kategori.index', compact(
            'kategoriGallery',
            'count',
            'no'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.data.gallery.kategori.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        KategoriGallery::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('dashboard.datasekolah.kategori.gallery.index')
            ->with('success', 'Berhasil Menambahkan Kategori Gallery');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kategoriGallery = KategoriGallery::findOrFail($id);

        return view(
            'dashboard.data.gallery.kategori.edit',
            compact('kategoriGallery')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $kategoriGallery = KategoriGallery::findOrFail($id);

        $kategoriGallery->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('dashboard.datasekolah.kategori.gallery.index')
            ->with('success', 'Berhasil Mengupdate Kategori Gallery');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kategoriGallery = KategoriGallery::findOrFail($id);
        $kategoriGallery->delete();

        return redirect()
            ->route('dashboard.datasekolah.kategori.gallery.index')
            ->with('success', 'Berhasil Menghapus Kategori Gallery');
    }
}
