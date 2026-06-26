<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeTrainingTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_open_training_index_and_see_only_own_assigned_trainings(): void
    {
        [$employeeUser] = $this->seedTrainingData();

        $this->actingAs($employeeUser)
            ->get(route('employee.training.index'))
            ->assertOk()
            ->assertSeeText('Training Saya')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Training Kepemimpinan')
            ->assertSeeText('Training Komunikasi Dasar')
            ->assertDontSeeText('Training Audit Internal');
    }

    public function test_employee_can_search_and_filter_own_trainings(): void
    {
        [$employeeUser] = $this->seedTrainingData();

        $this->actingAs($employeeUser)
            ->get(route('employee.training.index', ['q' => 'Komunikasi']))
            ->assertOk()
            ->assertSeeText('Training Komunikasi Dasar')
            ->assertDontSeeText('Training Keselamatan Kerja');

        $this->actingAs($employeeUser)
            ->get(route('employee.training.index', ['status' => 'passed']))
            ->assertOk()
            ->assertSeeText('Training Kepemimpinan')
            ->assertDontSeeText('Training Keselamatan Kerja');
    }

    public function test_employee_training_index_shows_empty_state_when_no_assignment_exists(): void
    {
        $employeeUser = User::factory()->create([
            'name' => 'Karyawan Kosong',
            'username' => 'karyawan-kosong-training',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $now = Carbon::now();
        DB::table('divisions')->insert([
            'id' => 1,
            'name' => 'HRD',
            'description' => 'Divisi HRD',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('positions')->insert([
            'id' => 1,
            'name' => 'Staff',
            'description' => 'Staff umum',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('employees')->insert([
            'user_id' => $employeeUser->id,
            'employee_number' => 'EMP-999',
            'division_id' => 1,
            'position_id' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->actingAs($employeeUser)
            ->get(route('employee.training.index'))
            ->assertOk()
            ->assertSeeText('Belum ada training yang ditugaskan.')
            ->assertSeeText('Training yang Anda miliki akan muncul di sini setelah admin membuat penugasan.');
    }

    public function test_employee_can_open_owned_training_detail_and_see_progress_information(): void
    {
        [$employeeUser] = $this->seedTrainingData();

        $this->actingAs($employeeUser)
            ->get(route('employee.training.show', ['training' => 1]))
            ->assertOk()
            ->assertSeeText('Detail Training')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Pre-Test Aktif')
            ->assertSeeText('Materi Aktif')
            ->assertSeeText('Post-Test Aktif')
            ->assertSeeText('Mulai Pre-Test');
    }

    public function test_employee_detail_returns_404_for_other_employee_training(): void
    {
        [$employeeUser] = $this->seedTrainingData();

        $this->actingAs($employeeUser)
            ->get(route('employee.training.show', ['training' => 3]))
            ->assertNotFound();
    }

    public function test_admin_cannot_open_employee_training_pages(): void
    {
        [$employeeUser, $adminUser] = $this->seedTrainingData();

        $this->actingAs($adminUser)
            ->get(route('employee.training.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($adminUser)
            ->get(route('employee.training.show', ['training' => 1]))
            ->assertRedirect(route('admin.dashboard'));
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedTrainingData(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Operasional', 'description' => 'Divisi operasional perusahaan', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeOne = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-training-flow',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeTwo = User::factory()->create([
            'name' => 'Sari Wulandari',
            'username' => 'sari-training-flow',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-training-flow',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeOne->id, 'employee_number' => 'EMP-100', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'user_id' => $employeeTwo->id, 'employee_number' => 'EMP-101', 'division_id' => 2, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 1,
                'title' => 'Training Keselamatan Kerja',
                'description' => 'Training inti bulan ini',
                'start_date' => $now->copy()->subDays(5)->toDateString(),
                'end_date' => $now->copy()->addDays(10)->toDateString(),
                'status' => 'published',
                'has_pre_test' => true,
                'has_post_test' => true,
                'passing_grade' => 75,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => true,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'title' => 'Training Kepemimpinan',
                'description' => 'Training kepemimpinan tim',
                'start_date' => $now->copy()->subDays(20)->toDateString(),
                'end_date' => $now->copy()->subDays(1)->toDateString(),
                'status' => 'archived',
                'has_pre_test' => false,
                'has_post_test' => false,
                'passing_grade' => null,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => false,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'title' => 'Training Audit Internal',
                'description' => 'Training hanya untuk employee lain',
                'start_date' => $now->copy()->subDays(2)->toDateString(),
                'end_date' => $now->copy()->addDays(20)->toDateString(),
                'status' => 'published',
                'has_pre_test' => false,
                'has_post_test' => false,
                'passing_grade' => null,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => false,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'title' => 'Training Komunikasi Dasar',
                'description' => 'Belum memiliki progress tetapi sudah ditugaskan',
                'start_date' => $now->copy()->subDays(1)->toDateString(),
                'end_date' => $now->copy()->addDays(14)->toDateString(),
                'status' => 'published',
                'has_pre_test' => false,
                'has_post_test' => false,
                'passing_grade' => null,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => false,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('training_assignments')->insert([
            ['id' => 1, 'training_id' => 1, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(3)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 2, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(15)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'training_id' => 3, 'target_type' => 'employee', 'target_id' => 2, 'assigned_at' => $now->copy()->subDays(1)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'training_id' => 4, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(2)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_materials')->insert([
            ['id' => 1, 'training_id' => 1, 'title' => 'Materi 1', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 1, 'title' => 'Materi 2', 'description' => 'Nonaktif', 'material_type' => 'link', 'file_path' => null, 'url' => 'https://example.com', 'file_type' => null, 'file_size' => null, 'order_number' => 2, 'is_active' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'training_id' => 2, 'title' => 'Materi Kepemimpinan', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 1, 'training_id' => 1, 'test_type' => 'pre_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Pre 1', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 1, 'test_type' => 'pre_test', 'question_type' => 'multiple_choice', 'order_number' => 2, 'question_text' => 'Pre 2', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'training_id' => 1, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Post 1', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'training_id' => 1, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 2, 'question_text' => 'Post 2', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            ['employee_id' => 1, 'training_id' => 1, 'assignment_id' => 1, 'status' => 'not_started', 'pre_test_completed_at' => null, 'material_completed_at' => null, 'post_test_completed_at' => null, 'final_score' => null, 'final_status' => null, 'completed_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 1, 'training_id' => 2, 'assignment_id' => 2, 'status' => 'passed', 'pre_test_completed_at' => $now, 'material_completed_at' => $now, 'post_test_completed_at' => $now, 'final_score' => 90, 'final_status' => 'passed', 'completed_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 2, 'training_id' => 3, 'assignment_id' => 3, 'status' => 'passed', 'pre_test_completed_at' => $now, 'material_completed_at' => $now, 'post_test_completed_at' => $now, 'final_score' => 88, 'final_status' => 'passed', 'completed_at' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$employeeOne, $adminUser];
    }
}
