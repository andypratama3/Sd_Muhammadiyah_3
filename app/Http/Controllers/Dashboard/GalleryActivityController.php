<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Gallery;
use App\Models\KategoriGallery;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\DataTransferObjects\GalleryData;
use Yajra\DataTables\Facades\DataTables;
use App\Actions\Dashboard\Gallery\GalleryAction;

class GalleryActivityController extends Controller
{
    public function index()
    {
        $limit = 10;
        $gallerys = Gallery::select('name', 'foto', 'slug')->paginate($limit);
        $count = $gallerys->count();
        $no = $limit * ($gallerys->currentPage() - 1);

        return view('dashboard.data.gallery.index', compact('gallerys', 'count', 'no'));
    }

    public function data_table()
    {
        $data = Gallery::select(['name', 'foto', 'link', 'slug', 'cover'])->orderBy('created_at', 'desc');

        return DataTables::of($data)
            ->addColumn('name', function ($row) {
                return $row->name;
            })
            ->addColumn('options', function ($row) {
                return '
                        <a href="'.route('dashboard.datasekolah.gallery.show', $row->slug).'" class="m-1 btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                        <a href="'.route('dashboard.datasekolah.gallery.edit', $row->slug).'" class="m-1 btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                        <button data-id="'.$row['slug'].'" class="btn btn-sm btn-danger me-1" id="btn-delete"><i class="fa fa-trash"></i></button>
                    ';
            })
            ->addColumn('cover', function ($row) {
                return '<img src="'.asset('storage/img/gallery/cover/'.$row->cover).'" width="100" height="100" class="img-thumbnail img-fluid" alt="Foto Gallery">';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'cover'])
            ->make(true);

    }

    public function create()
    {
        $kategoriGallery = KategoriGallery::all();
        return view('dashboard.data.gallery.create', compact('kategoriGallery'));
    }

    public function store(GalleryData $galleryData, GalleryAction $galleryAction)
    {
        $galleryAction->execute($galleryData);

        return redirect()->route('dashboard.datasekolah.gallery.index')->with('success', 'Berhasil Menambhakan Data Gallery');
    }

    public function edit(Gallery $gallery)
    {
        $kategoriGallery = KategoriGallery::all();
        return view('dashboard.data.gallery.edit', compact('gallery','kategoriGallery'));
    }

    public function update(GalleryData $galleryData, GalleryAction $galleryAction)
    {
        $galleryAction->execute($galleryData);

        return redirect()->route('dashboard.datasekolah.gallery.index')->with('success', 'Berhasil Update Data Gallery');
    }

    public function destroy(Gallery $gallery)
    {
        $oldFotos = explode(',', $gallery->foto);
        // Hapus semua foto dari storage
        foreach ($oldFotos as $oldFoto) {
            $filePath = 'public/img/gallery/'.trim($oldFoto);
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }

        $gallery->gallery_kategori()->detach();

        $action = $gallery->delete();

        if ($action) {
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil Menghapus Data',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Menghapus Data',
            ]);
        }

    }
}
