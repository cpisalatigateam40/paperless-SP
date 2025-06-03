<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $area = Area::first();

        $admin = User::firstOrCreate([
            'uuid' => (string) Str::uuid(),
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('123456'),
            'area_uuid' => $area->uuid,
        ]);
        $admin->assignRole($adminRole);

        $user = User::firstOrCreate([
            'uuid' => (string) Str::uuid(),
            'username' => 'user',
            'name' => 'User One',
            'email' => 'user@example.com',
            'password' => bcrypt('123456'),
            'area_uuid' => $area->uuid,
        ]);
        $user->assignRole($userRole);
    }
}