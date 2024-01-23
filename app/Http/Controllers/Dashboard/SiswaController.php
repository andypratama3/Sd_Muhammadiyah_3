<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class SiswaController extends Controller
{
    public function index()
    {
        return view('dashboard.data.siswa.index');
    }
    public function create()
    {
        $provinces = Http::get('https://emsifa.github.io/api-wilayah-indonesia/api/regencies/64.json')->json();
        $kelass = Kelas::all();
        return view('dashboard.data.siswa.create', compact('kelass','provinces'));
    }

}
