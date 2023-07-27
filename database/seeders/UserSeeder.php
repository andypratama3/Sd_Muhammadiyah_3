<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Karyawan;
use Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Role
        $listRoles = [
            'Superadmin',
            // Add more roles here
        ];

        foreach ($listRoles as $key => $value) {
            $roleName = $value;

            $role[$key] = Role::whereSlug(Str::slug($roleName))->first();

            if (!$role[$key]) {
                $role[$key] = new Role();
                $role[$key]->id = Str::uuid();
                $role[$key]->name = $roleName;
                $role[$key]->save();
            }
        }

        // Task
        $listTasks = [
            [
                'name' => 'Task',
                'description' => 'Manajemen Task',
            ],
            [
                'name' => 'Role',
                'description' => 'Manajemen Role',
            ],
            [
                'name' => 'Karyawan',
                'description' => 'Manajemen Karyawan',
            ],
            [
                'name' => 'Slider',
                'description' => 'Manajemen Slider',
            ],
        ];

        foreach ($listTasks as $key => $value) {
            $name = $value['name'];
            $description = $value['description'];

            $task[$key] = Task::whereSlug(Str::slug($name))->first();

            if (!$task[$key]) {
                $task[$key] = new Task();
                $task[$key]->id = Str::uuid();
                $task[$key]->name = $name;
                $task[$key]->description = $description;
                $task[$key]->save();
            }
        }

        $listTasks = Task::all();

        // Permission
        foreach ($listTasks as $task) {
            $name = $task->name;
            $listPermissions = array(
                [
                    'id' => Str::uuid(),
                    'name' => 'View ' . $name,
                    'task_id' => $task->id
                ],
                [
                    'id' => Str::uuid(),
                    'name' => 'Create ' . $name,
                    'task_id' => $task->id
                ],
                [
                    'id' => Str::uuid(),
                    'name' => 'Edit ' . $name,
                    'task_id' => $task->id
                ],
                [
                    'id' => Str::uuid(),
                    'name' => 'Delete ' . $name,
                    'task_id' => $task->id
                ],
            );

            foreach ($listPermissions as $listPermission) {
                $createPermissions = Permission::Create($listPermission);
            }
        }

        // User
        $roleSuperAdmin = Role::where('slug', 'superadmin')->first();
        // Call more role_id here

        // List User
        $listUsers = [
            [
                'name' => 'Superadmin',
                'email' => 'superadmin@superadmin.com',
                'nip' => '0000',
                'password' => bcrypt('superadmin'),
                'role_id' => $roleSuperAdmin->id,
            ],
            // Add another user here
        ];

        foreach ($listUsers as $key => $value) {
            $name = $value['name'];
            $email = $value['email'];
            $nip = $value['nip'];
            $avatar = "profile.jpg";
            $password = $value['password'];
            $role_id = $value['role_id'];

            // User
            $user[$key] = User::whereEmail($email)->first();

            if (!$user[$key]) {
                // User
                $user[$key] = new User();
                $user[$key]->id = Str::uuid();
                $user[$key]->name = $name;
                $user[$key]->email = $email;
                $user[$key]->password = $password;
                $user[$key]->avatar = $avatar;
                $user[$key]->slug = Str::slug('superadmin');
                $user[$key]->save();
                $user[$key]->roles()->attach($role_id);

                // Karyawan
                $karyawan[$key] = Karyawan::where('user_id', $user[$key]->id)->first();
                $karyawan[$key] = new Karyawan();
                $karyawan[$key]->id = Str::uuid();
                $karyawan[$key]->name = $name;
                $karyawan[$key]->slug = Str::slug('superadmin-xxxx');
                $karyawan[$key]->user_id = $user[$key]->id;
                $karyawan[$key]->save();
            }
        }
    }
}
