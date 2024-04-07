<?php

namespace App\Actions\Dashboard\User;

class UserActionDelete 
{
    public function execute($user)
    {
       dd($user);
       $karyawan = Karyawan::where('user_id', $user->id);
       $user->roles()->detach();
       $user->delete();

       return $user;

    }
}