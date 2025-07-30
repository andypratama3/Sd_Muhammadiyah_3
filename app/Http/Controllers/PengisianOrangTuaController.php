<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class PengisianOrangTuaController extends Controller
{
    public function index()
    {
        $siswas = Siswa::whereHas('kelas', fn($q) => $q->where('name', '!=', 'Lulus'))
            ->orderBy('name','asc')
            ->get();

        return view('pengisian_orang_tua.nomor_hp', compact('siswas'));
    }

    public function show($nisn)
    {
        $siswa = Siswa::where('nisn', $nisn)->first();

        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'no_hp' => $siswa->no_hp,
            ]
        ]);
    }

    public function update(Request $request, $nisn)
    {
        $request->validate([
            'no_hp' => 'required|string|regex:/^08[0-9]{9,11}$/|unique:siswas,no_hp,' . $nisn . ',nisn',
        ]);

        $siswa = Siswa::where('nisn', $nisn)->firstOrFail();
        $siswa->update(['no_hp' => $request->no_hp]);

        return redirect()->route('pengisian.index')->with('success','Data berhasil diupdate');
    }

}
