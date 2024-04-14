<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index()
    {
        $gurus = Guru::all();

        // foreach ($gurus as $guru) {
        //     foreach ($guru->pelajarans as $pelajaran) {
        //         $name_pelajaran = $pelajaran->name;
        //     }
        // }
        return view('profil.guru.guru', compact('gurus'));
    }
}
