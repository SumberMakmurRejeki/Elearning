<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTestResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_test_result_index(): void
    {
        [$admin, $employeeUser] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index'))
            ->assertOk()
            ->assertSeeText('Hasil Test')
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Post-Test');
    }

    public function test_admin_can_filter_test_results_by_training(): void
    {
        [$admin] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index', ['training_id' => 1]))
            ->assertOk()
            ->assertSeeText('Training Keselamatan Kerja');
    }

    public function test_admin_can_filter_test_results_by_division(): void
    {
        [$admin] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index', ['division_id' => 1]))
            ->assertOk()
            ->assertSeeText('Budi Santoso');
    }

    public function test_admin_can_filter_test_results_by_position(): void
    {
        [$admin] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index', ['position_id' => 1]))
            ->assertOk()
            ->assertSeeText('Budi Santoso');
    }

    public function test_admin_can_filter_test_results_by_test_type(): void
    {
        [$admin] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index', ['test_type' => 'pre_test']))
            ->assertOk()
            ->assertSee('Pre-Test');

        // Post-test should not appear when filtering pre_test
        $response = $this->actingAs($admin)
            ->get(route('admin.hasil-test.index', ['test_type' => 'pre_test']));
        $response->assertSee('Pre-Test');
    }

    public function test_admin_can_filter_test_results_by_grading_status(): void
    {
        [$admin] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index', ['grading_status' => 'auto_graded']))
            ->assertOk()
            ->assertSeeText('Auto-Graded');
    }

    public function test_employee_cannot_access_test_result_routes(): void
    {
        [, $employee] = $this->seedTestData();

        $this->actingAs($employee)
            ->get(route('admin.hasil-test.index'))
            ->assertRedirect(route('employee.dashboard'));

        $this->actingAs($employee)
            ->get(route('admin.hasil-test.show', ['attempt' => 1]))
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_admin_can_view_test_result_detail(): void
    {
        [$admin] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.show', ['attempt' => 2]))
            ->assertOk()
            ->assertSeeText('Detail Hasil Test')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('Post-Test')
            ->assertSeeText('85.00');
    }

    public function test_admin_can_view_test_result_detail_with_essay_answers(): void
    {
        [$admin] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.show', ['attempt' => 3]))
            ->assertOk()
            ->assertSeeText('Essay')
            ->assertSeeText('Menunggu Penilaian');
    }

    public function test_empty_state_displays_when_no_test_results(): void
    {
        $now = Carbon::now();

        $admin = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-empty',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index'))
            ->assertOk()
            ->assertSeeText('Belum ada hasil test');
    }

    public function test_admin_can_filter_by_date_range(): void
    {
        [$admin] = $this->seedTestData();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index', ['date_from' => now()->subDays(1)->format('Y-m-d')]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.hasil-test.index', ['date_from' => '2020-01-01', 'date_to' => '2020-12-31']))
            ->assertOk()
            ->assertSeeText('Tidak ada hasil test sesuai filter');
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedTestData(): array
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
            'username' => 'admin-test-result',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-test-result',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-TR-001', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            ['id' => 1, 'title' => 'Training Keselamatan Kerja', 'description' => 'Training dengan pre-test dan post-test', 'start_date' => $now->copy()->subDays(10)->toDateString(), 'end_date' => $now->copy()->addDays(5)->toDateString(), 'status' => 'published', 'has_pre_test' => true, 'has_post_test' => true, 'passing_grade' => 75, 'allow_post_test_retake' => false, 'max_post_test_attempt' => null, 'show_score_to_employee' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 1, 'training_id' => 1, 'test_type' => 'pre_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Apa itu K3?', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 1, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Apa manfaat APD?', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'training_id' => 1, 'test_type' => 'post_test', 'question_type' => 'essay', 'order_number' => 2, 'question_text' => 'Jelaskan prosedur K3', 'weight' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('question_options')->insert([
            ['id' => 1, 'question_id' => 1, 'option_label' => 'A', 'option_text' => 'Keselamatan Kerja', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'question_id' => 1, 'option_label' => 'B', 'option_text' => 'Bukan K3', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'question_id' => 2, 'option_label' => 'A', 'option_text' => 'Melindungi pekerja', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'question_id' => 2, 'option_label' => 'B', 'option_text' => 'Tidak berguna', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Pre-test attempt (auto_graded)
        DB::table('test_attempts')->insert([
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
        ]);

        // Post-test attempt (auto_graded, passed)
        DB::table('test_attempts')->insert([
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
        ]);

        // Post-test attempt with essay (waiting_manual_review)
        DB::table('test_attempts')->insert([
            'id' => 3,
            'employee_id' => 1,
            'training_id' => 1,
            'test_type' => 'post_test',
            'attempt_number' => 2,
            'status' => 'submitted',
            'started_at' => $now->copy()->subDays(2),
            'submitted_at' => $now->copy()->subDays(2),
            'mcq_score' => 90,
            'essay_score' => 0,
            'final_score' => null,
            'grading_status' => 'waiting_manual_review',
            'pass_status' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('test_answers')->insert([
            ['id' => 1, 'attempt_id' => 1, 'question_id' => 1, 'selected_option_id' => 1, 'essay_answer' => null, 'is_correct' => true, 'score' => 1, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'attempt_id' => 2, 'question_id' => 2, 'selected_option_id' => 3, 'essay_answer' => null, 'is_correct' => true, 'score' => 1, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'attempt_id' => 3, 'question_id' => 2, 'selected_option_id' => 3, 'essay_answer' => null, 'is_correct' => true, 'score' => 1, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'attempt_id' => 3, 'question_id' => 3, 'selected_option_id' => null, 'essay_answer' => 'Prosedur K3: pakai APD, cek alat, lapor insiden', 'is_correct' => null, 'score' => 0, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_assignments')->insert([
            ['id' => 1, 'training_id' => 1, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(8)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            ['id' => 1, 'employee_id' => 1, 'training_id' => 1, 'assignment_id' => 1, 'status' => 'post_test_completed', 'pre_test_completed_at' => $now->copy()->subDays(7), 'material_completed_at' => $now->copy()->subDays(5), 'post_test_completed_at' => $now->copy()->subDays(2), 'final_score' => 85, 'final_status' => 'passed', 'completed_at' => $now->copy()->subDays(4), 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$admin, $employeeUser];
    }
}
