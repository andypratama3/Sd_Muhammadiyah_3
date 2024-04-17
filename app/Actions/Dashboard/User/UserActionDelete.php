<?php
namespace App\Actions\Dashboard\User;

use App\Models\User;
use App\Models\Karyawan;
class UserActionDelete
{
    public function execute($user)
    {
       $karyawn = Karyawan::where('user_id', $user->id)->first();
       $user->roles()->detach();
       $user->delete();
       return $user;

    }
}
