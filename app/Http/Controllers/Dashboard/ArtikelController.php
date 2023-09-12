<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Artikel;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\ArtikelData;
use App\Actions\Dashboard\Artikel\ArtikelAction;

class ArtikelController extends Controller
{
    public function index()
    {
        $no = 0;
        $artikels = Artikel::select('name','artikel','slug')->get();

        return view('dashboard.artikel.index', compact('artikels','no'));
    }
    public function create()
    {
        $categorys = Category::select('id','name','slug')->get();
        return view('dashboard.artikel.create', compact('categorys'));
    }
    public function store(ArtikelData $artikelData, ArtikelAction $artikelAction)
    {
        $artikelAction->execute($artikelData);
        return redirect()->route('dashboard.news.artikel.index')->with('success','Berhasil Menambahkan Artikel');
    }

}
