<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\DataTransferObjects\ProfileData;
use App\Actions\Dashboard\Profile\ProfileAction;
use App\Actions\Dashboard\Profile\ProfileActionCrop;

class ProfileController extends Controller
{
    public function index()
    {
        $auth_id = Auth::id();
        if($auth_id){
            $karyawan = Karyawan::where('user_id', $auth_id)->first();
        }else{
            $user = User::where('id', $auth_id)->first();
        }

        return view('dashboard.profile.index',compact('karyawan'));
    }
    public function update(ProfileData $profileData, ProfileAction $profileAction)
    {
        $profileAction->execute($profileData);
        return redirect()->route('dashboard.pengaturan.profile.index')->with('success', 'Profile Berhasil Di Update');
    }
    public function crop_image(Request $request, ProfileActionCrop $profileActionCrop)
    {
        $profileActionCrop->execute($request);
        return response()->json(['success' => 'Berhasil mengganti foto profil']);
    }
    public function removeAvatar(Request $request)
    {
        $user = auth()->user();
        Storage::delete('storage/img/profile/' . $user->avatar);

        $user->avatar = 'default.jpg';
        $user->save();
        return response()->json(['message' => 'Profile image deleted successfully']);
    }
}
