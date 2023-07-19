<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreFasilitasRequest;
use App\Actions\Dashboard\Fasilitas\StoreFasilitasAction;
use App\Actions\Dashboard\Fasilitas\UpdateFasilitasAction;

class FasilitasController extends Controller
{
    public function index()
    {
        $no = 0;
        $fasilitass = Fasilitas::select(['nama_fasilitas','desc','foto','slug'])->get();
        return view('dashboard.fasilitas.index',compact('no','fasilitass'));
    }
    public function create()
    {
        return view('dashboard.fasilitas.create');
    }

    public function store(StoreFasilitasRequest $request, StoreFasilitasAction $storeFasilitasAction){

        $storeFasilitasAction->execute($request);
        return redirect()->route('dashboard.fasilitas.index')->with('succes-insert','Fasilitas Berhasil Di Tambah');
    }
    public function show($slug){
        $fasilitas = Fasilitas::select(['nama_fasilitas','desc','foto','slug'])->firstOrFail();

        return view('dashboard.fasilitas.show',compact('fasilitas'));
    }
    public function edit(){

        $fasilitas = Fasilitas::select(['nama_fasilitas','desc','foto','slug'])->firstOrFail();
        return view('dashboard.fasilitas.edit',compact('fasilitas'));
    }

    public function update(StoreFasilitasRequest $request, UpdateFasilitasAction $updateFasilitasAction, $slug)
    {
        $updateFasilitasAction->excute($request,$slug);

        return redirect()->route('dashboard.fasilitas.index')->with('succes-update','Fasilitas Berhasil Di Update!');
    }

}
