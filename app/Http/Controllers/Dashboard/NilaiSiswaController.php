<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Karyawan;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class NilaiSiswaController extends Controller
{
    public function index()
    {
        // $no = 0;
        // $karyawan = Karyawan::where('user_id', Auth::id())->firstOrFail();
        // $guru = Guru::where('karyawan_id', $karyawan->id)->first();

        // return view('dashboard.data.nilai.index', compact('no', 'guru'));
        return view('dashboard.data.nilai.index');
    }

    public function data_table(Request $request)
    {
        $nilai = Nilai::all();
    }

    public function matapelajaran($name)
    {
        $no = 0;
        $pelajaran = Pelajaran::where('name', $name)->firstOrFail();
        $kelass = Kelas::select(['name'])->orderBy('name')->get();

        return view('dashboard.data.nilai.matapelajaran', compact('no', 'pelajaran', 'kelass'));
    }

    public function kelas($kelas_name)
    {
        $no = 0;
        $kelas = Kelas::where('name', $kelas_name)->firstOrFail();
        $siswas = Siswa::whereHas('kelas', function ($q) use ($kelas) {
            $q->where('kelas_id', $kelas->id);
        })->get();

        return view('dashboard.data.nilai.ganjil', compact('no', 'siswas', 'kelas_name'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required',
            'pelajaran_id' => 'required',
            'nilai_tugas' => 'required|numeric|min:0|max:100',
            'nilai_uts' => 'required|numeric|min:0|max:100',
            'nilai_uas' => 'required|numeric|min:0|max:100',
            'semester' => 'required|in:ganjil,genap',
        ]);
    }

    public function show($id)
    {
        $nilai = Nilai::findOrFail($id);
        return view('dashboard.data.nilai.show', compact('nilai'));
    }

    public function edit($id)
    {
        $nilai = Nilai::findOrFail($id);
        return view('dashboard.data.nilai.edit', compact('nilai'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nilai_tugas' => 'required|numeric|min:0|max:100',
            'nilai_uts' => 'required|numeric|min:0|max:100',
            'nilai_uas' => 'required|numeric|min:0|max:100',
        ]);

        $nilai = Nilai::findOrFail($id);
        $nilai->update([
            'nilai_tugas' => $request->nilai_tugas,
            'nilai_uts' => $request->nilai_uts,
            'nilai_uas' => $request->nilai_uas,
        ]);

        return redirect()->route('dashboard.nilai.show', $nilai->id)->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $nilai = Nilai::findOrFail($id);
        $action = $nilai->delete();

        if($action) {
            return response()->json(['status' => 'success', 'message' => 'Nilai berhasil dihapus.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus nilai.']);
        }
    }

    public function exporExcel()
    {
        // Implementasi ekspor ke Excel
        
        return response()->json(['status' => 'success', 'message' => 'Ekspor Excel berhasil.']);
    }

}
