<?php

namespace App\Http\Controllers;

use App\Models\Prestasi;
use Illuminate\Http\Request;

class PrestasiSekolahController extends Controller
{
    public function index()
    {
        $prestasis = Prestasi::where('status', 2)->get();
        return view('profil.prestasi_sekolah.index',compact('prestasis'));
    }
    public function show(Prestasi $prestasi)
    {
        return view('profil.prestasi_sekolah.show', compact('prestasi'));
    }
}
