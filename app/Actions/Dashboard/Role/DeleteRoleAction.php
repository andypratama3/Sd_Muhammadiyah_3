<?php

namespace App\Actions\Dashboard\Role;

class DeleteRoleAction
{
    public function execute($role)
    {
        $role->delete();
    }
}
