<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $auth_id = Auth::id();
        if(!$auth_id){
            $karyawan = Karyawan::where('user_id', $auth_id)->firstOrFail();
        }else{
            $karyawan = User::where('id', $auth_id)->first();
        }
        return view('dashboard.profile.index',compact('karyawan'));
    }
    public function update(ProfileData $profileData, ProfileAction $profileAction)
    {
        $profileAction->execute($profileData);
    }
}
