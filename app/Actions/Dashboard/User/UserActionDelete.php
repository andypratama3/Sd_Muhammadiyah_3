<?php
namespace App\Action\Dashboard\User;

class UserActionDelete 
{
    public function execute($user)
    {
       $user->delete();

       return $user;

    }
}