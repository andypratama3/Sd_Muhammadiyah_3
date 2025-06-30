<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class ProfilSekolahController extends Controller
{
    public function index()
    {
        $siswas = Siswa::whereHas('kelas', function($q) {
            $q->where('name','!=','Lulus');
        })->count();

        $alumni = Siswa::whereHas('kelas', function($q) {
            $q->where('name','Lulus');
        })->count();

        // count pengalaman from now
        $pengalaman = date('Y') - 1979;



        return view('sejarah', compact('siswas', 'pengalaman', 'alumni'));
    }
}
