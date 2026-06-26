<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminQuestionBankTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_question_index_and_empty_state(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.soal.index'))
            ->assertOk()
            ->assertSeeText('Soal Test')
            ->assertSeeText('Belum ada data soal test.')
            ->assertSeeText('Tambah Soal');
    }

    public function test_admin_can_filter_questions_by_training_test_type_question_type_status_and_query(): void
    {
        $admin = $this->makeAdmin();
        [$trainingA, $trainingB] = $this->seedTrainings();
        $this->seedQuestions($trainingA['id'], $trainingB['id']);

        $this->actingAs($admin)
            ->get(route('admin.soal.index', [
                'training_id' => $trainingA['id'],
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'status' => 'active',
                'q' => 'APD',
            ]))
            ->assertOk()
            ->assertSeeText('APD yang wajib digunakan?')
            ->assertDontSeeText('Jelaskan prosedur evakuasi')
            ->assertDontSeeText('Pertanyaan onboarding');
    }

    public function test_admin_can_create_multiple_choice_question(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $this->actingAs($admin)
            ->post(route('admin.soal.store'), [
                'training_id' => $training['id'],
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'APD yang wajib digunakan?',
                'weight' => 10,
                'is_active' => 1,
                'options' => [
                    ['option_text' => 'Helm proyek', 'is_correct' => 1],
                    ['option_text' => 'Sandal', 'is_correct' => 0],
                ],
            ])
            ->assertRedirect(route('admin.soal.index'));

        $question = DB::table('questions')->where('question_text', 'APD yang wajib digunakan?')->first();

        $this->assertNotNull($question);
        $this->assertSame('pre_test', $question->test_type);
        $this->assertSame('multiple_choice', $question->question_type);

        $this->assertDatabaseHas('question_options', [
            'question_id' => $question->id,
            'option_label' => 'A',
            'option_text' => 'Helm proyek',
            'is_correct' => true,
        ]);

        $this->assertDatabaseHas('question_options', [
            'question_id' => $question->id,
            'option_label' => 'B',
            'option_text' => 'Sandal',
            'is_correct' => false,
        ]);
    }

    public function test_admin_can_create_essay_question(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $this->actingAs($admin)
            ->post(route('admin.soal.store'), [
                'training_id' => $training['id'],
                'test_type' => 'post_test',
                'question_type' => 'essay',
                'order_number' => 2,
                'question_text' => 'Jelaskan prosedur evakuasi kebakaran.',
                'weight' => 15,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.soal.index'));

        $question = DB::table('questions')->where('question_text', 'Jelaskan prosedur evakuasi kebakaran.')->first();

        $this->assertNotNull($question);
        $this->assertSame('essay', $question->question_type);
        $this->assertDatabaseMissing('question_options', [
            'question_id' => $question->id,
        ]);
    }

    public function test_question_create_validation_errors_are_returned(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('admin.soal.create'))
            ->post(route('admin.soal.store'), [
                'training_id' => '',
                'test_type' => '',
                'question_type' => '',
                'order_number' => '',
                'question_text' => '',
                'weight' => '',
                'is_active' => '',
            ])
            ->assertRedirect(route('admin.soal.create'))
            ->assertSessionHasErrors([
                'training_id' => 'Training wajib dipilih.',
                'test_type' => 'Jenis test wajib dipilih.',
                'question_type' => 'Jenis soal wajib dipilih.',
                'order_number' => 'Nomor soal wajib diisi.',
                'question_text' => 'Pertanyaan wajib diisi.',
                'weight' => 'Bobot nilai wajib diisi.',
                'is_active' => 'Status soal wajib dipilih.',
            ]);
    }

    public function test_multiple_choice_question_requires_at_least_two_options(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $this->actingAs($admin)
            ->from(route('admin.soal.create'))
            ->post(route('admin.soal.store'), [
                'training_id' => $training['id'],
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'APD?',
                'weight' => 10,
                'is_active' => 1,
                'options' => [
                    ['option_text' => 'Helm proyek', 'is_correct' => 1],
                ],
            ])
            ->assertRedirect(route('admin.soal.create'))
            ->assertSessionHasErrors([
                'options' => 'Pilihan jawaban minimal 2 opsi untuk soal pilihan ganda.',
            ]);
    }

    public function test_multiple_choice_question_requires_exactly_one_correct_answer(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $this->actingAs($admin)
            ->from(route('admin.soal.create'))
            ->post(route('admin.soal.store'), [
                'training_id' => $training['id'],
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'APD?',
                'weight' => 10,
                'is_active' => 1,
                'options' => [
                    ['option_text' => 'Helm proyek', 'is_correct' => 1],
                    ['option_text' => 'Sepatu safety', 'is_correct' => 1],
                ],
            ])
            ->assertRedirect(route('admin.soal.create'))
            ->assertSessionHasErrors([
                'options' => 'Soal pilihan ganda harus memiliki tepat 1 jawaban benar.',
            ]);
    }

    public function test_admin_can_edit_question_and_switch_from_multiple_choice_to_essay(): void
    {
        $admin = $this->makeAdmin();
        [$trainingA, $trainingB] = $this->seedTrainings();
        $questionId = $this->seedMultipleChoiceQuestion($trainingA['id']);

        $this->actingAs($admin)
            ->put(route('admin.soal.update', $questionId), [
                'training_id' => $trainingB['id'],
                'test_type' => 'post_test',
                'question_type' => 'essay',
                'order_number' => 3,
                'question_text' => 'Jelaskan alur pelaporan insiden.',
                'weight' => 12,
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.soal.index'));

        $this->assertDatabaseHas('questions', [
            'id' => $questionId,
            'training_id' => $trainingB['id'],
            'test_type' => 'post_test',
            'question_type' => 'essay',
            'order_number' => 3,
            'question_text' => 'Jelaskan alur pelaporan insiden.',
            'weight' => 12,
            'is_active' => false,
        ]);

        $this->assertDatabaseMissing('question_options', [
            'question_id' => $questionId,
        ]);
    }

    public function test_admin_cannot_change_used_question_structure(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $questionId = $this->seedMultipleChoiceQuestion($training['id']);
        $this->seedUsedAttemptForQuestion($training['id'], $questionId);

        $this->actingAs($admin)
            ->from(route('admin.soal.show', $questionId))
            ->put(route('admin.soal.update', $questionId), [
                'training_id' => $training['id'],
                'test_type' => 'post_test',
                'question_type' => 'essay',
                'order_number' => 2,
                'question_text' => 'Pertanyaan berubah',
                'weight' => 20,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.soal.show', $questionId))
            ->assertSessionHas('error', 'Soal tidak dapat diubah karena sudah digunakan dalam test.');
    }

    public function test_admin_can_view_question_show_page(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $questionId = $this->seedMultipleChoiceQuestion($training['id']);

        $this->actingAs($admin)
            ->get(route('admin.soal.show', $questionId))
            ->assertOk()
            ->assertSeeText('Detail Soal')
            ->assertSeeText($training['title'])
            ->assertSeeText('Pilihan Ganda')
            ->assertSeeText('Helm proyek');
    }

    public function test_admin_can_toggle_question_status(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $questionId = $this->seedMultipleChoiceQuestion($training['id']);

        $this->actingAs($admin)
            ->patch(route('admin.soal.status', $questionId), ['is_active' => 0])
            ->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'id' => $questionId,
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_delete_used_question(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $questionId = $this->seedMultipleChoiceQuestion($training['id']);
        $this->seedUsedAttemptForQuestion($training['id'], $questionId);

        $this->actingAs($admin)
            ->delete(route('admin.soal.destroy', $questionId))
            ->assertRedirect()
            ->assertSessionHas('error', 'Soal tidak dapat dihapus karena sudah digunakan dalam test. Silakan nonaktifkan soal saja.');

        $this->assertDatabaseHas('questions', ['id' => $questionId]);
    }

    public function test_admin_can_delete_unused_question_permanently(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $questionId = $this->seedMultipleChoiceQuestion($training['id']);

        $this->actingAs($admin)
            ->delete(route('admin.soal.destroy', $questionId))
            ->assertRedirect(route('admin.soal.index'));

        $this->assertDatabaseMissing('questions', ['id' => $questionId]);
        $this->assertDatabaseMissing('question_options', ['question_id' => $questionId]);
    }

    public function test_employee_cannot_access_admin_question_page(): void
    {
        $employeeUser = User::factory()->create([
            'name' => 'Karyawan Soal',
            'username' => 'karyawan-soal',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->actingAs($employeeUser)
            ->get(route('admin.soal.index'))
            ->assertRedirect(route('employee.dashboard'));
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Soal',
            'username' => 'admin-soal',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    /**
     * @return array<int, array{id:int,title:string}>
     */
    private function seedTrainings(): array
    {
        $now = Carbon::now();
        $admin = User::factory()->create([
            'name' => 'Seed Admin Soal',
            'username' => 'seed-admin-soal',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 1,
                'title' => 'Training Keselamatan Kerja',
                'description' => 'Training dasar keselamatan kerja.',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-15',
                'status' => 'draft',
                'has_pre_test' => false,
                'has_post_test' => false,
                'passing_grade' => null,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => false,
                'created_by' => $admin->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'title' => 'Training Orientasi Karyawan',
                'description' => 'Training orientasi.',
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-15',
                'status' => 'published',
                'has_pre_test' => false,
                'has_post_test' => false,
                'passing_grade' => null,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => false,
                'created_by' => $admin->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        return [
            ['id' => 1, 'title' => 'Training Keselamatan Kerja'],
            ['id' => 2, 'title' => 'Training Orientasi Karyawan'],
        ];
    }

    private function seedQuestions(int $trainingAId, int $trainingBId): void
    {
        $now = Carbon::now();

        DB::table('questions')->insert([
            [
                'id' => 1,
                'training_id' => $trainingAId,
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'APD yang wajib digunakan?',
                'weight' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'training_id' => $trainingAId,
                'test_type' => 'post_test',
                'question_type' => 'essay',
                'order_number' => 2,
                'question_text' => 'Jelaskan prosedur evakuasi',
                'weight' => 15,
                'is_active' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'training_id' => $trainingBId,
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'order_number' => 1,
                'question_text' => 'Pertanyaan onboarding',
                'weight' => 8,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('question_options')->insert([
            ['question_id' => 1, 'option_label' => 'A', 'option_text' => 'Helm proyek', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question_id' => 1, 'option_label' => 'B', 'option_text' => 'Sandal', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
            ['question_id' => 3, 'option_label' => 'A', 'option_text' => 'Jawaban A', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question_id' => 3, 'option_label' => 'B', 'option_text' => 'Jawaban B', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedMultipleChoiceQuestion(int $trainingId): int
    {
        $now = Carbon::now();

        DB::table('questions')->insert([
            'id' => 1,
            'training_id' => $trainingId,
            'test_type' => 'pre_test',
            'question_type' => 'multiple_choice',
            'order_number' => 1,
            'question_text' => 'APD yang wajib digunakan?',
            'weight' => 10,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('question_options')->insert([
            ['question_id' => 1, 'option_label' => 'A', 'option_text' => 'Helm proyek', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['question_id' => 1, 'option_label' => 'B', 'option_text' => 'Sandal', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return 1;
    }

    private function seedUsedAttemptForQuestion(int $trainingId, int $questionId): void
    {
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

        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-soal',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            'id' => 1,
            'user_id' => $user->id,
            'employee_number' => 'EMP-001',
            'division_id' => 1,
            'position_id' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('test_attempts')->insert([
            'id' => 1,
            'employee_id' => 1,
            'training_id' => $trainingId,
            'test_type' => 'pre_test',
            'attempt_number' => 1,
            'status' => 'submitted',
            'started_at' => $now,
            'submitted_at' => $now,
            'mcq_score' => 10,
            'essay_score' => 0,
            'final_score' => 10,
            'grading_status' => 'auto_graded',
            'pass_status' => 'passed',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $selectedOptionId = DB::table('question_options')->where('question_id', $questionId)->where('is_correct', true)->value('id');

        DB::table('test_answers')->insert([
            'attempt_id' => 1,
            'question_id' => $questionId,
            'selected_option_id' => $selectedOptionId,
            'essay_answer' => null,
            'is_correct' => true,
            'score' => 10,
            'graded_by' => null,
            'graded_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
