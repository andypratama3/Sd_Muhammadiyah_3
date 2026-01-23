<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use Illuminate\Http\Request;
use App\Models\LokasiAbsensi;
use App\Http\Controllers\Controller;

class LokasiAbsenController extends Controller
{
    public function index()
    {
        $lokasiAbsensi = LokasiAbsensi::all();
        return view('dashboard.absensis.lokasi_absen.index', compact('lokasiAbsensi'));
    }

    public function create()
    {
        return view('dashboard.absensis.lokasi_absen.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'radius'      => 'required|integer|min:1',
            'alamat'      => 'required|string',
            'status'      => 'required|in:aktif,nonaktif',
        ]);

        LokasiAbsensi::create($validated);

        return redirect()
            ->route('dashboard.lokasi.absen.index')
            ->with('success', 'Lokasi absensi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $lokasiAbsensi = LokasiAbsensi::findOrFail($id);
        return view('dashboard.absensis.lokasi_absen.edit',compact('lokasiAbsensi'));
    }

    public function update(Request $request, LokasiAbsensi $lokasiAbsensi)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'radius'      => 'required|integer|min:1',
            'alamat'      => 'required|string',
            'status'      => 'required|in:aktif,nonaktif',
        ]);

        $lokasiAbsensi->update($validated);

        return redirect()
            ->route('dashboard.lokasi.absen.index')
            ->with('success', 'Lokasi absensi berhasil diperbarui');
    }

    public function destroy($id)
    {
        LokasiAbsensi::findOrFail($id)->delete();
        
        return redirect()
            ->route('dashboard.lokasi.absen.index')
            ->with('success', 'Lokasi absensi berhasil dihapus');
    }
}
