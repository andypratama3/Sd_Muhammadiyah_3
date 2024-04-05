<?php

namespace App\Actions\Dashboard\User;

class UserActionDelete 
{
    public function execute($user)
    {
       $user->roles()->dettach();
       $user->delete();

       return $user;

    }
}