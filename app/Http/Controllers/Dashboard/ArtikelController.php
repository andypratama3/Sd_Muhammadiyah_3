<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index()
    {
        return view('dashboard.artikel.index');
    }
    public function create()
    {
        return view('dashboard.artikel.create');
    }
}
