<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
    public function __invoke()
    {
        $beritas = Berita::select(['judul','desc','foto','slug'])->latest()->get();
        return view('beranda', compact('beritas'));
    }
    public function detail($slug)
    {
        $berita = Berita::where('slug',$slug)->firstOrFail();
        return view('detail-berita',compact('berita'));
    }
}
