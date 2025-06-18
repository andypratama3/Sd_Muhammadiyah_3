<?php

namespace App\Http\Controllers;

class KontakController extends Controller
{
    public function index()
    {
        return view('kontak');
    }

    public function success()
    {
        return view('keritiksaran-success');
    }
}
