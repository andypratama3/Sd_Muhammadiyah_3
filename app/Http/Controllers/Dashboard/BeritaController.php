<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Berita;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\BeritaData;
use Yajra\DataTables\Facades\DataTables;
use App\Actions\Dashboard\Berita\ActionBerita;
use App\Actions\Dashboard\Berita\DeleteBeritaAction;

class BeritaController extends Controller
{
    public function index()
    {
        $no = 0;
        $beritas = Berita::select(['judul', 'desc', 'foto', 'slug'])->get();
        return view('dashboard.berita.index', compact('no', 'beritas'));
    }

    public function data_table()
    {
        $query = Berita::select('id','judul','desc','foto','slug')->orderBy('created_at','asc');
        return DataTables::of($query)

                ->addColumn('foto', function ($row){
                    return '<img src="' . asset('storage/img/berita/' . $row->foto) . '" width="100" height="100" class="img-thumbnail" alt="Foto Berita">';
                })
                ->addColumn('options', function ($row){
                    return '
                    <a href="' . route('dashboard.news.berita.show', $row->slug) . '" class="btn btn-sm me-1 btn-warning"><i class="fa fa-eye"></i></a>
                    <a href="' . route('dashboard.news.berita.edit', $row->slug) . '" class="btn btn-sm me-1 btn-primary"><i class="fa fa-pen"></i></a>
                    <button data-id="' . $row['slug'] . '" class="btn btn-sm btn-danger" id="btn-delete"><i class="fa fa-trash"></i></button>
                ';
                })
                ->rawColumns(['foto','options'])
                ->addIndexColumn()
                ->make(true);

    }
    public function create()
    {
        return view('dashboard.berita.create');
    }

    public function store(ActionBerita $ActionBerita, BeritaData $beritaData)
    {
        $ActionBerita->execute($beritaData);

        return redirect()->route('dashboard.news.berita.index')->with('success', 'Berita Berhasil Di Tambah');
    }

    public function show($slug)
    {
        $berita = Berita::where('slug', $slug)->select(['judul', 'desc', 'foto', 'slug'])->firstOrFail();

        return view('dashboard.berita.show', compact('berita'));
    }

    public function edit($slug)
    {
        $berita = Berita::where('slug', $slug)->select(['judul', 'desc', 'foto', 'slug'])->firstOrFail();

        return view('dashboard.berita.edit', compact('berita'));
    }

    public function update(ActionBerita $ActionBerita, BeritaData $beritaData)
    {
        $ActionBerita->execute($beritaData);
        return redirect()->route('dashboard.news.berita.index')->with('success', 'Berita Berhasil Di Update');
    }

    public function destroy(DeleteBeritaAction $deleteBeritaAction, $slug)
    {
        if($deleteBeritaAction)
        {
            $deleteBeritaAction->execute($slug);
            return response()->json(['status' => 'success', 'message' => 'Berhasil Menghapus Berita']);
        }else{
            return response()->json(['status' => 'error', 'message' => 'Gagal Menghapus Berita']);
        }
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('gambar_upload')) {
            $file = $request->file('gambar_upload');
            $ext = $file->getClientOriginalExtension();

            $upload_path = public_path('/storage/img/berita/');
            $filename = 'berita_' . Str::slug(Str::random(6)) . '_' . date('YmdHis') . '.' . $ext;

            $file->move($upload_path, $filename);

            // Berikan URL file yang dapat digunakan
            $fileUrl = url('storage/img/berita/' . $filename);
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil Mengupload Gambar',
                'url' => $fileUrl,
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengunggah gambar',
        ], 400);
    }
}
