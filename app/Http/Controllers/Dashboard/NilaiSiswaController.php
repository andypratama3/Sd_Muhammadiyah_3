<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Karyawan;
use App\Models\Pelajaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class NilaiSiswaController extends Controller
{
    public function index()
    {
        $no = 0;
        $karyawan = Karyawan::where('user_id', Auth::id())->firstOrFail();
        $guru = Guru::where('karyawan_id', $karyawan->id)->first();

        return view('dashboard.data.nilai.index', compact('no','guru'));
    }
    public function matapelajaran($name)
    {
        $no = 0;
        $pelajaran = Pelajaran::where('name', $name)->firstOrFail();
        $kelass = Kelas::select(['name'])->orderBy('name')->get();
        return view('dashboard.data.nilai.matapelajaran', compact('no','pelajaran','kelass'));
    }
    public function kelas($kelas_name)
    {
        $no = 0;
        $kelas = Kelas::where('name', $kelas_name)->firstOrFail();
        $siswas = Siswa::whereHas('kelas', function($q) use ($kelas) {
            $q->where('kelas_id', $kelas->id);
        })->get();
        return view('dashboard.data.nilai.ganjil', compact('no','siswas','kelas_name'));
    }
    // public function smesterGenap()
    // {
    //     $siswas = Http::get(route('siswa.api'))->json();
    //     dd($siswas->name);

    // }

}
