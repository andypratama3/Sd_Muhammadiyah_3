<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $listPermissions = [
            'view-pengaturan',
            'manage-pengaturan',
            'webhook',
            'view-siswa',
            'manage-siswa',
            'view-data-sekolah',
            'manage-data-sekolah',
            'view-pembayaran',
            'manage-pembayaran',
            'nilai-view',
            'nilai-manage',
            'absensi-view',
            'absensi-manage',
        ];

        $listRoles = [
            'superadmin',
            'user',
            'media',
            'tenaga-pendidikan',
            'guru',
            'siswa',
        ];

        foreach ($listPermissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach ($listRoles as $role) {
            Role::create([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $superadmin = User::create([
            'id' => Str::uuid(),
            'name' => 'Super Admin',
            'email' => 'superadmin@superadmin.com',
            'password' => bcrypt('Superadmin1211'),
            'avatar' => 'profile.jpg',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $roleSuperadmin = Role::where('name', 'superadmin')->first();
        $superadmin->assignRole($roleSuperadmin);

        $this->command->info('User seeder completed successfully!');
    }
}
