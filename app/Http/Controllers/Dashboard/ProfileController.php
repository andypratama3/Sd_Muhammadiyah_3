<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Dashboard\Profile\ProfileAction;
use App\Actions\Dashboard\Profile\ProfileActionUpload;
use App\DataTransferObjects\ProfileData;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $auth_id = Auth::id();
        if ($auth_id) {
            $karyawan = Karyawan::where('user_id', $auth_id)->first();
        } else {
            $user = User::where('id', $auth_id)->first();
        }

        return view('dashboard.profile.index', compact('karyawan'));
    }

    public function update(ProfileData $profileData, ProfileAction $profileAction)
    {
        $profileAction->execute($profileData);

        return redirect()->route('dashboard.pengaturan.profile.index')->with('success', 'Profile Berhasil Di Update');
    }

    public function upload_image(Request $request, ProfileActionUpload $profileActionUpload)
    {
        $profileActionUpload->execute($request);

        return response()->json(['success' => 'Berhasil mengganti foto profil']);
    }

    public function removeAvatar(Request $request)
    {
        $user = auth()->user();

        if ($user->avatar !== 'default.jpg') {
            Storage::delete('public/img/profile/' . $user->avatar);
        }

        $user->avatar = 'default.jpg';
        $user->save();

        return response()->json(['message' => 'Profile image deleted successfully']);
    }
}
