<?php

namespace App\Http\Controllers;

use App\Models\Prestasi;
use Illuminate\Http\Request;

class PrestasiController extends Controller
{
    public function index()
    {
        $prestasis = Prestasi::where('status',1)->get();
        return view('profil.prestasi.index', compact('prestasis'));
    }
    public function show(Prestasi $prestasi)
    {
        return view('profil.prestasi.show', compact('prestasi'));
    }
}
