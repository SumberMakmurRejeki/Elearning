<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_report_index(): void
    {
        [$admin] = $this->seedReportData();

        $this->actingAs($admin)
            ->get(route('admin.laporan.index'))
            ->assertOk()
            ->assertSeeText('Laporan Training')
            ->assertSeeText('Training Keselamatan Kerja');
    }

    public function test_report_shows_summary_cards(): void
    {
        [$admin] = $this->seedReportData();

        $response = $this->actingAs($admin)
            ->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertSeeText('Total Assignment');
        $response->assertSeeText('Total Selesai');
        $response->assertSeeText('Total Lulus');
        $response->assertSeeText('Total Tidak Lulus');
    }

    public function test_report_table_shows_training_data(): void
    {
        [$admin] = $this->seedReportData();

        $this->actingAs($admin)
            ->get(route('admin.laporan.index'))
            ->assertOk()
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Lulus');
    }

    public function test_admin_can_filter_report_by_training(): void
    {
        [$admin] = $this->seedReportData();

        $this->actingAs($admin)
            ->get(route('admin.laporan.index', ['training_id' => 1]))
            ->assertOk()
            ->assertSeeText('Training Keselamatan Kerja');
    }

    public function test_admin_can_filter_report_by_division(): void
    {
        [$admin] = $this->seedReportData();

        $this->actingAs($admin)
            ->get(route('admin.laporan.index', ['division_id' => 1]))
            ->assertOk()
            ->assertSeeText('Training Keselamatan Kerja');
    }

    public function test_admin_can_view_report_detail(): void
    {
        [$admin] = $this->seedReportData();

        $this->actingAs($admin)
            ->get(route('admin.laporan.show', ['training' => 1]))
            ->assertOk()
            ->assertSeeText('Detail Laporan Training')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('Lulus');
    }

    public function test_report_detail_shows_employee_progress(): void
    {
        [$admin] = $this->seedReportData();

        $response = $this->actingAs($admin)
            ->get(route('admin.laporan.show', ['training' => 1]));

        $response->assertOk();
        $response->assertSeeText('Budi Santoso');
        $response->assertSeeText('Lulus');
        $response->assertSeeText('100.00');
        $response->assertSeeText('85.00');
    }

    public function test_employee_cannot_access_report_routes(): void
    {
        [, $employee] = $this->seedReportData();

        $this->actingAs($employee)
            ->get(route('admin.laporan.index'))
            ->assertRedirect(route('employee.dashboard'));

        $this->actingAs($employee)
            ->get(route('admin.laporan.show', ['training' => 1]))
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_empty_state_displays_when_no_report_data(): void
    {
        $now = Carbon::now();

        $admin = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-report-empty',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.laporan.index'))
            ->assertOk()
            ->assertSeeText('Belum ada data laporan');
    }

    public function test_report_handles_training_without_attempts(): void
    {
        [$admin] = $this->seedReportDataWithoutAttempts();

        $response = $this->actingAs($admin)
            ->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertSeeText('Training Tanpa Test');
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedReportData(): array
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
            'username' => 'admin-report',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-report',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-RPT-001', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            ['id' => 1, 'title' => 'Training Keselamatan Kerja', 'description' => 'Training dengan pre-test dan post-test', 'start_date' => $now->copy()->subDays(10)->toDateString(), 'end_date' => $now->copy()->addDays(5)->toDateString(), 'status' => 'published', 'has_pre_test' => true, 'has_post_test' => true, 'passing_grade' => 75, 'allow_post_test_retake' => false, 'max_post_test_attempt' => null, 'show_score_to_employee' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 1, 'training_id' => 1, 'test_type' => 'pre_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Apa itu K3?', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 1, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Apa manfaat APD?', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('question_options')->insert([
            ['id' => 1, 'question_id' => 1, 'option_label' => 'A', 'option_text' => 'Keselamatan Kerja', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'question_id' => 1, 'option_label' => 'B', 'option_text' => 'Bukan K3', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'question_id' => 2, 'option_label' => 'A', 'option_text' => 'Melindungi pekerja', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'question_id' => 2, 'option_label' => 'B', 'option_text' => 'Tidak berguna', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
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
                'mcq_score' => 100,
                'essay_score' => 0,
                'final_score' => 100,
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

        DB::table('training_assignments')->insert([
            ['id' => 1, 'training_id' => 1, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(8)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
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

        return [$admin, $employeeUser];
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedReportDataWithoutAttempts(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 2, 'name' => 'IT', 'description' => 'Information Technology', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 2, 'name' => 'Developer', 'description' => 'Software Developer', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-report-no-attempts',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Andi Wijaya',
            'username' => 'andi-report',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 2, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-RPT-002', 'division_id' => 2, 'position_id' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            ['id' => 2, 'title' => 'Training Tanpa Test', 'description' => 'Training tanpa pre-test dan post-test', 'start_date' => $now->copy()->subDays(10)->toDateString(), 'end_date' => $now->copy()->addDays(5)->toDateString(), 'status' => 'published', 'has_pre_test' => false, 'has_post_test' => false, 'passing_grade' => null, 'allow_post_test_retake' => false, 'max_post_test_attempt' => null, 'show_score_to_employee' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_assignments')->insert([
            ['id' => 2, 'training_id' => 2, 'target_type' => 'employee', 'target_id' => 2, 'assigned_at' => $now->copy()->subDays(8)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'id' => 2,
                'employee_id' => 2,
                'training_id' => 2,
                'assignment_id' => 2,
                'status' => 'not_started',
                'pre_test_completed_at' => null,
                'material_completed_at' => null,
                'post_test_completed_at' => null,
                'final_score' => null,
                'final_status' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        return [$admin, $employeeUser];
    }
}
