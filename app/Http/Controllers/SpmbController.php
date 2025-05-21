<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SpmbController extends Controller
{
    public function index()
    {
        // return view('spmb.index');
        return view('spmb.comming_soon');
    }


    public function store(Request $request)
    {
        $request->validate([

        ]);
    }
}
