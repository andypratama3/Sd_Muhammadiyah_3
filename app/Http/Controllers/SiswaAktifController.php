<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaAktifController extends Controller
{
    public function index(Request $request)
    {
        $siswas = Siswa::whereHas('kelas', function ($q) {
            $q->where('name','!=','Lulus');
        });

        if ($request->has('tahun') && $request->tahun !== null) {
            $siswas->where('kelas_tahun', $request->tahun);
        }

        $siswas = $siswas->with('kelas')->get()->groupBy('kelas_tahun');

        return view('siswa_aktif', compact('siswas'));
    }
}
