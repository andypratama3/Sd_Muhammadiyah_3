<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Berita;
class BeritaController extends Controller
{
    public function index()
    {
        $beritas = Berita::orderBy('created_at', 'asc')->get();
        return view('berita.index', compact('beritas'));
    }
    public function show($slug)
    {
        $berita = Berita::where('slug', $slug)->firstOrFail();
        $artikel_trending_list = Artikel::orderBy('jumlah_klik')->take(20)->get();
        return view('detail-berita', compact('berita','artikel_trending_list'));
    }
}
