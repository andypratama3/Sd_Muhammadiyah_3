<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SiswaController extends Controller
{
    public function index()
    {
        return view('dashboard.data.siswa.index');
    }

}
