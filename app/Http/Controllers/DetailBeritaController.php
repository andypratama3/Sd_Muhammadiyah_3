<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Artikel;

class DetailBeritaController extends Controller
{
    public function show($slug)
    {
        $berita = Berita::where('slug', $slug)->firstOrFail();
        $artikel_trending_list = Artikel::orderBy('jumlah_klik')->take(20)->get();
        return view('detail-berita', compact('berita','artikel_trending_list'));
    }
}
