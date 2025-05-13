<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AttendancesController extends Controller
{
    public function index()
    {
        $kelas = Kelas::orderBy("name","asc")->get();

        $siswas = Siswa::all();
        $attendances = Attendances::all();

        return view('dashboard.attendances.index', compact('kelas'));
    }

    
}
