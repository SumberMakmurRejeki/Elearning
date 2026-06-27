<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminProgressMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_progress_index(): void
    {
        [$admin, $employeeUser] = $this->seedProgressData();

        $this->actingAs($admin)
            ->get(route('admin.progress.index'))
            ->assertOk()
            ->assertSeeText('Monitoring Progress Training')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Budi Santoso');
    }

    public function test_admin_can_filter_progress_by_training(): void
    {
        [$admin, $employeeUser] = $this->seedProgressData();

        $this->actingAs($admin)
            ->get(route('admin.progress.index', ['training_id' => 1]))
            ->assertOk()
            ->assertSeeText('Training Keselamatan Kerja');
    }

    public function test_admin_can_filter_progress_by_status(): void
    {
        [$admin, $employeeUser] = $this->seedProgressData();

        $this->actingAs($admin)
            ->get(route('admin.progress.index', ['status' => 'passed']))
            ->assertOk()
            ->assertSeeText('Lulus');
    }

    public function test_employee_cannot_access_progress_monitoring(): void
    {
        [$admin, $employeeUser] = $this->seedProgressData();

        $this->actingAs($employeeUser)
            ->get(route('admin.progress.index'))
            ->assertRedirect(route('employee.dashboard'));

        $this->actingAs($employeeUser)
            ->get(route('admin.progress.show', ['progress' => 1]))
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_admin_can_view_progress_detail(): void
    {
        [$admin, $employeeUser] = $this->seedProgressData();

        $this->actingAs($admin)
            ->get(route('admin.progress.show', ['progress' => 1]))
            ->assertOk()
            ->assertSeeText('Detail Progress Training')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('Lulus');
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedProgressData(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-progress',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-progress',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-700', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 1,
                'title' => 'Training Keselamatan Kerja',
                'description' => 'Training dengan pre-test dan post-test',
                'start_date' => $now->copy()->subDays(10)->toDateString(),
                'end_date' => $now->copy()->addDays(5)->toDateString(),
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
        ]);

        DB::table('training_assignments')->insert([
            ['id' => 1, 'training_id' => 1, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(8)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_materials')->insert([
            ['id' => 1, 'training_id' => 1, 'title' => 'Modul 1', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 1, 'title' => 'Modul 2', 'description' => 'Aktif', 'material_type' => 'link', 'file_path' => null, 'url' => 'https://example.com', 'file_type' => null, 'file_size' => null, 'order_number' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('material_access_logs')->insert([
            ['employee_id' => 1, 'training_id' => 1, 'material_id' => 1, 'opened_at' => $now->copy()->subDays(7), 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 1, 'training_id' => 1, 'material_id' => 2, 'opened_at' => $now->copy()->subDays(6), 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'id' => 1,
                'employee_id' => 1,
                'training_id' => 1,
                'assignment_id' => 1,
                'status' => 'passed',
                'pre_test_completed_at' => $now->copy()->subDays(7),
                'material_completed_at' => $now->copy()->subDays(5),
                'post_test_completed_at' => $now->copy()->subDays(4),
                'final_score' => 85,
                'final_status' => 'passed',
                'completed_at' => $now->copy()->subDays(4),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('test_attempts')->insert([
            [
                'id' => 1,
                'employee_id' => 1,
                'training_id' => 1,
                'test_type' => 'pre_test',
                'attempt_number' => 1,
                'status' => 'completed',
                'started_at' => $now->copy()->subDays(7),
                'submitted_at' => $now->copy()->subDays(7),
                'mcq_score' => 80,
                'essay_score' => 0,
                'final_score' => 80,
                'grading_status' => 'auto_graded',
                'pass_status' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'employee_id' => 1,
                'training_id' => 1,
                'test_type' => 'post_test',
                'attempt_number' => 1,
                'status' => 'completed',
                'started_at' => $now->copy()->subDays(4),
                'submitted_at' => $now->copy()->subDays(4),
                'mcq_score' => 85,
                'essay_score' => 0,
                'final_score' => 85,
                'grading_status' => 'auto_graded',
                'pass_status' => 'passed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        return [$admin, $employeeUser];
    }
}
