<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = ['customer', 'rider', 'restaurant', 'admin'];
        $guards = ['web', 'api'];

        foreach ($guards as $guard) {
            foreach ($roles as $role) {
                Role::firstOrCreate([
                    'name' => $role,
                    'guard_name' => $guard,
                ]);
            }
        }
    }
}
