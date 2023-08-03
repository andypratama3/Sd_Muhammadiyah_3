<?php

namespace App\Actions\Dashboard\Role;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleAction
{
    public function execute(Request $request): void
    {
        $role = new Role();
        $role->name = $request->name;
        $role->save();
        $role->permissions()->attach($request->izin_akses);
    }
}
