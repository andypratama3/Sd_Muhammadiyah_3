<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use Illuminate\Http\Request;

class FasilitasController extends Controller
{
    public function index()
    {
        $fasilitas = Fasilitas::select('nama_fasilitas','desc','foto','slug')->firstOrFail();
        return view('profil.fasilitas', compact('fasilitas'));
    }
}
