<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StrukturTenagaPendidikan;

class StrukturTenagaPendidikanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = 15;

        $strukturTenagaPendidikan = StrukturTenagaPendidikan::with('parent')->orderBy('name', 'asc')->paginate($limit);
        $no = $limit * ($strukturTenagaPendidikan->currentPage() - 1);
        return view('dashboard.tenagapendidikan.struktur_tenaga_pendidikan.index', compact('no','strukturTenagaPendidikan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $strukturTenagaPendidikan = StrukturTenagaPendidikan::orderBy('name', 'asc')->get();

        return view('dashboard.tenagapendidikan.struktur_tenaga_pendidikan.create', compact('strukturTenagaPendidikan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $strukturTenagaPendidikan = StrukturTenagaPendidikan::create([
            'name' => $request->name,
            'struktur_tenaga_pendidikan_id' => $request->struktur_tenaga_pendidikan_id,
        ]);

        return redirect()->route('dashboard.datasekolah.struktur.tenaga.pendidikan.index')->with('success','Berhasil Menambahkan Struktur Tenaga Pendidikan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $slug)
    {
        $struktur = StrukturTenagaPendidikan::where('slug', $slug)->first();
        $strukturTenagaPendidikan = StrukturTenagaPendidikan::orderBy('name', 'asc')->get();
        return view('dashboard.tenagapendidikan.struktur_tenaga_pendidikan.edit', compact('struktur','strukturTenagaPendidikan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $slug)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $strukturTenagaPendidikan = StrukturTenagaPendidikan::where('slug', $slug)->first();

        $strukturTenagaPendidikan->update([
            'name' => $request->name,
            'struktur_tenaga_pendidikan_id' => $request->struktur_tenaga_pendidikan_id,
        ]);

        return redirect()->route('dashboard.datasekolah.struktur.tenaga.pendidikan.index')->with('success','Berhasil Mengubah Struktur Tenaga Pendidikan');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $slug)
    {
        $strukturTenagaPendidikan = StrukturTenagaPendidikan::where('slug', $slug)->first();

        $strukturTenagaPendidikan->delete();

        return redirect()->route('dashboard.datasekolah.struktur.tenaga.pendidikan.index')->with('success','Berhasil Menghapus Struktur Tenaga Pendidikan');
    }
}
