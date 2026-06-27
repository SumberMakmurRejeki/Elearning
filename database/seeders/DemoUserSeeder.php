<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Divisi
        DB::table('divisions')->upsert([
            ['name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'IT', 'description' => 'Information Technology', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['description', 'is_active', 'updated_at']);

        // Jabatan
        DB::table('positions')->upsert([
            ['name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manager', 'description' => 'Manager', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['description', 'is_active', 'updated_at']);

        // Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Training',
                'email' => 'admin@demo.local',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Karyawan
        $karyawan = User::updateOrCreate(
            ['username' => 'budi'],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@demo.local',
                'password' => Hash::make('password'),
                'role' => 'karyawan',
                'is_active' => true,
            ]
        );

        $hrd = DB::table('divisions')->where('name', 'HRD')->first();
        $staff = DB::table('positions')->where('name', 'Staff')->first();

        if ($hrd && $staff) {
            DB::table('employees')->upsert([
                [
                    'user_id' => $karyawan->id,
                    'employee_number' => 'EMP-001',
                    'division_id' => $hrd->id,
                    'position_id' => $staff->id,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ], ['user_id'], ['employee_number', 'division_id', 'position_id', 'is_active', 'updated_at']);
        }
    }
}
