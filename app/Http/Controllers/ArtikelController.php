<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $limit = 10;
        $categorys = Category::select(['id','name','slug'])->orderBy('name','desc')->get();
        $maxClicks = Artikel::max('jumlah_klik');
        $artikels_trending = Artikel::select('id','name','artikel','image','created_at','slug')
            ->where('status', 'publish')
            ->orderBy('jumlah_klik', 'DESC')
            ->paginate($limit);

        if ($request->ajax()) {
            return view('artikel.load-data', compact(
                'artikels_trending', 'maxClicks'
            ));
        }

        if($request->category)
        {
            $artikels_trending = $artikels_trending
                ->where('category_id', $request->category)
                ->orderBy('created_at', 'desc')
                ->paginate($limit);
        }

        return view('artikel.index', compact(
            'artikels_trending', 'maxClicks', 'categorys'
        ));
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
