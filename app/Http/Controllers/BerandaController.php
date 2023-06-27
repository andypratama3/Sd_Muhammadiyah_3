<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
    public function __invoke()
    {
        $beritas = Berita::select(['judul','desc','foto'])->latest()->get();
        return view('beranda', compact('beritas'));
    }

    public function visi_misi()
    {
        return view('visi&misi');
    }
}
