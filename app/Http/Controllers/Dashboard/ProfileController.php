<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $auth_id = Auth::id();
        $karyawan = Karyawan::where('user_id', $auth_id)->firstOrFail();

        //display role

        return view('dashboard.profile.index',compact('karyawan'));
    }
}
