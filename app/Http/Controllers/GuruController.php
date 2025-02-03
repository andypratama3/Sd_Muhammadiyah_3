<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index()
    {
        $limit = 15;
        $gurus = Guru::orderBy('name', 'asc')->get();

        return view('profil.guru.guru', compact('gurus'));
    }
}
