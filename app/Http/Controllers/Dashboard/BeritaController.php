<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Berita;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Dashboard\Berita\StoreBeritaAction;
use App\Http\Requests\Dashboard\StoreBeritaRequest;
use App\Actions\Dashboard\Berita\DeleteBeritaAction;
use App\Actions\Dashboard\Berita\UpdateBeritaAction;
use App\Http\Requests\Dashboard\UpdateBeritaRequest;

class BeritaController extends Controller
{
    public function index()
    {
        $no = 0;
        $beritas = Berita::select(['judul','desc','foto','slug'])->get();
        return view('dashboard.berita.index', compact('no','beritas'));
    }

    public function create()
    {
        return view('dashboard.berita.create');
    }
    public function store(StoreBeritaRequest $request , StoreBeritaAction $storeBeritaAction)
    {
        $storeBeritaAction->execute($request);
        return redirect()->route('dashboard.berita.index')->with('status','Berita Berhasil Di Tambah');
    }

    public function show($slug){
        $berita = Berita::where('slug',$slug)->select(['judul','desc','foto','slug'])->firstOrFail();
        return view('dashboard.berita.show', compact('berita'));
    }
    public function edit($slug){
        $berita = Berita::where('slug',$slug)->select(['judul','desc','foto','slug'])->firstOrFail();
        return view('dashboard.berita.edit', compact('berita'));
    }
    public function update(UpdateBeritaRequest $request, UpdateBeritaAction $updateBeritaAction, $slug)
    {

        $updateBeritaAction->execute($request,$slug);
        redirect()->route('dashboard.berita.index')->with('status-update','Berita Berhasil Di Updtae');
    }
    public function destroy(DeleteBeritaAction $deleteBeritaAction,$slug){

        $deleteBeritaAction->execute($slug);
        return view('dashboard.berita.index')->with('status-delete','berita berhasil di hapus');
    }
}
