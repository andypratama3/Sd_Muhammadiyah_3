<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Jadwal;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $kelass = Kelas::select(['name'])->orderBy('name','asc')->get();
        $jadwals = Jadwal::all();
        return view('jadwal.index', compact('kelass','jadwals'));
    }
    public function show($kelas)
    {
        $kelass = Kelas::where('name', $kelas)->first();
        $category_kelas = json_decode($kelass->category_kelas);
        return view('jadwal.show', compact('kelass', 'category_kelas'));
    }
    public function tahun_ajaran(Request $request)
    {
        $kelas = $request->kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        $category_kelas = $request->category_kelas;

        $kelas = Jadwal::where('kelas', $kelas)->where('tahun_ajaran', $tahun_ajaran)->where('category_kelas', $category_kelas)->first();

        if($kelas){
            return response()->json($kelas);
        }else{
            return response()->json(['message' => 'Tidak Ada Data Jadwal']);

        }
    }
}
