<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Fasilitas;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\FasilitasData;
use App\Actions\Dashboard\Fasilitas\FasilitasAction;
use App\Http\Requests\Dashboard\UpdateFasilitasRequest;
use App\Actions\Dashboard\Fasilitas\DeleteFasilitasAction;
use App\Actions\Dashboard\Fasilitas\UpdateFasilitasAction;

class FasilitasController extends Controller
{
    public function index()
    {
        $no = 0;
        $fasilitass = Fasilitas::select(['nama_fasilitas', 'desc', 'foto', 'slug'])->get();

        return view('dashboard.fasilitas.index', compact('no', 'fasilitass'));
    }

    public function create()
    {
        return view('dashboard.fasilitas.create');
    }

    public function store(FasilitasAction $FasilitasAction, FasilitasData $FasilitasData)
    {

        $storeFasilitasAction->execute($FasilitasData);

        return redirect()->route('dashboard.fasilitas.index')->with('success', 'Fasilitas Berhasil Di Tambah');
    }

    public function show($slug)
    {
        $fasilitas = Fasilitas::select(['nama_fasilitas', 'desc', 'foto', 'slug'])->firstOrFail();
        $images = explode(',', $fasilitas->foto);
        return view('dashboard.fasilitas.show', compact('fasilitas','images'));
    }

    public function edit()
    {

        $fasilitas = Fasilitas::select(['nama_fasilitas', 'desc', 'foto', 'slug'])->firstOrFail();
        $images = explode(',', $fasilitas->foto);
        return view('dashboard.fasilitas.edit', compact('fasilitas','images'));
    }

    public function update(UpdateFasilitasRequest $request, UpdateFasilitasAction $updateFasilitasAction, $slug)
    {
        $updateFasilitasAction->execute($request, $slug);

        return redirect()->route('dashboard.fasilitas.index')->with('success', 'Fasilitas Berhasil Di Update!');
    }

    public function destroy(DeleteFasilitasAction $deleteFasilitasAction, $slug)
    {
        $deleteFasilitasAction->execute($slug);
        return redirect()->route('dashboard.fasilitas.index')->with('success', 'Fasilitas Berhasil Di Hapus!');
    }
}
