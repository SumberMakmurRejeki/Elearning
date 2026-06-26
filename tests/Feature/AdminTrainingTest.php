<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTrainingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_training_index_and_empty_state(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.training.index'))
            ->assertOk()
            ->assertSeeText('Daftar Training')
            ->assertSeeText('Belum ada data training.')
            ->assertSeeText('Tambah Training');
    }

    public function test_admin_can_create_training_and_it_is_saved_as_draft(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.training.store'), $this->validPayload())
            ->assertRedirect(route('admin.training.index'));

        $this->assertDatabaseHas('trainings', [
            'title' => 'Training K3 Dasar',
            'description' => 'Pelatihan keselamatan kerja dasar untuk karyawan baru.',
            'status' => 'draft',
            'has_pre_test' => true,
            'has_post_test' => true,
            'passing_grade' => 80,
            'allow_post_test_retake' => true,
            'max_post_test_attempt' => 2,
            'show_score_to_employee' => true,
            'created_by' => $admin->id,
        ]);
    }

    public function test_training_create_validation_errors_are_returned(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('admin.training.create'))
            ->post(route('admin.training.store'), [
                'title' => '',
                'description' => 'Deskripsi training',
                'start_date' => '2026-07-10',
                'end_date' => '2026-07-01',
                'has_pre_test' => 0,
                'has_post_test' => 1,
                'passing_grade' => '',
                'allow_post_test_retake' => 1,
                'max_post_test_attempt' => '',
                'show_score_to_employee' => '',
            ])
            ->assertRedirect(route('admin.training.create'))
            ->assertSessionHasErrors([
                'title' => 'Judul training wajib diisi.',
                'end_date' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
                'passing_grade' => 'Passing grade wajib diisi jika post-test aktif.',
                'max_post_test_attempt' => 'Jumlah maksimal percobaan wajib diisi jika pengulangan post-test diizinkan.',
                'show_score_to_employee' => 'Pengaturan tampilkan nilai wajib dipilih.',
            ]);
    }

    public function test_admin_can_edit_training(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[0];

        $this->actingAs($admin)
            ->put(route('admin.training.update', $training['id']), [
                'title' => 'Training K3 Lanjutan',
                'description' => 'Materi lanjutan untuk pelatihan keselamatan kerja.',
                'start_date' => '2026-07-05',
                'end_date' => '2026-07-20',
                'has_pre_test' => 0,
                'has_post_test' => 1,
                'passing_grade' => 85,
                'allow_post_test_retake' => 0,
                'max_post_test_attempt' => '',
                'show_score_to_employee' => 0,
            ])
            ->assertRedirect(route('admin.training.index'));

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'title' => 'Training K3 Lanjutan',
            'description' => 'Materi lanjutan untuk pelatihan keselamatan kerja.',
            'status' => 'draft',
            'has_pre_test' => false,
            'has_post_test' => true,
            'passing_grade' => 85,
            'allow_post_test_retake' => false,
            'max_post_test_attempt' => null,
            'show_score_to_employee' => false,
        ]);
    }

    public function test_admin_can_view_training_show_page(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[0];

        $this->actingAs($admin)
            ->get(route('admin.training.show', $training['id']))
            ->assertOk()
            ->assertSeeText('Detail Training')
            ->assertSeeText($training['title'])
            ->assertSeeText('Materi Training')
            ->assertSeeText('Soal Test')
            ->assertSeeText('Penugasan Training');
    }

    public function test_admin_can_search_and_filter_training_by_status_month_and_year(): void
    {
        $admin = $this->makeAdmin();
        $this->seedTrainings();

        $this->actingAs($admin)
            ->get(route('admin.training.index', [
                'q' => 'Orientasi',
                'status' => 'published',
                'month' => 7,
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertSeeText('Training Orientasi Draft')
            ->assertDontSeeText('Training Keselamatan Kerja')
            ->assertDontSeeText('Training Lama');
    }

    public function test_admin_can_publish_draft_training(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[0];
        $this->insertMaterial($training['id']);
        $this->insertQuestion($training['id'], 'pre_test', 'multiple_choice', 1);
        $this->insertQuestion($training['id'], 'post_test', 'multiple_choice', 2);

        $this->actingAs($admin)
            ->patch(route('admin.training.status', $training['id']), ['status' => 'published'])
            ->assertRedirect();

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'status' => 'published',
        ]);
    }

    public function test_admin_can_archive_published_training(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[1];

        $this->actingAs($admin)
            ->patch(route('admin.training.status', $training['id']), ['status' => 'archived'])
            ->assertRedirect();

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'status' => 'archived',
        ]);
    }

    public function test_admin_cannot_archive_draft_training_directly(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[0];

        $this->actingAs($admin)
            ->from(route('admin.training.show', $training['id']))
            ->patch(route('admin.training.status', $training['id']), ['status' => 'archived'])
            ->assertRedirect(route('admin.training.show', $training['id']))
            ->assertSessionHas('error', 'Status training tidak valid untuk kondisi training saat ini.');

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'status' => 'draft',
        ]);
    }

    public function test_admin_cannot_publish_archived_training_directly(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[2];

        $this->actingAs($admin)
            ->from(route('admin.training.show', $training['id']))
            ->patch(route('admin.training.status', $training['id']), ['status' => 'published'])
            ->assertRedirect(route('admin.training.show', $training['id']))
            ->assertSessionHas('error', 'Status training tidak valid untuk kondisi training saat ini.');

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'status' => 'archived',
        ]);
    }

    public function test_admin_cannot_publish_training_without_materials(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[0];

        $this->actingAs($admin)
            ->from(route('admin.training.show', $training['id']))
            ->patch(route('admin.training.status', $training['id']), ['status' => 'published'])
            ->assertRedirect(route('admin.training.show', $training['id']))
            ->assertSessionHas('error', 'Training belum dapat dipublish karena materi aktif belum tersedia.');

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'status' => 'draft',
        ]);
    }

    public function test_admin_cannot_publish_training_with_pre_test_enabled_without_pre_test_questions(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[0];
        $this->insertMaterial($training['id']);

        $this->actingAs($admin)
            ->from(route('admin.training.show', $training['id']))
            ->patch(route('admin.training.status', $training['id']), ['status' => 'published'])
            ->assertRedirect(route('admin.training.show', $training['id']))
            ->assertSessionHas('error', 'Training belum dapat dipublish karena soal pre-test aktif belum tersedia.');

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'status' => 'draft',
        ]);
    }

    public function test_admin_cannot_publish_training_with_post_test_enabled_without_post_test_questions(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[0];
        $this->insertMaterial($training['id']);
        $this->insertQuestion($training['id'], 'pre_test', 'multiple_choice', 1);

        $this->actingAs($admin)
            ->from(route('admin.training.show', $training['id']))
            ->patch(route('admin.training.status', $training['id']), ['status' => 'published'])
            ->assertRedirect(route('admin.training.show', $training['id']))
            ->assertSessionHas('error', 'Training belum dapat dipublish karena soal post-test aktif belum tersedia.');

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_publish_ready_training(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[0];
        $this->insertMaterial($training['id']);
        $this->insertQuestion($training['id'], 'pre_test', 'multiple_choice', 1);
        $this->insertQuestion($training['id'], 'post_test', 'multiple_choice', 2);

        $this->actingAs($admin)
            ->patch(route('admin.training.status', $training['id']), ['status' => 'published'])
            ->assertRedirect();

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
            'status' => 'published',
        ]);
    }

    public function test_admin_cannot_delete_training_that_has_related_data(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[1];
        $now = Carbon::now();

        DB::table('training_materials')->insert([
            'training_id' => $training['id'],
            'title' => 'Modul K3',
            'material_type' => 'file',
            'file_path' => 'training/modul-k3.pdf',
            'url' => null,
            'file_type' => 'pdf',
            'file_size' => 1024,
            'order_number' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.training.destroy', $training['id']))
            ->assertRedirect();

        $this->assertDatabaseHas('trainings', [
            'id' => $training['id'],
        ]);
    }

    public function test_admin_can_delete_unused_training_permanently(): void
    {
        $admin = $this->makeAdmin();
        $training = $this->seedTrainings()[2];

        $this->actingAs($admin)
            ->delete(route('admin.training.destroy', $training['id']))
            ->assertRedirect(route('admin.training.index'));

        $this->assertDatabaseMissing('trainings', [
            'id' => $training['id'],
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-training',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'title' => 'Training K3 Dasar',
            'description' => 'Pelatihan keselamatan kerja dasar untuk karyawan baru.',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
            'has_pre_test' => 1,
            'has_post_test' => 1,
            'passing_grade' => 80,
            'allow_post_test_retake' => 1,
            'max_post_test_attempt' => 2,
            'show_score_to_employee' => 1,
        ];
    }

    /**
     * @return array<int, array{id:int,title:string,status:string,start_date:string,end_date:string}>
     */
    private function seedTrainings(): array
    {
        $now = Carbon::now();
        $admin = User::factory()->create([
            'name' => 'Seed Admin Training',
            'username' => 'seed-admin-training',
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
                'has_pre_test' => true,
                'has_post_test' => true,
                'passing_grade' => 75,
                'allow_post_test_retake' => true,
                'max_post_test_attempt' => 3,
                'show_score_to_employee' => true,
                'created_by' => $admin->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'title' => 'Training Orientasi Draft',
                'description' => 'Training orientasi untuk karyawan baru.',
                'start_date' => '2026-07-05',
                'end_date' => '2026-07-18',
                'status' => 'published',
                'has_pre_test' => false,
                'has_post_test' => true,
                'passing_grade' => 70,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => null,
                'show_score_to_employee' => false,
                'created_by' => $admin->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'title' => 'Training Lama',
                'description' => 'Training yang sudah tidak aktif.',
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-31',
                'status' => 'archived',
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
            ['id' => 1, 'title' => 'Training Keselamatan Kerja', 'status' => 'draft', 'start_date' => '2026-07-01', 'end_date' => '2026-07-15'],
            ['id' => 2, 'title' => 'Training Orientasi Draft', 'status' => 'published', 'start_date' => '2026-07-05', 'end_date' => '2026-07-18'],
            ['id' => 3, 'title' => 'Training Lama', 'status' => 'archived', 'start_date' => '2026-05-01', 'end_date' => '2026-05-31'],
        ];
    }

    private function insertMaterial(int $trainingId): void
    {
        $now = Carbon::now();

        DB::table('training_materials')->insert([
            'training_id' => $trainingId,
            'title' => 'Materi Training',
            'material_type' => 'file',
            'file_path' => 'training/materi.pdf',
            'url' => null,
            'file_type' => 'pdf',
            'file_size' => 1024,
            'order_number' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function insertQuestion(int $trainingId, string $testType, string $questionType, int $orderNumber): void
    {
        $now = Carbon::now();

        DB::table('questions')->insert([
            'training_id' => $trainingId,
            'test_type' => $testType,
            'question_type' => $questionType,
            'order_number' => $orderNumber,
            'question_text' => 'Pertanyaan '.$testType.' '.$orderNumber,
            'weight' => 10,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
