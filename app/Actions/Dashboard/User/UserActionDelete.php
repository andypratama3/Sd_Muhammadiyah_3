<?php

namespace App\Actions\Dashboard\User;

use App\Models\Karywan;
use App\Models\User;
class UserActionDelete
{
    public function execute($user)
    {
       $karyawan = Karyawan::where('user_id', $user->id);
       $user->roles()->detach();
       $user->delete();

       return $user;

    }
}
