<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class PengisianOrangTuaController extends Controller
{
    public function index()
    {
        $siswas = Siswa::whereHas('kelas', function ($q) {
            $q->where('name', '!=', 'Lulus');
        })
        ->orderBy('name','desc')
        ->get();

        return view('pengisian_orang_tua.nomor_hp', compact('siswas'));
    }

    public function update(Request $request,$nisn)
    {

        $request->validate([
            'nisn' => 'required|string|exists:siswas,nisn',
            'no_hp' => 'required|integer'
        ]);

        $siswa = Siswa::where('nisn', $siswa)->firstOrFail();

        $siswa->no_hp = $request->no_hp;
        $siswa->save();


        return redirect('pengisian-orang-tua')->with('success','Berhasil Mengubah Nomor HP Orang Tua');

    }
}
