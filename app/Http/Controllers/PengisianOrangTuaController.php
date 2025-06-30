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
}
