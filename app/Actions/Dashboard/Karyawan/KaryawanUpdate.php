<?php
namespace App\Actions\Dashboard\Karyawan;
use App\Models\Role;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;


class KaryawanUpdate
{
    public function execute($karyawanData): void
    {
        $karyawan = Karyawan::where('slug', '!=', 'superadmin-xxxx')->where('slug', $karyawanData->slug)->firstOrFail();
        $user = User::where('slug', '!=', 'superadmin')->where('id', $karyawan->user_id)->firstOrFail();

        $user->name = $karyawanData->name;
        $user->email = $karyawanData->email;
        $user->save();

        $role = Role::where('id', $karyawanData->role_id)->firstOrFail();
        $user->roles()->sync($role->id);
        $permissions = $role->permissions->pluck('id');
        $user->permissions()->sync($permissions);

        $karyawan->name = $karyawanData->name;
        $karyawan->save();
    }
}
