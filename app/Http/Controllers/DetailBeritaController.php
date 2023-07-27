<?php

namespace App\Http\Controllers;

use App\Models\Berita;

class DetailBeritaController extends Controller
{
    public function show($slug)
    {
        $berita = Berita::where('slug', $slug)->firstOrFail();

        return view('detail-berita', compact('berita'));
    }
}
