<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Fasilitas;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\FasilitasData;
use App\Actions\Dashboard\Fasilitas\FasilitasAction;
use App\Actions\Dashboard\Fasilitas\DeleteFasilitasAction;

class FasilitasController extends Controller
{
    public function index()
    {
        $limit = 15;
        $fasilitass = Fasilitas::select(['nama_fasilitas', 'desc', 'foto', 'slug'])->orderBy('created_at', 'desc')->paginate($limit);
        $count = Fasilitas::count();
        $no = $limit * ($fasilitass->currentPage() - 1);
        return view('dashboard.fasilitas.index', compact('no','count','fasilitass'));
    }

    public function create()
    {
        return view('dashboard.fasilitas.create');
    }

    public function store(FasilitasAction $FasilitasAction, FasilitasData $FasilitasData)
    {

        $FasilitasAction->execute($FasilitasData);


        return redirect()->route('dashboard.fasilitas.index')->with('success', 'Fasilitas Berhasil Di Tambah');
    }

    public function show($slug)
    {
        $fasilitas = Fasilitas::where('slug', $slug)->firstOrFail();
        return view('dashboard.fasilitas.show', compact('fasilitas'));
    }

    public function edit($slug)
    {

        $fasilitas = Fasilitas::where('slug', $slug)->firstOrFail();
        $images = explode(',', $fasilitas->foto);
        return view('dashboard.fasilitas.edit', compact('fasilitas','images'));
    }

    public function update(FasilitasAction $FasilitasAction,FasilitasData $FasilitasData )
    {
        $FasilitasAction->execute($FasilitasData);

        return redirect()->route('dashboard.fasilitas.index')->with('success', 'Fasilitas Berhasil Di Update!');
    }

    public function destroy(DeleteFasilitasAction $deleteFasilitasAction, $slug)
    {
        $deleteFasilitasAction->execute($slug);
        return redirect()->route('dashboard.fasilitas.index')->with('success', 'Fasilitas Berhasil Di Hapus!');
    }
}
