<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Berita;
use App\Models\Artikel;
class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $beritas = Berita::orderBy('created_at', 'desc')->paginate(9);

        if ($request->ajax()) {
            return view('berita.load', compact('beritas'))->render();
        }
        return view('berita.index', compact('beritas'));
    }

    public function show($slug)
    {
        $berita = Berita::where('slug', $slug)->firstOrFail();
        $latest_artikel = Artikel::orderBy('created_at', 'desc')->take(15)->get();
        $artikel_trending_list = Artikel::select('id','name','artikel','image','created_at','slug')->orderBy('jumlah_klik','DESC')->take(15)->get();
        return view('berita.detail-berita', compact('berita','latest_artikel','artikel_trending_list'));
    }
}
