<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FotoSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoSekolahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fotoSekolah = FotoSekolah::all();
        return view('dashboard.data.foto_sekolah.index', compact('fotoSekolah'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.data.foto_sekolah.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('foto_sekolah', 'public');
        }

        FotoSekolah::create([
            'name' => $request->name,
            'foto' => $fotoPath,
        ]);

        return redirect()->route('dashboard.datasekolah.foto_sekolah.index')
            ->with('success', 'Foto sekolah berhasil ditambahkan');
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
        $fotoSekolah = FotoSekolah::findOrFail($id);
        return view('dashboard.data.foto_sekolah.edit', compact('fotoSekolah'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $fotoSekolah = FotoSekolah::findOrFail($id);

        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($fotoSekolah->foto && Storage::disk('public')->exists($fotoSekolah->foto)) {
                Storage::disk('public')->delete($fotoSekolah->foto);
            }
            // Upload foto baru
            $fotoPath = $request->file('foto')->store('foto_sekolah', 'public');
            $fotoSekolah->foto = $fotoPath;
        }

        $fotoSekolah->name = $request->name;
        $fotoSekolah->save();

        return redirect()->route('dashboard.datasekolah.foto_sekolah.index')
            ->with('success', 'Foto sekolah berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $fotoSekolah = FotoSekolah::findOrFail($id);

        if ($fotoSekolah->foto && Storage::disk('public')->exists($fotoSekolah->foto)) {
            Storage::disk('public')->delete($fotoSekolah->foto);
        }

        $fotoSekolah->delete();

        return redirect()->route('dashboard.datasekolah.foto_sekolah.index')
            ->with('success', 'Foto sekolah berhasil dihapus');
    }
}
