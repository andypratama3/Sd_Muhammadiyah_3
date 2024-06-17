<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index()
    {
        $gurus = Guru::orderBy('name', 'asc')->paginate(10);
        
        return view('profil.guru.guru', compact('gurus'));
    }
}
