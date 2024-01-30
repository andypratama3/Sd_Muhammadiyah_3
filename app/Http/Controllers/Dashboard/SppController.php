<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SppController extends Controller
{
    public function index()
    {
        return view('dashboard.data.spp.index');
    }
    public function create()
    {
        return view('dashboard.data.spp.create');
    }
}
