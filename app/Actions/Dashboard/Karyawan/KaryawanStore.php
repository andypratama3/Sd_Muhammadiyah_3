<?php
namespace App\Actions\Dashboard\Karyawan;
use App\Models\Role;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;


class KaryawanStore
{
    public function execute(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt('12345678');
        $user->avatar = 'profile.jpg';
        $user->save();


        $karyawan = new Karyawan();
        $karyawan->name = $request->name;
        $karyawan->sex = $request->sex;
        $karyawan->phone = $request->phone;
        $karyawan->user_id = $user->id;
        $karyawan->save();

        $role = Role::where('id', $request->role_id)->firstOrFail();
        $user->roles()->attach($role);
        $permissions = $role->permissions->pluck('id');
        $user->permissions()->attach($permissions);
    }
}
