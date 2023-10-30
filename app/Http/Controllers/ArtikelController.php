<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index()
    {
        $no = 0;
        $maxClicks = Artikel::max('jumlah_klik');

        $artikels = Artikel::where('jumlah_klik', $maxClicks)->select('id','name','artikel','image','created_at','slug')->orderBy('created_at','asc')->get();
        $artikel_not_trending = Artikel::select('id','name','artikel','image','created_at','slug')->orderBy('created_at','asc')->get();


        $artikel_trending_list = Artikel::select('id','name','artikel','image','created_at','slug')->orderBy('jumlah_klik','asc')->get();
        return view('artikel.index', compact('no','artikels', 'maxClicks','artikel_not_trending','artikel_trending_list'));
    }
    public function show(Artikel $artikel)
    {
        $artikel->incrementClickCount();
        $firstCharacter = substr(strip_tags($artikel->artikel), 0, 1);
        $contentWithoutFirstCharacter = substr(strip_tags($artikel->artikel), 1);

        return view('artikel.show', compact('artikel','firstCharacter','contentWithoutFirstCharacter'));
    }
}
