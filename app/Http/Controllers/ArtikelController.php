<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Comment;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $no = 0;
        $limit = 10;
        $maxClicks = Artikel::max('jumlah_klik');
        $artikels_trending = Artikel::select('id','name','artikel','image','created_at','slug')->where('status','publish')->orderBy('jumlah_klik','DESC')->take(5)->get();
        $artikel_not_trending = Artikel::select('id','name','artikel','image','created_at','slug')->where('status','publish')->orderBy('created_at','desc')->paginate($limit);
        // dd($artikel_not_trending->count());
        $latest_artikel = Artikel::orderBy('created_at', 'desc');
        $artikel_trending_list = Artikel::orderBy('jumlah_klik')->take(20)->get();
        if($request->ajax()){
            return view('artikel.load-data', compact('no','artikels_trending','artikel_not_trending','artikel_trending_list', 'maxClicks','latest_artikel'));
        }

        return view('artikel.index', compact('no','artikels_trending','artikel_not_trending','artikel_trending_list', 'maxClicks','latest_artikel'));
    }
    public function show(Artikel $artikel)
    {
        $artikel->incrementClickCount();
        $maxClicks = Artikel::max('jumlah_klik');

        $firstCharacter = substr(strip_tags($artikel->artikel), 0, 1);
        $contentWithoutFirstCharacter = substr(strip_tags($artikel->artikel), 1);
        $comments = $artikel->comments()->orderBy('created_at', 'DESC')->get();
        $latest_artikel = Artikel::orderBy('created_at', 'desc')->take(15)->get();
        $count = $comments->count();
        $artikel_trending_list = Artikel::select('id','name','artikel','image','created_at','slug')->orderBy('jumlah_klik','DESC')->take(15)->get();
        return view('artikel.show', compact('artikel','firstCharacter','contentWithoutFirstCharacter','comments','count','artikel_trending_list','latest_artikel'));
    }

}
