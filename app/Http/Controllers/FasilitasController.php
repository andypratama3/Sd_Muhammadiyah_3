<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use Illuminate\Http\Request;

class FasilitasController extends Controller
{
    public function index()
    {
        $fasilitass = Fasilitas::all();
        return view('profil.fasilitas.index', compact('fasilitass'));
    }
    public function show($nama_fasilitas)
    {
        $fasilitas = Fasilitas::where('nama_fasilitas', $nama_fasilitas)->firstOrFail();
        return view('profil.fasilitas.show', compact('fasilitas'));
    }
}
