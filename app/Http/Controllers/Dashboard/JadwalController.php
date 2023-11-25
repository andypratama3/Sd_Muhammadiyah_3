<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwals = Jadwal::select('id','name','kelas','category_kelas','slug')->get();
        return view('dashboard.data.jadwal.index', compact('jadwals'));
    }
    public function create()
    {
        $kelass = Kelas::select('id','name','category_kelas','slug')->get();
        return view('dashboard.data.jadwal.create', compact('kelass'));
    }
    public function getCategoryKelas(Request $request)
    {
        $kelasId = $request->input('id');
        $kelas = Kelas::find($kelasId);

        $categoryKelas = json_decode($kelas->category_kelas, true);
        sort($categoryKelas);
        return response()->json($categoryKelas);
    }
    

}
