<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Jadwal;
use App\Models\Pelajaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\JadwalData;
use App\Actions\Dashboard\Jadwal\JadwalAction;
use App\Actions\Dashboard\Jadwal\JadwalActionDelete;

class JadwalController extends Controller
{
    public function index()
    {
        /*
            ! ada ada saja
        */
        $no = 0;
        $jadwals = Jadwal::with('kelas_jadwal')->select('id', 'tahun_ajaran', 'jadwal', 'kelas_id', 'category_kelas', 'slug')->orderBy('kelas_id', 'desc')->get();

        return view('dashboard.data.jadwal.index', compact('no', 'jadwals'));
    }

    public function create()
    {
        $kelass = Kelas::select('id', 'name', 'category_kelas', 'slug')->orderBy('name')->get();
        $pelajaran = Pelajaran::orderBy('name', 'asc')->get();
        $guru = Guru::orderBy('name', 'asc')->get();

        return view('dashboard.data.jadwal.create', compact('kelass', 'pelajaran','guru'));
    }

    // public function store(Request $request)
    // {
    //     dd($request->all());

    // }


    public function store(JadwalData $jadwalData, JadwalAction $jadwalAction, Request $request)
    {
        $jadwal = Jadwal::where('kelas_id', $jadwalData->kelas)->where('tahun_ajaran', $jadwalData->tahun_ajaran)->where('category_kelas', $jadwalData->category_kelas)->exists();
        if ($jadwalData != $jadwal) {
            $jadwalAction->execute($jadwalData);
            return redirect()->route('dashboard.datasekolah.jadwal.index')->with('success', 'Berhasil Menambahkan Jadwal');
        } else {
            return redirect()->route('dashboard.datasekolah.jadwal.index')->with('error', 'Jadwal Telah Ada');
        }
    }

    public function show($id)
    {
        $jadwal = Jadwal::where('id', $id)->firstOrFail();
        $kelass = Kelas::all();
        $pelajaran = Pelajaran::orderBy('name','asc')->get();
        $guru = Guru::orderBy('name' ,'asc')->get();

        return view('dashboard.data.jadwal.show', compact('jadwal', 'kelass','pelajaran','guru'));
    }

    public function edit($id)
    {
        $jadwal = Jadwal::where('id', $id)->firstOrFail();
        $kelass = Kelas::all();
        $pelajaran = Pelajaran::orderBy('name','asc')->get();
        $guru = Guru::orderBy('name' ,'asc')->get();

        return view('dashboard.data.jadwal.edit', compact('jadwal', 'kelass','pelajaran','guru'));
    }

    public function update(JadwalData $jadwalData, JadwalAction $jadwalAction)
    {
        $jadwalAction->execute($jadwalData);

        return redirect()->route('dashboard.datasekolah.jadwal.index')->with('success', 'Berhasil Update Jadwal');

    }

    public function destroy(JadwalActionDelete $jadwalActionDelete, $slug)
    {
        $jadwalActionDelete->execute($slug);

        return redirect()->route('dashboard.datasekolah.jadwal.index')->with('success', 'Data Jadwal Berhasil Di Hapus');
    }

    public function getCategoryKelas(Request $request)
    {
        $kelasId = $request->input('id');
        $kelas = Kelas::find($kelasId);

        if (!$kelas) {
            return response()->json(['error' => 'Kelas tidak ditemukan'], 404);
        }

        if ($kelas->name === 'Lulus') {
            $currentYear = date('Y');
            $years = range(2019, $currentYear);
            $categoryKelas = array_map(fn($year) => $year, $years);

            return response()->json($categoryKelas);
        }

        $categoryKelas = json_decode($kelas->category_kelas, true);
        sort($categoryKelas);

        return response()->json($categoryKelas);
    }


    public function getSmester(Request $request)
    {
        $kelas = $request->kelas;
        $category_kelas = $request->category_kelas;

        $existingGenap = Jadwal::where('kelas', $kelas)->where('category_kelas', $category_kelas)->where('tahun_ajaran')->exists();
        $existingGanjil = Jadwal::where('kelas', $kelas)->where('category_kelas', $category_kelas)->where('smester', 'ganjil')->exists();

        $response = [
            'genap' => $existingGenap,
            'ganjil' => $existingGanjil,
        ];

        return response()->json($response);
    }
}
