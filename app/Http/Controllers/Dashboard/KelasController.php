<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KelasController extends Controller
{
    public function index()
    {
        $no = 0;
        $kelass = Kelas::select(['name','slug'])->get();

        return view('dashboard.data.kelas.index', compact('no','kelass'));
    }
}
