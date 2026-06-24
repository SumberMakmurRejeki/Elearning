<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Training',
                'email' => 'admin@elearning.local',
                'password' => 'password',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
