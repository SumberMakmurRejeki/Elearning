<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeePreTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_open_pre_test_page_and_see_active_questions(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        $this->actingAs($employeeUser)
            ->get(route('employee.pre-test.show', ['training' => 1]))
            ->assertOk()
            ->assertSeeText('Pre-Test')
            ->assertSeeText('Apa yang dimaksud dengan K3?')
            ->assertSeeText('Sebutkan 3 alat pelindung diri')
            ->assertDontSeeText('post_test');
    }

    public function test_employee_cannot_open_pre_test_for_unassigned_training(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        $this->actingAs($employeeUser)
            ->get(route('employee.pre-test.show', ['training' => 3]))
            ->assertRedirect(route('employee.material.index', ['training' => 3]));
    }

    public function test_employee_cannot_open_pre_test_when_training_has_no_pre_test(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        $this->actingAs($employeeUser)
            ->get(route('employee.pre-test.show', ['training' => 2]))
            ->assertRedirect(route('employee.material.index', ['training' => 2]));
    }

    public function test_employee_cannot_open_pre_test_when_already_completed(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        // Mark pre-test as completed
        DB::table('employee_training_progress')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->update(['pre_test_completed_at' => Carbon::now()]);

        $this->actingAs($employeeUser)
            ->get(route('employee.pre-test.show', ['training' => 1]))
            ->assertRedirect(route('employee.material.index', ['training' => 1]));
    }

    public function test_employee_can_submit_pre_test_with_mcq_and_essay(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        $response = $this->actingAs($employeeUser)
            ->post(route('employee.pre-test.submit', ['training' => 1]), [
                'answers' => [
                    [
                        'question_id' => 1,
                        'selected_option_id' => 1,
                        'essay_answer' => null,
                    ],
                    [
                        'question_id' => 2,
                        'selected_option_id' => null,
                        'essay_answer' => 'Helm, sarung tangan, sepatu safety',
                    ],
                ],
            ]);

        $response->assertRedirect(route('employee.material.index', ['training' => 1]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('test_attempts', [
            'employee_id' => 1,
            'training_id' => 1,
            'test_type' => 'pre_test',
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('test_answers', [
            'question_id' => 1,
            'selected_option_id' => 1,
        ]);

        $this->assertDatabaseHas('test_answers', [
            'question_id' => 2,
            'essay_answer' => 'Helm, sarung tangan, sepatu safety',
        ]);
    }

    public function test_pre_test_submit_updates_progress(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        $this->actingAs($employeeUser)
            ->post(route('employee.pre-test.submit', ['training' => 1]), [
                'answers' => [
                    [
                        'question_id' => 1,
                        'selected_option_id' => 1,
                        'essay_answer' => null,
                    ],
                    [
                        'question_id' => 2,
                        'selected_option_id' => null,
                        'essay_answer' => 'Jawaban essay',
                    ],
                ],
            ]);

        $progress = DB::table('employee_training_progress')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->first();

        $this->assertNotNull($progress->pre_test_completed_at);
        $this->assertEquals('pre_test_completed', $progress->status);
    }

    public function test_pre_test_mcq_scoring_persists_correctness_and_scores(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        $this->actingAs($employeeUser)
            ->post(route('employee.pre-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 1, 'essay_answer' => null],
                    ['question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Jawaban essay'],
                ],
            ]);

        $this->assertDatabaseHas('test_answers', [
            'question_id' => 1,
            'selected_option_id' => 1,
            'is_correct' => 1,
            'score' => 1,
        ]);

        $attempt = DB::table('test_attempts')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->where('test_type', 'pre_test')
            ->first();

        $this->assertEquals(100.0, (float) $attempt->mcq_score);
        $this->assertEquals('waiting_manual_review', $attempt->grading_status);
        $this->assertNull($attempt->final_score);
    }

    public function test_pre_test_invalid_option_is_scored_zero_without_error(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        $response = $this->actingAs($employeeUser)
            ->post(route('employee.pre-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 99999, 'essay_answer' => null],
                    ['question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Jawaban essay'],
                ],
            ]);

        $response->assertRedirect(route('employee.material.index', ['training' => 1]));

        $this->assertDatabaseHas('test_answers', [
            'question_id' => 1,
            'selected_option_id' => null,
            'is_correct' => 0,
            'score' => 0,
        ]);
    }

    public function test_pre_test_double_submit_is_prevented(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        // First submit
        $this->actingAs($employeeUser)
            ->post(route('employee.pre-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 1, 'essay_answer' => null],
                    ['question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Jawaban'],
                ],
            ]);

        // Second submit should fail
        $this->actingAs($employeeUser)
            ->post(route('employee.pre-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 2, 'essay_answer' => null],
                    ['question_id' => 2, 'selected_option_id' => null, 'essay_answer' => 'Jawaban lain'],
                ],
            ])
            ->assertSessionHas('error');

        // Only one attempt should exist
        $count = DB::table('test_attempts')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->where('test_type', 'pre_test')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_pre_test_submit_fails_when_not_all_questions_answered(): void
    {
        [$employeeUser] = $this->seedPreTestData();

        // Only answer question 1, skip question 2
        $this->actingAs($employeeUser)
            ->post(route('employee.pre-test.submit', ['training' => 1]), [
                'answers' => [
                    ['question_id' => 1, 'selected_option_id' => 1, 'essay_answer' => null],
                ],
            ])
            ->assertSessionHas('error');
    }

    public function test_admin_cannot_access_pre_test_pages(): void
    {
        [, $adminUser] = $this->seedPreTestData();

        $this->actingAs($adminUser)
            ->get(route('employee.pre-test.show', ['training' => 1]))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($adminUser)
            ->post(route('employee.pre-test.submit', ['training' => 1]))
            ->assertRedirect(route('admin.dashboard'));
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedPreTestData(): array
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
            'username' => 'budi-pretest',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-pretest',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-300', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 1,
                'title' => 'Training Keselamatan Kerja',
                'description' => 'Training dengan pre-test aktif',
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
                'title' => 'Training Tanpa Pre-Test',
                'description' => 'Training tanpa pre-test',
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
            ['id' => 2, 'training_id' => 2, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(1)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => 1,
                'training_id' => 1,
                'assignment_id' => 1,
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

        // Pre-test questions: 1 MCQ + 1 Essay
        DB::table('questions')->insert([
            [
                'id' => 1,
                'training_id' => 1,
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'Apa yang dimaksud dengan K3?',
                'weight' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'training_id' => 1,
                'test_type' => 'pre_test',
                'question_type' => 'essay',
                'order_number' => 2,
                'question_text' => 'Sebutkan 3 alat pelindung diri',
                'weight' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Post-test question (should NOT appear in pre-test)
            [
                'id' => 3,
                'training_id' => 1,
                'test_type' => 'post_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'Post-test question',
                'weight' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Inactive question (should NOT appear)
            [
                'id' => 4,
                'training_id' => 1,
                'test_type' => 'pre_test',
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
            ['id' => 1, 'question_id' => 1, 'option_label' => 'A', 'option_text' => 'Keselamatan dan Kesehatan Kerja', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'question_id' => 1, 'option_label' => 'B', 'option_text' => 'Kantor dan Kerja', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'question_id' => 1, 'option_label' => 'C', 'option_text' => 'Kualitas dan Kinerja', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [$employeeUser, $adminUser];
    }
}
