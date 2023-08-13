<?php

namespace App\Actions\Dashboard\Role;

use App\Models\Role;
use App\Models\User;


class RoleAction
{
    public function execute($RoleData): Role
    {
        $role = Role::updateOrCreate(
            ['slug' => $RoleData->slug],
            [
                'name' => $RoleData->name,
            ],
        );

        if (empty($RoleData->slug)) {
            $role->permissions()->attach($RoleData->permissions);
        } else {
            $role->permissions()->sync($RoleData->permissions);
            $users = User::whereHas('Roles', function ($query) use ($role) {
                $query->where('id', $role->id);
            })->get();

            foreach ($users as $user) {
                $user->permissions()->sync($RoleData->permissions);
            }
        }

        return $role;
    }
}
