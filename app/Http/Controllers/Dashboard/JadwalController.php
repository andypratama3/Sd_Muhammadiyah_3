<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\JadwalData;
use App\Actions\Dashboard\Jadwal\JadwalAction;

class JadwalController extends Controller
{
    public function index()
    {
        $no = 0;
        $jadwals = Jadwal::with('kelas_jadwal')->select('id','smester','jadwal','kelas','category_kelas','slug')->get();
        return view('dashboard.data.jadwal.index', compact('no','jadwals'));
    }
    public function create()
    {
        $kelass = Kelas::select('id','name','category_kelas','slug')->orderBy('name')->get();
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

    public function getSmester(Request $request)
    {
        $category_kelas = $request->category_kelas;
        $jadwal = Jadwal::where('category_kelas', $category_kelas)->first();

        return response()->json($jadwal);
    }
    public function store(JadwalData $jadwalData , JadwalAction $jadwalAction)
    {
        $jadwalAction->execute($jadwalData);

        $jadwalAction->execute($jadwalData);
        return redirect()->route('dashboard.datamaster.jadwal.index')->with('success', 'Berhasil Menambahkan Jadwal');
    }

}
