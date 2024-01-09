<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\JadwalData;
use App\Actions\Dashboard\Jadwal\JadwalAction;
use App\Actions\Dashboard\Jadwal\JadwalActionDelete;

class JadwalController extends Controller
{
    public function index()
    {
        $no = 0;
        $jadwals = Jadwal::with('kelas_jadwal')->select('id','tahun_ajaran','jadwal','kelas','category_kelas','slug')->orderBy('kelas', 'desc')->get();
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
        $kelas = $request->kelas;
        $category_kelas = $request->category_kelas;

        $existingGenap = Jadwal::where('kelas', $kelas)->where('category_kelas', $category_kelas)->where('tahun_ajaran',)->exists();
        $existingGanjil = Jadwal::where('kelas', $kelas)->where('category_kelas', $category_kelas)->where('smester', 'ganjil')->exists();

        $response = [
            'genap' => $existingGenap,
            'ganjil' => $existingGanjil,
        ];

        return response()->json($response);
    }

    public function store(JadwalData $jadwalData , JadwalAction $jadwalAction)
    {
        $jadwalAction->execute($jadwalData);
        return redirect()->route('dashboard.datamaster.jadwal.index')->with('success', 'Berhasil Menambahkan Jadwal');
    }
    public function edit(Jadwal $jadwal)
    {
        $kelass = Kelas::all();
        return view('dashboard.data.jadwal.edit', compact('jadwal', 'kelass'));
    }
    public function update(JadwalData $jadwalData, JadwalAction $jadwalAction)
    {
        $jadwalAction->execute($jadwalData);
        return redirect()->route('dashboard.datamaster.jadwal.index')->with('success', 'Berhasil Update Jadwal');

    }
    public function destroy(JadwalActionDelete $jadwalActionDelete,$slug)
    {
        $jadwalActionDelete->execute($slug);
        return redirect()->route('dashboard.datamaster.jadwal.index')->with('success', 'Data Jadwal Berhasil Di Hapus');
    }

}
