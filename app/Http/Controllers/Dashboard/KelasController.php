<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\KelasData;
use App\Actions\Dashboard\Kelas\KelasAction;
use App\Actions\Dashboard\Kelas\KelasDeleteAction;

class KelasController extends Controller
{
    public function index()
    {
        $no = 0;
        $kelass = Kelas::select(['name','category_kelas','slug'])->orderBy('name')->get();
        // dd($kelass);
        return view('dashboard.data.kelas.index', compact('no','kelass'));
    }
    public function show($slug)
    {
        $kelas = Kelas::where('slug', $slug)->firstOrFail();
        return view('dashboard.data.kelas.show', compact('kelas'));
    }
    public function create()
    {
        return view('dashboard.data.kelas.create');
    }
    public function store(KelasData $kelasData, KelasAction $kelasAction)
    {
        $kelasAction->execute($kelasData);
        return redirect()->route('dashboard.datasekolah.kelas.index')->with('success','Berhasil Menambahkan Kelas');
    }
    public function edit($slug)
    {
        $kelas = Kelas::where('slug', $slug)->firstOrFail();

        return view('dashboard.data.kelas.edit', compact('kelas'));
    }
    public function update(KelasData $kelasData, KelasAction $kelasAction)
    {
        $kelasAction->execute($kelasData);
        return redirect()->route('dashboard.datasekolah.kelas.index')->with('succes','Berhasil Mengubah Kelas');
    }
    public function destroy($slug, KelasDeleteAction $kelasDeleteAction)
    {
        $kelasDeleteAction->execute($slug);
        return redirect()->route('dashboard.datasekolah.kelas.index')->with('success', 'Berhasil Menghapus Kelas!');
    }
}
