<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\KategoriPrestasi;
use App\Http\Controllers\Controller;

class KategoriPrestasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $limit = 15;
        $kategoriPrestasi = KategoriPrestasi::select(['id','name'])->paginate($limit);
        $count = $kategoriPrestasi->count();

        $no = $limit * ($kategoriPrestasi->currentPage() - 1);

        return view('dashboard.prestasi.kategori.index', compact(
            'kategoriPrestasi',
            'count',
            'no'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.prestasi.kategori.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $kategoriPrestasi = KategoriPrestasi::create([
            'name' => $request->name
        ]);

        return redirect()->route('dashboard.datasekolah.kategori.prestasi.index')->with('success','Berhasil Menambahkan Kategori Prestasi');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kategoriPrestasi = KategoriPrestasi::find($id);
        return view('dashboard.prestasi.kategori.edit', compact('kategoriPrestasi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $kategoriPrestasi = KategoriPrestasi::find($id);

        $kategoriPrestasi->update([
            'name' => $request->name
        ]);

        return redirect()->route('dashboard.datasekolah.kategori.prestasi.index')->with('success','Berhasil Mengupdate Kategori Prestasi');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kategoriPrestasi = KategoriPrestasi::find($id);
        $action = $kategoriPrestasi->delete();

        if($action){
            return response()->json(['status' => 'success', 'message' => 'Berhasil Menghapus Kategori Prestasi']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Gagal Menghapus Kategori Prestasi']);
        }

    }
}
