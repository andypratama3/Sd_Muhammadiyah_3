<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TenagaPendidikan;

class TenagaPendidikanController extends Controller
{
    public function index()
    {
        $datas = TenagaPendidikan::all();
        return view('profil.tenagapendidikan.index', compact('datas'));
    }
}
