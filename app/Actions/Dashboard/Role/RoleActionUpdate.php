<?php

namespace App\Actions\Dashboard\Role;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;

class RoleActionUpdate
{
    public function execute(Request $request, $role): void
    {
        $role->name = $request->name;
        $role->save();
        $role->permissions()->sync($request->izin_akses);
        $users = User::whereHas('Roles', function ($query) use ($role) {
            $query->where('id', $role->id);
        })->get();

        foreach ($users as $user) {
            $user->permissions()->sync($request->izin_akses);
        }
    }
}
