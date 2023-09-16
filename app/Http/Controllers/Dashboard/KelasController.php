<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KelasController extends Controller
{
    public function index()
    {
        $kelass = Kelas::select(['id','name','slug'])->get();
        return view('layouts.dashboard.partial.kelas',compact('kelass'));
    }
}
