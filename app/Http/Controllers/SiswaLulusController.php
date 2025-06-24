<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaLulusController extends Controller
{
    public function index()
    {
        $siswas = Siswa::all();

        // group siswa by tahun lulus
        // $siswas = $siswas->whereHas('kelas', function ($q) {
        //     $q->where('name', 'Lulus');
        // })->groupBy('kelas_tahun')
        // ->get();

        return view('siswa_lulus', compact('siswas'));
    }

    
}
