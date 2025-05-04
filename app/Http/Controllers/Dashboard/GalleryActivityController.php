<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Gallery;
use Illuminate\Http\Request;
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
        return view('dashboard.data.gallery.index', compact('gallerys','count', 'no'));
    }

    public function data_table()
    {
        $data = Gallery::select('name', 'foto', 'slug')->orderBy('created_at', 'desc');

        return DataTables::of($data)
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('options', function ($row) {
                    return '
                        <a href="' . route('dashboard.datasekolah.gallery.show', $row->slug) . '" class="btn btn-sm m-1 btn-warning"><i class="fa fa-eye"></i></a>
                        <a href="' . route('dashboard.datasekolah.gallery.edit', $row->slug) . '" class="btn btn-sm m-1 btn-info"><i class="fa fa-edit"></i></a>
                        <button data-id="' . $row['slug'] . '" class="btn btn-sm btn-danger me-1" id="btn-delete"><i class="fa fa-trash"></i></button>
                    ';
                })
                ->addColumn('cover', function ($row){
                    return '<img src="' . asset('storage/img/gallery/' . $row->foto) . '" width="100" height="100" class="img-thumbnail img-fluid" alt="Foto Gallery">';
                })
                ->addIndexColumn()
                ->rawColumns(['options','cover'])
                ->make(true);

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
        $oldFotos = explode(',', $gallery->foto);
        // Hapus semua foto dari storage
        foreach ($oldFotos as $oldFoto) {
            $filePath = 'public/img/gallery/' . trim($oldFoto);
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }
        $action = $gallery->delete();

        if($action){
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil Menghapus Data'
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Menghapus Data'
            ]);
        }


    }
}
