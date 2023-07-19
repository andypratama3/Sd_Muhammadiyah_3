<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRolePermissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //role permissions Seed

        $users = User::create([
            'email' => 'Admin@gmail.com',
            'username' => ''
        ]);

    }
}
