<?php

namespace App\Http\Controllers;

use App\Models\Prestasi;

class PrestasiSiswaController extends Controller
{
    public function index()
    {
        $prestasis = Prestasi::where('status', 1)->get();

        return view('profil.prestasi_siswa.index', compact('prestasis'));
    }

    public function show($slug)
    {
        $prestasi = Prestasi::where('slug', $slug)->firstOrFail();

        return view('profil.prestasi_siswa.show', compact('prestasi'));
    }
}
