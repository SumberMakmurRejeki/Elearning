<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeePostTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_open_post_test_page_and_see_active_questions(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        $this->actingAs($employeeUser)
            ->get(route('employee.post-test.show', ['training' => 1]))
            ->assertOk()
            ->assertSeeText('Post-Test')
            ->assertSeeText('Apa manfaat APD?')
            ->assertSeeText('Jelaskan prosedur K3')
            ->assertDontSeeText('pre_test');
    }

    public function test_employee_cannot_open_post_test_for_unassigned_training(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        $this->actingAs($employeeUser)
            ->get(route('employee.post-test.show', ['training' => 3]))
            ->assertRedirect(route('employee.training.show', ['training' => 3]))
            ->assertSessionHas('error');
    }

    public function test_employee_cannot_open_post_test_when_training_has_no_post_test(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        $this->actingAs($employeeUser)
            ->get(route('employee.post-test.show', ['training' => 2]))
            ->assertRedirect(route('employee.training.show', ['training' => 2]))
            ->assertSessionHas('error');
    }

    public function test_employee_cannot_open_post_test_when_materials_not_opened(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        // Remove material access logs so materials are not "opened"
        DB::table('material_access_logs')->truncate();

        $this->actingAs($employeeUser)
            ->get(route('employee.post-test.show', ['training' => 1]))
            ->assertRedirect(route('employee.training.show', ['training' => 1]))
            ->assertSessionHas('error');
    }

    public function test_employee_cannot_open_post_test_when_pre_test_not_completed(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        // Ensure pre_test_completed_at is null (seed already has material logs)
        DB::table('employee_training_progress')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->update(['pre_test_completed_at' => null]);

        $this->actingAs($employeeUser)
            ->get(route('employee.post-test.show', ['training' => 1]))
            ->assertRedirect(route('employee.training.show', ['training' => 1]))
            ->assertSessionHas('error');
    }

    public function test_employee_cannot_open_post_test_when_already_completed(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        DB::table('employee_training_progress')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->update(['post_test_completed_at' => Carbon::now()]);

        $this->actingAs($employeeUser)
            ->get(route('employee.post-test.show', ['training' => 1]))
            ->assertRedirect(route('employee.training.show', ['training' => 1]))
            ->assertSessionHas('error');
    }

    public function test_employee_can_submit_post_test_with_mcq_and_essay(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        $response = $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 1, 'essay_answer' => null],
                    ['question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Prosedur K3: pakai APD, cek alat, lapor insiden'],
                ],
            ]);

        $response->assertRedirect(route('employee.training.show', ['training' => 1]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('test_attempts', [
            'employee_id' => 1,
            'training_id' => 1,
            'test_type' => 'post_test',
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('test_answers', [
            'question_id' => 1,
            'selected_option_id' => 1,
        ]);

        $this->assertDatabaseHas('test_answers', [
            'question_id' => 2,
            'essay_answer' => 'Prosedur K3: pakai APD, cek alat, lapor insiden',
        ]);
    }

    public function test_post_test_submit_updates_progress_and_sets_waiting_review_for_essay(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 1, 'essay_answer' => null],
                    ['question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Jawaban essay'],
                ],
            ]);

        $progress = DB::table('employee_training_progress')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->first();

        $this->assertNotNull($progress->post_test_completed_at);
        $this->assertEquals('waiting_essay_review', $progress->status);

        $attempt = DB::table('test_attempts')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->where('test_type', 'post_test')
            ->first();

        $this->assertEquals('waiting_manual_review', $attempt->grading_status);
    }

    public function test_post_test_all_mcq_passing_sets_passed_status(): void
    {
        [$employeeUser] = $this->seedPostTestMcqOnlyData();

        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 10]), [
                'answers' => [
                    ['question_id' => 10, 'selected_option_id' => 10, 'essay_answer' => null],
                    ['question_id' => 11, 'selected_option_id' => 12, 'essay_answer' => null],
                ],
            ]);

        $attempt = DB::table('test_attempts')
            ->where('employee_id', 10)
            ->where('training_id', 10)
            ->where('test_type', 'post_test')
            ->first();

        $progress = DB::table('employee_training_progress')
            ->where('employee_id', 10)
            ->where('training_id', 10)
            ->first();

        $this->assertEquals('auto_graded', $attempt->grading_status);
        $this->assertEquals('completed', $attempt->status);
        $this->assertEquals('passed', $attempt->pass_status);
        $this->assertEquals(100.0, (float) $attempt->final_score);
        $this->assertEquals('passed', $progress->status);
        $this->assertEquals('passed', $progress->final_status);
    }

    public function test_post_test_all_mcq_failing_sets_failed_status(): void
    {
        [$employeeUser] = $this->seedPostTestMcqOnlyData();

        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 10]), [
                'answers' => [
                    ['question_id' => 10, 'selected_option_id' => 11, 'essay_answer' => null],
                    ['question_id' => 11, 'selected_option_id' => 13, 'essay_answer' => null],
                ],
            ]);

        $attempt = DB::table('test_attempts')
            ->where('employee_id', 10)
            ->where('training_id', 10)
            ->where('test_type', 'post_test')
            ->first();

        $progress = DB::table('employee_training_progress')
            ->where('employee_id', 10)
            ->where('training_id', 10)
            ->first();

        $this->assertEquals('failed', $attempt->pass_status);
        $this->assertEquals('failed', $progress->status);
        $this->assertEquals('failed', $progress->final_status);
        $this->assertEquals(0.0, (float) $attempt->final_score);
    }

    public function test_post_test_invalid_option_is_scored_zero_without_error(): void
    {
        [$employeeUser] = $this->seedPostTestMcqOnlyData();

        $response = $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 10]), [
                'answers' => [
                    ['question_id' => 10, 'selected_option_id' => 99999, 'essay_answer' => null],
                    ['question_id' => 11, 'selected_option_id' => 12, 'essay_answer' => null],
                ],
            ]);

        $response->assertRedirect(route('employee.training.show', ['training' => 10]));

        $this->assertDatabaseHas('test_answers', [
            'question_id' => 10,
            'selected_option_id' => null,
            'is_correct' => 0,
            'score' => 0,
        ]);
    }

    public function test_retake_creates_new_attempt_and_keeps_old_attempt(): void
    {
        [$employeeUser] = $this->seedRetakeData();

        // First attempt: fails (answers wrong)
        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 30]), [
                'answers' => [
                    ['question_id' => 30, 'selected_option_id' => 31, 'essay_answer' => null],
                    ['question_id' => 31, 'selected_option_id' => 33, 'essay_answer' => null],
                ],
            ]);

        $progress = DB::table('employee_training_progress')
            ->where('employee_id', 30)
            ->where('training_id', 30)
            ->first();
        $this->assertEquals('failed', $progress->status);

        // Retake: passes (answers correct)
        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 30], ['retake' => true]), [
                'retake' => true,
                'answers' => [
                    ['question_id' => 30, 'selected_option_id' => 30, 'essay_answer' => null],
                    ['question_id' => 31, 'selected_option_id' => 32, 'essay_answer' => null],
                ],
            ]);

        $progress = DB::table('employee_training_progress')
            ->where('employee_id', 30)
            ->where('training_id', 30)
            ->first();
        $this->assertEquals('passed', $progress->status);
        $this->assertEquals(100.0, (float) $progress->final_score);

        $attempts = DB::table('test_attempts')
            ->where('employee_id', 30)
            ->where('training_id', 30)
            ->where('test_type', 'post_test')
            ->orderBy('attempt_number')
            ->get();

        $this->assertEquals(2, $attempts->count());
        $this->assertEquals('failed', $attempts[0]->pass_status);
        $this->assertEquals('passed', $attempts[1]->pass_status);
    }

    public function test_retake_route_redirects_when_already_passed(): void
    {
        [$employeeUser] = $this->seedRetakeDataPassed();

        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.retake', ['training' => 40]))
            ->assertRedirect(route('employee.training.show', ['training' => 40]))
            ->assertSessionHas('error');
    }

    public function test_retake_route_redirects_when_retake_not_allowed(): void
    {
        [$employeeUser] = $this->seedRetakeDataNotAllowed();

        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.retake', ['training' => 50]))
            ->assertRedirect(route('employee.training.show', ['training' => 50]))
            ->assertSessionHas('error');
    }

    public function test_retake_route_redirects_when_attempt_limit_reached(): void
    {
        [$employeeUser] = $this->seedRetakeDataLimitReached();

        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.retake', ['training' => 60]))
            ->assertRedirect(route('employee.training.show', ['training' => 60]))
            ->assertSessionHas('error');
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedRetakeData(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 30, 'name' => 'IT', 'description' => 'IT', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 30, 'name' => 'Developer', 'description' => 'Developer', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Retake User',
            'username' => 'retake-user',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin Retake',
            'username' => 'admin-retake',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 30, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-600', 'division_id' => 30, 'position_id' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 30,
                'title' => 'Training Retake Allowed',
                'description' => 'Retake allowed',
                'start_date' => $now->toDateString(),
                'end_date' => $now->copy()->addDays(7)->toDateString(),
                'status' => 'published',
                'has_pre_test' => false,
                'has_post_test' => true,
                'passing_grade' => 75,
                'allow_post_test_retake' => true,
                'max_post_test_attempt' => 3,
                'show_score_to_employee' => true,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('training_assignments')->insert([
            ['id' => 30, 'training_id' => 30, 'target_type' => 'employee', 'target_id' => 30, 'assigned_at' => $now->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_materials')->insert([
            ['id' => 30, 'training_id' => 30, 'title' => 'Modul', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => 30,
                'training_id' => 30,
                'assignment_id' => 30,
                'status' => 'material_completed',
                'pre_test_completed_at' => null,
                'material_completed_at' => $now,
                'post_test_completed_at' => null,
                'final_score' => null,
                'final_status' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('material_access_logs')->insert([
            ['employee_id' => 30, 'training_id' => 30, 'material_id' => 30, 'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 30, 'training_id' => 30, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Soal 1', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 31, 'training_id' => 30, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 2, 'question_text' => 'Soal 2', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('question_options')->insert([
            ['id' => 30, 'question_id' => 30, 'option_label' => 'A', 'option_text' => 'Benar', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 31, 'question_id' => 30, 'option_label' => 'B', 'option_text' => 'Salah', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 32, 'question_id' => 31, 'option_label' => 'A', 'option_text' => 'Benar', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 33, 'question_id' => 31, 'option_label' => 'B', 'option_text' => 'Salah', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$employeeUser, $adminUser];
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedRetakeDataPassed(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 40, 'name' => 'HR', 'description' => 'HR', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 40, 'name' => 'HR Staff', 'description' => 'HR Staff', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Passed User',
            'username' => 'passed-user',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin Passed',
            'username' => 'admin-passed',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 40, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-700', 'division_id' => 40, 'position_id' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 40,
                'title' => 'Training Already Passed',
                'description' => 'Already passed',
                'start_date' => $now->toDateString(),
                'end_date' => $now->copy()->addDays(7)->toDateString(),
                'status' => 'published',
                'has_pre_test' => false,
                'has_post_test' => true,
                'passing_grade' => 75,
                'allow_post_test_retake' => true,
                'max_post_test_attempt' => 3,
                'show_score_to_employee' => true,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('training_assignments')->insert([
            ['id' => 40, 'training_id' => 40, 'target_type' => 'employee', 'target_id' => 40, 'assigned_at' => $now->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_materials')->insert([
            ['id' => 40, 'training_id' => 40, 'title' => 'Modul', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => 40,
                'training_id' => 40,
                'assignment_id' => 40,
                'status' => 'passed',
                'pre_test_completed_at' => null,
                'material_completed_at' => $now,
                'post_test_completed_at' => $now,
                'final_score' => 100,
                'final_status' => 'passed',
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('test_attempts')->insert([
            ['id' => 40, 'employee_id' => 40, 'training_id' => 40, 'test_type' => 'post_test', 'attempt_number' => 1, 'status' => 'completed', 'started_at' => $now, 'submitted_at' => $now, 'mcq_score' => 100, 'essay_score' => 0, 'final_score' => 100, 'grading_status' => 'auto_graded', 'pass_status' => 'passed', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('material_access_logs')->insert([
            ['employee_id' => 40, 'training_id' => 40, 'material_id' => 40, 'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 40, 'training_id' => 40, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Soal 1', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('question_options')->insert([
            ['id' => 40, 'question_id' => 40, 'option_label' => 'A', 'option_text' => 'Benar', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$employeeUser, $adminUser];
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedRetakeDataNotAllowed(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 50, 'name' => 'Finance', 'description' => 'Finance', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 50, 'name' => 'Accountant', 'description' => 'Accountant', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'No Retake User',
            'username' => 'no-retake-user',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin No Retake',
            'username' => 'admin-no-retake',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 50, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-800', 'division_id' => 50, 'position_id' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 50,
                'title' => 'Training No Retake',
                'description' => 'Retake not allowed',
                'start_date' => $now->toDateString(),
                'end_date' => $now->copy()->addDays(7)->toDateString(),
                'status' => 'published',
                'has_pre_test' => false,
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
            ['id' => 50, 'training_id' => 50, 'target_type' => 'employee', 'target_id' => 50, 'assigned_at' => $now->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_materials')->insert([
            ['id' => 50, 'training_id' => 50, 'title' => 'Modul', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => 50,
                'training_id' => 50,
                'assignment_id' => 50,
                'status' => 'failed',
                'pre_test_completed_at' => null,
                'material_completed_at' => $now,
                'post_test_completed_at' => $now,
                'final_score' => 50,
                'final_status' => 'failed',
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('test_attempts')->insert([
            ['id' => 50, 'employee_id' => 50, 'training_id' => 50, 'test_type' => 'post_test', 'attempt_number' => 1, 'status' => 'completed', 'started_at' => $now, 'submitted_at' => $now, 'mcq_score' => 50, 'essay_score' => 0, 'final_score' => 50, 'grading_status' => 'auto_graded', 'pass_status' => 'failed', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('material_access_logs')->insert([
            ['employee_id' => 50, 'training_id' => 50, 'material_id' => 50, 'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 50, 'training_id' => 50, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Soal 1', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('question_options')->insert([
            ['id' => 50, 'question_id' => 50, 'option_label' => 'A', 'option_text' => 'Benar', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$employeeUser, $adminUser];
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedRetakeDataLimitReached(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 60, 'name' => 'Marketing', 'description' => 'Marketing', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 60, 'name' => 'Marketer', 'description' => 'Marketer', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Limit User',
            'username' => 'limit-user',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin Limit',
            'username' => 'admin-limit',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 60, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-900', 'division_id' => 60, 'position_id' => 60, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 60,
                'title' => 'Training Limit Reached',
                'description' => 'Max attempts reached',
                'start_date' => $now->toDateString(),
                'end_date' => $now->copy()->addDays(7)->toDateString(),
                'status' => 'published',
                'has_pre_test' => false,
                'has_post_test' => true,
                'passing_grade' => 75,
                'allow_post_test_retake' => true,
                'max_post_test_attempt' => 2,
                'show_score_to_employee' => true,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('training_assignments')->insert([
            ['id' => 60, 'training_id' => 60, 'target_type' => 'employee', 'target_id' => 60, 'assigned_at' => $now->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_materials')->insert([
            ['id' => 60, 'training_id' => 60, 'title' => 'Modul', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => 60,
                'training_id' => 60,
                'assignment_id' => 60,
                'status' => 'failed',
                'pre_test_completed_at' => null,
                'material_completed_at' => $now,
                'post_test_completed_at' => $now,
                'final_score' => 40,
                'final_status' => 'failed',
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('test_attempts')->insert([
            ['id' => 60, 'employee_id' => 60, 'training_id' => 60, 'test_type' => 'post_test', 'attempt_number' => 1, 'status' => 'completed', 'started_at' => $now, 'submitted_at' => $now, 'mcq_score' => 50, 'essay_score' => 0, 'final_score' => 50, 'grading_status' => 'auto_graded', 'pass_status' => 'failed', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 61, 'employee_id' => 60, 'training_id' => 60, 'test_type' => 'post_test', 'attempt_number' => 2, 'status' => 'completed', 'started_at' => $now, 'submitted_at' => $now, 'mcq_score' => 40, 'essay_score' => 0, 'final_score' => 40, 'grading_status' => 'auto_graded', 'pass_status' => 'failed', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('material_access_logs')->insert([
            ['employee_id' => 60, 'training_id' => 60, 'material_id' => 60, 'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 60, 'training_id' => 60, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Soal 1', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('question_options')->insert([
            ['id' => 60, 'question_id' => 60, 'option_label' => 'A', 'option_text' => 'Benar', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$employeeUser, $adminUser];
    }

    public function test_post_test_double_submit_is_prevented(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        // First submit
        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 1, 'essay_answer' => null],
                    ['question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Jawaban'],
                ],
            ]);

        // Second submit should fail
        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 2, 'essay_answer' => null],
                    ['question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Jawaban lain'],
                ],
            ])
            ->assertSessionHas('error');

        $count = DB::table('test_attempts')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->where('test_type', 'post_test')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_post_test_submit_fails_when_not_all_questions_answered(): void
    {
        [$employeeUser] = $this->seedPostTestData();

        $this->actingAs($employeeUser)
            ->post(route('employee.post-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 1, 'essay_answer' => null],
                ],
            ])
            ->assertSessionHas('error');
    }

    public function test_admin_cannot_access_post_test_pages(): void
    {
        [, $adminUser] = $this->seedPostTestData();

        $this->actingAs($adminUser)
            ->get(route('employee.post-test.show', ['training' => 1]))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($adminUser)
            ->post(route('employee.post-test.submit', ['training' => 1]))
            ->assertRedirect(route('admin.dashboard'));
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedPostTestData(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-posttest',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-posttest',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-400', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 1,
                'title' => 'Training Keselamatan Kerja',
                'description' => 'Training dengan pre-test dan post-test',
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
                'title' => 'Training Tanpa Post-Test',
                'description' => 'Training tanpa post-test',
                'start_date' => $now->copy()->subDays(2)->toDateString(),
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
            [
                'id' => 3,
                'title' => 'Training Orang Lain',
                'description' => 'Training untuk employee lain',
                'start_date' => $now->copy()->subDays(1)->toDateString(),
                'end_date' => $now->copy()->addDays(20)->toDateString(),
                'status' => 'published',
                'has_pre_test' => true,
                'has_post_test' => true,
                'passing_grade' => 70,
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
            ['id' => 2, 'training_id' => 2, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(1)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Materials for training 1 (2 active)
        DB::table('training_materials')->insert([
            ['id' => 1, 'training_id' => 1, 'title' => 'Modul 1', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 1, 'title' => 'Modul 2', 'description' => 'Aktif', 'material_type' => 'link', 'file_path' => null, 'url' => 'https://example.com', 'file_type' => null, 'file_size' => null, 'order_number' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Pre-test completed, materials opened, post-test NOT yet completed
        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => 1,
                'training_id' => 1,
                'assignment_id' => 1,
                'status' => 'material_completed',
                'pre_test_completed_at' => $now->copy()->subDays(2),
                'material_completed_at' => $now->copy()->subDay(),
                'post_test_completed_at' => null,
                'final_score' => null,
                'final_status' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => 1,
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

        // Material access logs (both materials opened)
        DB::table('material_access_logs')->insert([
            ['employee_id' => 1, 'training_id' => 1, 'material_id' => 1, 'opened_at' => $now->copy()->subDays(1), 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 1, 'training_id' => 1, 'material_id' => 2, 'opened_at' => $now->copy()->subHours(12), 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Post-test questions: 1 MCQ + 1 Essay
        DB::table('questions')->insert([
            [
                'id' => 1,
                'training_id' => 1,
                'test_type' => 'post_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'Apa manfaat APD?',
                'weight' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'training_id' => 1,
                'test_type' => 'post_test',
                'question_type' => 'essay',
                'order_number' => 2,
                'question_text' => 'Jelaskan prosedur K3',
                'weight' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Pre-test question (should NOT appear in post-test)
            [
                'id' => 3,
                'training_id' => 1,
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'Pre-test question',
                'weight' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Inactive question (should NOT appear)
            [
                'id' => 4,
                'training_id' => 1,
                'test_type' => 'post_test',
                'question_type' => 'multiple_choice',
                'order_number' => 3,
                'question_text' => 'Inactive question',
                'weight' => 1,
                'is_active' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // MCQ options
        DB::table('question_options')->insert([
            ['id' => 1, 'question_id' => 1, 'option_label' => 'A', 'option_text' => 'Melindungi dari bahaya', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'question_id' => 1, 'option_label' => 'B', 'option_text' => 'Membuat panas', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'question_id' => 1, 'option_label' => 'C', 'option_text' => 'Tidak berguna', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$employeeUser, $adminUser];
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedPostTestMcqOnlyData(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 10, 'name' => 'Operasional', 'description' => 'Operasional', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 10, 'name' => 'Operator', 'description' => 'Operator', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Andi Operator',
            'username' => 'andi-posttest-mcq',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin MCQ',
            'username' => 'admin-posttest-mcq',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 10, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-410', 'division_id' => 10, 'position_id' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 10,
                'title' => 'Training MCQ Only',
                'description' => 'Post-test pilihan ganda saja',
                'start_date' => $now->copy()->subDays(3)->toDateString(),
                'end_date' => $now->copy()->addDays(5)->toDateString(),
                'status' => 'published',
                'has_pre_test' => false,
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
            ['id' => 10, 'training_id' => 10, 'target_type' => 'employee', 'target_id' => 10, 'assigned_at' => $now->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_materials')->insert([
            ['id' => 10, 'training_id' => 10, 'title' => 'Modul A', 'description' => 'Aktif', 'material_type' => 'file', 'file_path' => null, 'url' => null, 'file_type' => null, 'file_size' => null, 'order_number' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => 10,
                'training_id' => 10,
                'assignment_id' => 10,
                'status' => 'material_completed',
                'pre_test_completed_at' => null,
                'material_completed_at' => $now,
                'post_test_completed_at' => null,
                'final_score' => null,
                'final_status' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('material_access_logs')->insert([
            ['employee_id' => 10, 'training_id' => 10, 'material_id' => 10, 'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 10, 'training_id' => 10, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Soal 1', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'training_id' => 10, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 2, 'question_text' => 'Soal 2', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('question_options')->insert([
            ['id' => 10, 'question_id' => 10, 'option_label' => 'A', 'option_text' => 'Benar', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'question_id' => 10, 'option_label' => 'B', 'option_text' => 'Salah', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'question_id' => 11, 'option_label' => 'A', 'option_text' => 'Benar', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'question_id' => 11, 'option_label' => 'B', 'option_text' => 'Salah', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$employeeUser, $adminUser];
    }
}
