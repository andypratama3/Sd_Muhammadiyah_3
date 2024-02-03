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

        $user = new User();
        $user->name = $karyawanData->name;
        $user->email = $karyawanData->email;
        $user->password = bcrypt('12345678');
        $user->avatar = 'profile.jpg';
        $user->save();

         /*
            todo Karyawan Action
        **/
        $karyawan = Karyawan::updateOrcreate(
            [ 'slug' => $karyawanData->slug ],
            [
                'name' => $karyawanData->name,
                'sex' => $karyawanData->sex,
                'phone' => $karyawanData->phone,
                'user_id' => $user->id,
            ]
        );
        
        $roles = Role::findOrFail($karyawanData->role_id);
        $permissionsData = $roles->permissions->pluck('id');
        if(empty($karyawanData->slug)){
            $user->roles()->attach($roles);
            $user->permissions()->attach($permissionsData->toArray());
        }else{
            $user->roles()->sync([$roles->id]);
            $user->permissions()->sync($permissionsData->toArray());
        }

    }

}
