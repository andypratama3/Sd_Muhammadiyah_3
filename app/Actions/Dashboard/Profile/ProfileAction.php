<?php
namespace App\Actions\Dashboard\Profile;

use App\Models\User;
use App\Models\Karyawan;

class ProfileAction
{
    public function execute($profileData)
    {

        // 'name',
        // 'sex',
        // 'phone',
        // 'slug',
        // 'user_id',
        $user = User::find(auth()->id());
        $karyawan = Karyawan::where('user_id', $user->id)->firstOrFail();

        $karyawan = Karyawan::updateOrCreate(
            ['slug' => $profileData->slug],
            [
                'name' => $profileData->name,
                'sex' => $profileData->sex,
                'phone' => $profileData->phone,

            ]);
        $user->name = $profileData->name;
        $user->save();
    }
}
