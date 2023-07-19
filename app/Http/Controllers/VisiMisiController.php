<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;

class VisiMisiController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return view('visi-misi.index');
    }
}
