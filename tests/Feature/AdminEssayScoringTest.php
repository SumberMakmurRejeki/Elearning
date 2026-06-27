<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminEssayScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_essay_answer_index_and_filters(): void
    {
        [$admin] = $this->seedEssayScoringData();

        $this->actingAs($admin)
            ->get(route('admin.essay-answers.index'))
            ->assertOk()
            ->assertSeeText('Jawaban Essay')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Budi Santoso');

        $this->actingAs($admin)
            ->get(route('admin.essay-answers.index', ['test_type' => 'post_test']))
            ->assertOk()
            ->assertSee('Post-Test');

        // Verify only post-test essay rows appear by checking the answer text
        $this->actingAs($admin)
            ->get(route('admin.essay-answers.index', ['test_type' => 'post_test']))
            ->assertSee('Essay post-test')
            ->assertDontSee('Jelaskan prosedur K3');
    }

    public function test_employee_cannot_access_admin_essay_routes(): void
    {
        [, $employee] = $this->seedEssayScoringData();

        $this->actingAs($employee)
            ->get(route('admin.essay-answers.index'))
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_admin_can_view_essay_answer_detail(): void
    {
        [$admin] = $this->seedEssayScoringData();

        $this->actingAs($admin)
            ->get(route('admin.essay-answers.show', ['answer' => 2]))
            ->assertOk()
            ->assertSeeText('Nilai Jawaban Essay')
            ->assertSeeText('Jelaskan prosedur K3')
            ->assertSeeText('Jawaban essay awal');
    }

    public function test_score_validation_rejects_below_zero_and_above_weight(): void
    {
        [$admin] = $this->seedEssayScoringData();

        $this->actingAs($admin)
            ->post(route('admin.essay-answers.score', ['answer' => 2]), ['score' => -1])
            ->assertSessionHasErrors('score');

        $this->actingAs($admin)
            ->post(route('admin.essay-answers.score', ['answer' => 2]), ['score' => 99])
            ->assertSessionHasErrors('score');
    }

    public function test_scoring_one_of_multiple_essay_answers_keeps_attempt_waiting(): void
    {
        [$admin] = $this->seedEssayScoringDataWithMultipleEssays();

        $this->actingAs($admin)
            ->post(route('admin.essay-answers.score', ['answer' => 20]), ['score' => 2])
            ->assertRedirect(route('admin.essay-answers.show', ['answer' => 20]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('test_answers', [
            'id' => 20,
            'score' => 2,
        ]);

        $attempt = DB::table('test_attempts')->where('id', 20)->first();
        $this->assertEquals('waiting_manual_review', $attempt->grading_status);
        $this->assertEquals('submitted', $attempt->status);
    }

    public function test_final_pre_test_essay_scoring_marks_attempt_reviewed_without_pass_fail(): void
    {
        [$admin] = $this->seedEssayScoringData();

        $this->actingAs($admin)
            ->post(route('admin.essay-answers.score', ['answer' => 2]), ['score' => 1])
            ->assertRedirect(route('admin.essay-answers.show', ['answer' => 2]));

        $attempt = DB::table('test_attempts')->where('id', 1)->first();
        $progress = DB::table('employee_training_progress')->where('employee_id', 1)->where('training_id', 1)->first();

        $this->assertEquals('manual_reviewed', $attempt->grading_status);
        $this->assertEquals('graded', $attempt->status);
        $this->assertEquals(101.0, (float) $attempt->final_score);
        $this->assertNull($attempt->pass_status);
        $this->assertEquals('pre_test_completed', $progress->status);
        $this->assertNull($progress->final_status);
    }

    public function test_final_post_test_essay_scoring_sets_passed_status_when_meeting_passing_grade(): void
    {
        [$admin] = $this->seedEssayScoringData();

        $this->actingAs($admin)
            ->post(route('admin.essay-answers.score', ['answer' => 4]), ['score' => 20])
            ->assertRedirect(route('admin.essay-answers.show', ['answer' => 4]));

        $attempt = DB::table('test_attempts')->where('id', 2)->first();
        $progress = DB::table('employee_training_progress')->where('employee_id', 1)->where('training_id', 2)->first();

        $this->assertEquals('manual_reviewed', $attempt->grading_status);
        $this->assertEquals('graded', $attempt->status);
        $this->assertEquals('passed', $attempt->pass_status);
        $this->assertEquals(100.0, (float) $attempt->final_score);
        $this->assertEquals('passed', $progress->status);
        $this->assertEquals('passed', $progress->final_status);
    }

    public function test_final_post_test_essay_scoring_sets_failed_status_when_below_passing_grade(): void
    {
        [$admin] = $this->seedEssayScoringData();

        $this->actingAs($admin)
            ->post(route('admin.essay-answers.score', ['answer' => 4]), ['score' => 0])
            ->assertRedirect(route('admin.essay-answers.show', ['answer' => 4]));

        $attempt = DB::table('test_attempts')->where('id', 2)->first();
        $progress = DB::table('employee_training_progress')->where('employee_id', 1)->where('training_id', 2)->first();

        $this->assertEquals('failed', $attempt->pass_status);
        $this->assertEquals('failed', $progress->status);
        $this->assertEquals('failed', $progress->final_status);
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedEssayScoringData(): array
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
            'username' => 'admin-essay',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employee = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-essay',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employee->id, 'employee_number' => 'EMP-500', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            ['id' => 1, 'title' => 'Training Keselamatan Kerja', 'description' => 'Pre-test essay', 'start_date' => $now->toDateString(), 'end_date' => $now->copy()->addDays(7)->toDateString(), 'status' => 'published', 'has_pre_test' => true, 'has_post_test' => false, 'passing_grade' => null, 'allow_post_test_retake' => false, 'max_post_test_attempt' => null, 'show_score_to_employee' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'title' => 'Training Post Test Essay', 'description' => 'Post-test essay', 'start_date' => $now->toDateString(), 'end_date' => $now->copy()->addDays(7)->toDateString(), 'status' => 'published', 'has_pre_test' => false, 'has_post_test' => true, 'passing_grade' => 90, 'allow_post_test_retake' => false, 'max_post_test_attempt' => null, 'show_score_to_employee' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 1, 'training_id' => 1, 'test_type' => 'pre_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'MCQ pre-test', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 1, 'test_type' => 'pre_test', 'question_type' => 'essay', 'order_number' => 2, 'question_text' => 'Jelaskan prosedur K3', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'training_id' => 2, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'MCQ post-test', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'training_id' => 2, 'test_type' => 'post_test', 'question_type' => 'essay', 'order_number' => 2, 'question_text' => 'Essay post-test', 'weight' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('test_attempts')->insert([
            ['id' => 1, 'employee_id' => 1, 'training_id' => 1, 'test_type' => 'pre_test', 'attempt_number' => 1, 'status' => 'submitted', 'started_at' => $now, 'submitted_at' => $now, 'mcq_score' => 100, 'essay_score' => 0, 'final_score' => null, 'grading_status' => 'waiting_manual_review', 'pass_status' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'employee_id' => 1, 'training_id' => 2, 'test_type' => 'post_test', 'attempt_number' => 1, 'status' => 'submitted', 'started_at' => $now, 'submitted_at' => $now, 'mcq_score' => 80, 'essay_score' => 0, 'final_score' => null, 'grading_status' => 'waiting_manual_review', 'pass_status' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('test_answers')->insert([
            ['id' => 1, 'attempt_id' => 1, 'question_id' => 1, 'selected_option_id' => null, 'essay_answer' => null, 'is_correct' => true, 'score' => 1, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'attempt_id' => 1, 'question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Jawaban essay awal', 'is_correct' => null, 'score' => 0, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'attempt_id' => 2, 'question_id' => 3, 'selected_option_id' => null, 'essay_answer' => null, 'is_correct' => true, 'score' => 1, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'attempt_id' => 2, 'question_id' => 4, 'selected_option_id' => null, 'essay_answer' => 'Jawaban essay post test', 'is_correct' => null, 'score' => 0, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            ['employee_id' => 1, 'training_id' => 1, 'assignment_id' => null, 'status' => 'pre_test_completed', 'pre_test_completed_at' => $now, 'material_completed_at' => null, 'post_test_completed_at' => null, 'final_score' => null, 'final_status' => null, 'completed_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 1, 'training_id' => 2, 'assignment_id' => null, 'status' => 'waiting_essay_review', 'pre_test_completed_at' => null, 'material_completed_at' => $now, 'post_test_completed_at' => $now, 'final_score' => null, 'final_status' => null, 'completed_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$admin, $employee];
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedEssayScoringDataWithMultipleEssays(): array
    {
        [$admin, $employee] = $this->seedEssayScoringData();
        $now = Carbon::now();

        DB::table('trainings')->insert([
            ['id' => 20, 'title' => 'Training Multi Essay', 'description' => 'Multi essay', 'start_date' => $now->toDateString(), 'end_date' => $now->copy()->addDays(7)->toDateString(), 'status' => 'published', 'has_pre_test' => false, 'has_post_test' => true, 'passing_grade' => 70, 'allow_post_test_retake' => false, 'max_post_test_attempt' => null, 'show_score_to_employee' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 20, 'training_id' => 20, 'test_type' => 'post_test', 'question_type' => 'essay', 'order_number' => 1, 'question_text' => 'Essay 1', 'weight' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'training_id' => 20, 'test_type' => 'post_test', 'question_type' => 'essay', 'order_number' => 2, 'question_text' => 'Essay 2', 'weight' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('test_attempts')->insert([
            ['id' => 20, 'employee_id' => 1, 'training_id' => 20, 'test_type' => 'post_test', 'attempt_number' => 1, 'status' => 'submitted', 'started_at' => $now, 'submitted_at' => $now, 'mcq_score' => 0, 'essay_score' => 0, 'final_score' => null, 'grading_status' => 'waiting_manual_review', 'pass_status' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('test_answers')->insert([
            ['id' => 20, 'attempt_id' => 20, 'question_id' => 20, 'selected_option_id' => null, 'essay_answer' => 'Jawaban essay 1', 'is_correct' => null, 'score' => 0, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'attempt_id' => 20, 'question_id' => 21, 'selected_option_id' => null, 'essay_answer' => 'Jawaban essay 2', 'is_correct' => null, 'score' => 0, 'graded_by' => null, 'graded_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$admin, $employee];
    }
}
