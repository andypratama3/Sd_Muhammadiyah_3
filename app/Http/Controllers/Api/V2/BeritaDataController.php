<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BeritaDataController extends Controller
{
    public function countData()
    {
        $berita = Berita::groupBy('category')->select('category', DB::raw('count(*) as total'))->get();

        if($berita){
            return $this->success($berita, "OK");
        }

        $this->error('Data tidak ditemukan');
    }

    public function beritaPopuler()
    {
        $berita = Berita::orderBy('views', 'desc')->take(10)->get();

        if($berita){
            return $this->success($berita, "OK");
        }

        $this->error('Data tidak ditemukan');
    }

    public function search(Request $request)
    {
        $search = $request->search;


    }

    public function list(Request $request)
    {
        $search = $request->input("search");

        $paginate = 10;

        if ($search) {
            $data = Berita::where('judul', 'LIKE', "%{$search}%")
                ->orWhere('desc', 'LIKE', "%{$search}%")
                ->orderBy('created_at', 'desc')
                ->paginate($paginate);
        } else {
            $data = Berita::orderBy('created_at', 'desc')->paginate($paginate);
        }

        if ($data) {
            return $this->paginated($data, "OK");
        }

        $this->error('Data tidak ditemukan');
    }

    public function show($slug)
    {
        $data = Berita::where('slug', $slug)->firstOrFail();

        if($data){
            return $this->success([
                "data" => [
                    'judul'=> $data->judul,
                    'desc' => $data->desc,
                    'views' => $data->views,
                    'slug' => $data->slug,
                    'category' => $data->category,
                    'foto' => $data->foto,
                ]
            ], "OK");
        }

        $this->error('Data tidak ditemukan');
    }
}
