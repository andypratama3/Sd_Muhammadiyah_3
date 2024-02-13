<?php
namespace App\Actions\Dashboard\Profile;

use App\Models\User;
use App\Models\Karyawan;

class ProfileAction
{
    public function execute($profileData)
    {
        $user = User::find(auth()->id());
        $karyawan = Karyawan::where('user_id', $user->id)->firstOrFail();

        $user->name = $profileData->name;
        $user->email = $profileData->email;
        $user->save();

        $karyawan->name = $profileData->name;
        $karyawan->save();
    }
}
