<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaLulusController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::where('name', 'Lulus')->first();

        $siswas = Siswa::with(['kelas' => function ($q) {
            $q->withPivot('category_kelas');
        }])
        ->whereHas('kelas', function ($q) use ($kelas) {
            $q->where('kelas.id', $kelas->id);
        });

        if ($request->filled('tahun')) {
            $siswas->whereHas('kelas', function ($q) use ($request) {
                $q->where('siswa_kelas.category_kelas', $request->tahun);
            });
        }

        $siswas = $siswas->get();

        $siswas = $siswas->groupBy(function ($siswa) {
            return optional($siswa->kelas->first())->pivot->category_kelas;
        });

        return view('siswa_lulus', compact('siswas'));
    }



}
