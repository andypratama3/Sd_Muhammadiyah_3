<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Artikel;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\ArtikelData;
use Yajra\DataTables\Facades\DataTables;
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
        $query = Artikel::with('categorys')->select('id','name','image','jumlah_klik','artikel','slug')->orderBy('created_at','asc');
        return DataTables::of($query)
                ->addColumn('kategori.name', function ($artikel) {
                    $categoryNames = $artikel->categorys->pluck('name')->implode(', ');
                    return $categoryNames;
                })
                ->addColumn('options', function ($row){
                    return '
                    <a href="' . route('dashboard.news.artikel.show', $row->slug) . '" class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                    <a href="' . route('dashboard.news.artikel.edit', $row->slug) . '" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                    <button data-id="' . $row['slug'] . '" class="btn btn-sm btn-danger" id="btn-delete"><i class="fa fa-trash"></i></button>
                ';
                })
                ->rawColumns(['options'])
                ->addIndexColumn()
                ->make(true);

    }
    public function create()
    {
        $categorys = Category::select('id','name','slug')->get();
        return view('dashboard.artikel.create', compact('categorys'));
    }
    public function store(ArtikelData $artikelData, ArtikelAction $artikelAction)
    {
        $artikelAction->execute($artikelData);
        activity()->log('Menambahkan Artikel');
        return redirect()->route('dashboard.news.artikel.index')->with('success','Berhasil Menambahkan Artikel');
    }
    public function show(Artikel $artikel)
    {

        return view('dashboard.artikel.show', compact('artikel'));
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
    public function destroy(ArtikelDeleteAction $artikelActionDelete,$slug)
    {
        if($artikelActionDelete)
        {
            $artikelActionDelete->execute($slug);
            return response()->json(['status' => 'success', 'message' => 'Berhasil Menghapus Artikel']);
            // toaster()->sucess('Berhasil Menghapus Artikel');
        }else{
            return response()->json(['status' => 'error', 'message' => 'Gagal Mengirim Kritik Dan Saran']);
        }
    }
}
