<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('divisions')->updateOrInsert(
            ['name' => 'HRD'],
            ['description' => 'Human Resource Development', 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('divisions')->updateOrInsert(
            ['name' => 'Operasional'],
            ['description' => 'Divisi operasional perusahaan', 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('divisions')->updateOrInsert(
            ['name' => 'Finance Lama'],
            ['description' => 'Contoh divisi nonaktif', 'is_active' => false, 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('positions')->updateOrInsert(
            ['name' => 'Staff'],
            ['description' => 'Staff umum', 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('positions')->updateOrInsert(
            ['name' => 'Supervisor'],
            ['description' => 'Supervisor operasional', 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('positions')->updateOrInsert(
            ['name' => 'Supervisor Lama'],
            ['description' => 'Contoh jabatan nonaktif', 'is_active' => false, 'updated_at' => $now, 'created_at' => $now]
        );

        $hrdId = DB::table('divisions')->where('name', 'HRD')->value('id');
        $opsId = DB::table('divisions')->where('name', 'Operasional')->value('id');
        $staffId = DB::table('positions')->where('name', 'Staff')->value('id');
        $supervisorId = DB::table('positions')->where('name', 'Supervisor')->value('id');

        $budi = User::updateOrCreate(
            ['username' => 'budi01'],
            [
                'name' => 'Budi Santoso',
                'password' => 'password',
                'role' => 'karyawan',
                'is_active' => true,
            ]
        );

        $siti = User::updateOrCreate(
            ['username' => 'siti01'],
            [
                'name' => 'Siti Rahma',
                'password' => 'password',
                'role' => 'karyawan',
                'is_active' => false,
            ]
        );

        DB::table('employees')->updateOrInsert(
            ['user_id' => $budi->id],
            [
                'employee_number' => 'EMP-001',
                'division_id' => $hrdId,
                'position_id' => $staffId,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('employees')->updateOrInsert(
            ['user_id' => $siti->id],
            [
                'employee_number' => 'EMP-002',
                'division_id' => $opsId,
                'position_id' => $supervisorId,
                'is_active' => false,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $adminId = User::where('username', 'admin')->value('id');

        DB::table('trainings')->updateOrInsert(
            ['title' => 'Training Keselamatan Kerja'],
            [
                'description' => 'Training dasar keselamatan kerja untuk karyawan.',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'status' => 'published',
                'has_pre_test' => true,
                'has_post_test' => true,
                'passing_grade' => 75,
                'allow_post_test_retake' => true,
                'max_post_test_attempt' => 3,
                'show_score_to_employee' => true,
                'created_by' => $adminId,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('trainings')->updateOrInsert(
            ['title' => 'Training Keselamatan Kerja Draft'],
            [
                'description' => 'Draft training untuk persiapan materi.',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
                'status' => 'draft',
                'has_pre_test' => false,
                'has_post_test' => false,
                'passing_grade' => null,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => false,
                'created_by' => $adminId,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('trainings')->updateOrInsert(
            ['title' => 'Training Lama'],
            [
                'description' => 'Contoh training archived.',
                'start_date' => now()->subDays(60)->toDateString(),
                'end_date' => now()->subDays(30)->toDateString(),
                'status' => 'archived',
                'has_pre_test' => false,
                'has_post_test' => false,
                'passing_grade' => null,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => false,
                'created_by' => $adminId,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
