<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Comment;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index()
    {
        $no = 0;
        $limit = 25;
        $maxClicks = Artikel::max('jumlah_klik');
        $artikels_trending = Artikel::where('jumlah_klik', $maxClicks)->select('id','name','artikel','image','created_at','slug')->get();
        $artikel_not_trending = Artikel::select('id','name','artikel','image','created_at','slug')->orderBy('created_at','desc')->paginate($limit);
        // $data_artikel = json_encode($artikel_not_trending);
        $artikel_trending_list = Artikel::orderBy('jumlah_klik')->take(20)->get();

        return view('artikel.index', compact('no','artikels_trending','artikel_not_trending','artikel_trending_list', 'maxClicks'));
    }
    public function show(Artikel $artikel)
    {
        $artikel->incrementClickCount();
        $firstCharacter = substr(strip_tags($artikel->artikel), 0, 1);
        $contentWithoutFirstCharacter = substr(strip_tags($artikel->artikel), 1);
        $comments = $artikel->comments()->orderBy('created_at', 'DESC')->get();
        $count = $comments->count();
        $artikel_trending_list = Artikel::select('id','name','artikel','image','created_at','slug')->orderBy('jumlah_klik','DESC')->take(15)->get();
        return view('artikel.show', compact('artikel','firstCharacter','contentWithoutFirstCharacter','comments','count','artikel_trending_list'));
    }

}
