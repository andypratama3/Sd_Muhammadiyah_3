<?php

namespace App\Actions\Dashboard\User;

class UserActionDelete 
{
    public function execute($user)
    {
       $user->delete();

       return $user;

    }
}