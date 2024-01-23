<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NilaiSiswaController extends Controller
{
    public function index()
    {
        $no = 0;
        $karyawan = Karyawan::where('user_id', Auth::id())->firstOrFail();
        $guru = Guru::where('karyawan_id', $karyawan->id)->first();

        return view('dashboard.data.nilai.index', compact('no','guru'));
    }
    public function matapelajaran()
    {
        return view('dashboard.data.nilaisiswa.index');
    }
}
