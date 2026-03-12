<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Dashboard\Kelas\KelasAction;
use App\Actions\Dashboard\Kelas\KelasDeleteAction;
use App\DataTransferObjects\KelasData;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pelajaran;

class KelasController extends Controller
{
    public function index()
    {
        $no = 0;
        $kelass = Kelas::select(['name', 'category_kelas', 'slug'])->orderBy('name')->get();

        // dd($kelass);
        return view('dashboard.data.kelas.index', compact('no', 'kelass'));
    }

    public function show($slug)
    {
        $kelas = Kelas::where('slug', $slug)->firstOrFail();

        return view('dashboard.data.kelas.show', compact('kelas'));
    }

    public function create()
    {
        $pelajarans = Pelajaran::all();
        return view('dashboard.data.kelas.create', compact('pelajarans'));
    }

    public function store(KelasData $kelasData, KelasAction $kelasAction)
    {
        $kelasAction->execute($kelasData);

        return redirect()->route('dashboard.datasekolah.kelas.index')->with('success', 'Berhasil Menambahkan Kelas');
    }

    public function edit($slug)
    {
        $kelas = Kelas::where('slug', $slug)->firstOrFail();
        $pelajarans = Pelajaran::all();

        return view('dashboard.data.kelas.edit', compact('kelas', 'pelajarans'));
    }

    public function update(KelasData $kelasData, KelasAction $kelasAction)
    {
        $kelasAction->execute($kelasData);

        return redirect()->route('dashboard.datasekolah.kelas.index')->with('succes', 'Berhasil Mengubah Kelas');
    }

    public function destroy($slug, KelasDeleteAction $kelasDeleteAction)
    {
        $kelasDeleteAction->execute($slug);

        return redirect()->route('dashboard.datasekolah.kelas.index')->with('success', 'Berhasil Menghapus Kelas!');
    }
}
