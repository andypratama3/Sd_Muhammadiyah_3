<?php

namespace App\Http\Controllers;

use App\Models\TenagaPendidikan;

class TenagaPendidikanController extends Controller
{
    public function index()
    {
        $tenagakependidikans = TenagaPendidikan::all();

        return view('profil.tenagapendidikan.index', compact('tenagakependidikans'));
    }
}
