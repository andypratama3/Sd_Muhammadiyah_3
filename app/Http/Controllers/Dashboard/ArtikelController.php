<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Artikel;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\ArtikelData;
use App\Actions\Dashboard\Artikel\ArtikelAction;
use App\Actions\Dashboard\Artikel\ArtikelDeleteAction;

class ArtikelController extends Controller
{
    public function index()
    {
        return view('dashboard.artikel.index');
    }
    public function data_table()
    {
        $query = Artikel::select('id','name','artikel','slug')->ordrBy('created_at','asc');
        dd($query);
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
    public function edit(Artikel $artikel)
    {
        $categorys = Category::select('id','name','slug')->get();
        return view('dashboard.artikel.edit', compact('artikel','categorys'));
    }
    public function update(ArtikelData $artikelData, ArtikelAction $artikelAction, $slug)
    {
        $artikelAction->execute($artikelData, $slug);
        return redirect()->route('dashboard.news.artikel.index')->with('success','Berhasil Update Artikel');

    }
    public function destroy(Artikel $artikel,ArtikelDeleteAction $artikelActionDelete)
    {
        $artikelActionDelete->execute($artikel);
    return redirect()->route('dashboard.news.artikel.index')->with('success','Berhasil Menghapus Artikel');
    }
}
