<?php
namespace App\Actions\Dashboard\Profile;

use App\Models\User;
use App\Models\Karyawan;

class ProfileAction
{
    public function execute($profileData)
    {
        $user = User::find(auth()->id());
        $karyawan = Karyawan::where('user_id', $user->id)->first();
        if($karyawan){
            $karyawan = Karyawan::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $profileData->name,
                    'sex' => $profileData->sex,
                    'phone' => $profileData->phone,
                    'email' => $profileData->email,
                ]);
            }else{
                $user->email = $profileData->email;
                
            }
        $user->name = $profileData->name;
        $user->save();
    }
}
