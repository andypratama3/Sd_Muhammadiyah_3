<?php
namespace App\Actions\Dashboard\Karyawan;

use App\Models\Role;
use App\Models\User;
use App\Models\Karyawan;

class ActionKaryawan
{
    public function execute($karyawanData)
    {
        /*
            todo User Action
        **/
        // handle foto for user when update
        $userData = [
            'name' => $karyawanData->name,
            'email' => $karyawanData->email,
            'nip' => $karyawanData->nip,
            'password' => bcrypt('sdmuhammadiyah3samarinda.com'),
            'avatar' => 'profile.jpg',

        ];

        $user = User::where('id', $karyawanData->user_id)->first();
        if($user){
            $user->update($userData);
        }else{
            $user = User::create($userData);
        }

         /*
            todo Karyawan Action
        **/
        $karyawan = Karyawan::updateOrcreate(
            [ 'slug' => $karyawanData->slug ],
            [
                'name' => $karyawanData->name,
                'sex' => $karyawanData->sex,
                'phone' => $karyawanData->phone,
                'email'=> $karyawanData->email,
                'nip' => $karyawanData->nip,
                'phone' => $karyawanData->phone,
                'user_id' => $user->id,
            ]
        );

        if(empty($karyawanData->slug)){
            $user->roles()->attach($karyawanData->role_id);
        }else{
            $user->roles()->sync($karyawanData->role_id);
        }

    }

}
