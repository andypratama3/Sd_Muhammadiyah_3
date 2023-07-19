<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use App\Models\Berita;

class BerandaController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $beritas = Berita::select(['judul', 'desc', 'foto', 'slug'])->latest()->get();

        return view('beranda', compact(
            'beritas'
        ));
    }
}
