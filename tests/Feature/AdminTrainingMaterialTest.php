<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTrainingMaterialTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_material_index_and_empty_state(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.materi.index'))
            ->assertOk()
            ->assertSeeText('Materi Training')
            ->assertSeeText('Belum ada data materi training.')
            ->assertSeeText('Tambah Materi');
    }

    public function test_admin_can_filter_materials_by_training_query_type_and_status(): void
    {
        $admin = $this->makeAdmin();
        [$trainingA, $trainingB] = $this->seedTrainings();

        DB::table('training_materials')->insert([
            [
                'id' => 1,
                'training_id' => $trainingA['id'],
                'title' => 'Modul K3 PDF',
                'description' => 'Materi file keselamatan kerja.',
                'material_type' => 'file',
                'file_path' => 'training-materials/1/modul-k3.pdf',
                'url' => null,
                'file_type' => 'pdf',
                'file_size' => 1024,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'training_id' => $trainingB['id'],
                'title' => 'Video Orientasi',
                'description' => 'Materi video onboarding.',
                'material_type' => 'link',
                'file_path' => null,
                'url' => 'https://www.youtube.com/watch?v=abc123',
                'file_type' => null,
                'file_size' => null,
                'order_number' => 2,
                'is_active' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.materi.index', [
                'training_id' => $trainingA['id'],
                'q' => 'Modul',
                'material_type' => 'file',
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSeeText('Modul K3 PDF')
            ->assertDontSeeText('Video Orientasi');
    }

    public function test_admin_can_create_file_material_and_store_file_privately(): void
    {
        Storage::fake('local');

        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $file = UploadedFile::fake()->create('handbook.pdf', 1024, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.materi.store'), [
                'training_id' => $training['id'],
                'title' => 'Handbook K3',
                'description' => 'Buku panduan keselamatan kerja.',
                'material_type' => 'file',
                'file' => $file,
                'url' => '',
                'order_number' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.materi.index'));

        $material = DB::table('training_materials')->where('title', 'Handbook K3')->first();

        $this->assertNotNull($material);
        $this->assertSame('file', $material->material_type);
        $this->assertNotNull($material->file_path);
        $this->assertNull($material->url);
        Storage::disk('local')->assertExists($material->file_path);
    }

    public function test_admin_can_create_link_material(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $this->actingAs($admin)
            ->post(route('admin.materi.store'), [
                'training_id' => $training['id'],
                'title' => 'Video YouTube',
                'description' => 'Materi link video pelatihan.',
                'material_type' => 'link',
                'url' => 'https://www.youtube.com/watch?v=abc123',
                'order_number' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.materi.index'));

        $this->assertDatabaseHas('training_materials', [
            'training_id' => $training['id'],
            'title' => 'Video YouTube',
            'description' => 'Materi link video pelatihan.',
            'material_type' => 'link',
            'url' => 'https://www.youtube.com/watch?v=abc123',
            'file_path' => null,
            'is_active' => true,
        ]);
    }

    public function test_material_create_validation_errors_are_returned(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('admin.materi.create'))
            ->post(route('admin.materi.store'), [
                'training_id' => '',
                'title' => '',
                'description' => 'Materi tanpa training.',
                'material_type' => 'file',
                'order_number' => 'abc',
                'is_active' => '',
            ])
            ->assertRedirect(route('admin.materi.create'))
            ->assertSessionHasErrors([
                'training_id' => 'Training wajib dipilih.',
                'title' => 'Judul materi wajib diisi.',
                'file' => 'File materi wajib diupload.',
                'order_number' => 'Urutan materi harus berupa angka.',
                'is_active' => 'Status materi wajib dipilih.',
            ]);
    }

    public function test_material_create_rejects_invalid_file_format(): void
    {
        Storage::fake('local');

        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $file = UploadedFile::fake()->create('virus.exe', 200, 'application/octet-stream');

        $this->actingAs($admin)
            ->from(route('admin.materi.create'))
            ->post(route('admin.materi.store'), [
                'training_id' => $training['id'],
                'title' => 'File Tidak Valid',
                'description' => '',
                'material_type' => 'file',
                'file' => $file,
                'order_number' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.materi.create'))
            ->assertSessionHasErrors([
                'file' => 'Format file tidak didukung.',
            ]);
    }

    public function test_material_create_rejects_oversized_image_file(): void
    {
        Storage::fake('local');

        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $file = UploadedFile::fake()->image('poster.jpg')->size(6000);

        $this->actingAs($admin)
            ->from(route('admin.materi.create'))
            ->post(route('admin.materi.store'), [
                'training_id' => $training['id'],
                'title' => 'Poster Besar',
                'description' => '',
                'material_type' => 'file',
                'file' => $file,
                'order_number' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.materi.create'))
            ->assertSessionHasErrors([
                'file' => 'Ukuran file terlalu besar.',
            ]);
    }

    public function test_material_create_requires_url_when_type_is_link(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();

        $this->actingAs($admin)
            ->from(route('admin.materi.create'))
            ->post(route('admin.materi.store'), [
                'training_id' => $training['id'],
                'title' => 'Link Materi',
                'description' => '',
                'material_type' => 'link',
                'url' => '',
                'order_number' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.materi.create'))
            ->assertSessionHasErrors([
                'url' => 'URL materi wajib diisi.',
            ]);
    }

    public function test_admin_can_edit_material_and_switch_from_file_to_link_safely(): void
    {
        Storage::fake('local');

        $admin = $this->makeAdmin();
        [$trainingA, $trainingB] = $this->seedTrainings();
        $material = $this->seedFileMaterial($trainingA['id']);

        Storage::disk('local')->put($material['file_path'], 'old file');

        $this->actingAs($admin)
            ->put(route('admin.materi.update', $material['id']), [
                'training_id' => $trainingB['id'],
                'title' => 'Link Handbook',
                'description' => 'Materi dipindah menjadi link.',
                'material_type' => 'link',
                'url' => 'https://drive.google.com/file/d/123/view',
                'order_number' => 5,
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.materi.index'));

        $this->assertDatabaseHas('training_materials', [
            'id' => $material['id'],
            'training_id' => $trainingB['id'],
            'title' => 'Link Handbook',
            'description' => 'Materi dipindah menjadi link.',
            'material_type' => 'link',
            'url' => 'https://drive.google.com/file/d/123/view',
            'file_path' => null,
            'file_type' => null,
            'file_size' => null,
            'order_number' => 5,
            'is_active' => false,
        ]);

        Storage::disk('local')->assertMissing($material['file_path']);
    }

    public function test_admin_can_view_material_show_page(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $material = $this->seedLinkMaterial($training['id']);

        $this->actingAs($admin)
            ->get(route('admin.materi.show', $material['id']))
            ->assertOk()
            ->assertSeeText('Detail Materi')
            ->assertSeeText($material['title'])
            ->assertSeeText($training['title'])
            ->assertSeeText('Link Eksternal');
    }

    public function test_admin_can_toggle_material_status(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $material = $this->seedLinkMaterial($training['id']);

        $this->actingAs($admin)
            ->patch(route('admin.materi.status', $material['id']), ['is_active' => 0])
            ->assertRedirect();

        $this->assertDatabaseHas('training_materials', [
            'id' => $material['id'],
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_delete_material_that_has_been_accessed(): void
    {
        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $material = $this->seedLinkMaterial($training['id']);
        $employee = $this->seedEmployee();

        DB::table('material_access_logs')->insert([
            'employee_id' => $employee['id'],
            'training_id' => $training['id'],
            'material_id' => $material['id'],
            'opened_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.materi.destroy', $material['id']))
            ->assertRedirect()
            ->assertSessionHas('error', 'Materi tidak dapat dihapus karena sudah pernah diakses oleh karyawan. Silakan nonaktifkan materi saja.');

        $this->assertDatabaseHas('training_materials', [
            'id' => $material['id'],
        ]);
    }

    public function test_admin_can_delete_unused_material_and_remove_file_from_private_storage(): void
    {
        Storage::fake('local');

        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $material = $this->seedFileMaterial($training['id']);
        Storage::disk('local')->put($material['file_path'], 'delete me');

        $this->actingAs($admin)
            ->delete(route('admin.materi.destroy', $material['id']))
            ->assertRedirect(route('admin.materi.index'));

        $this->assertDatabaseMissing('training_materials', [
            'id' => $material['id'],
        ]);
        Storage::disk('local')->assertMissing($material['file_path']);
    }

    public function test_admin_can_preview_and_download_private_material_file(): void
    {
        Storage::fake('local');

        $admin = $this->makeAdmin();
        [$training] = $this->seedTrainings();
        $material = $this->seedFileMaterial($training['id']);
        Storage::disk('local')->put($material['file_path'], 'pdf content');

        $this->actingAs($admin)
            ->get(route('admin.materi.preview-file', $material['id']))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.materi.download-file', $material['id']))
            ->assertOk()
            ->assertDownload('handbook-k3.pdf');
    }

    public function test_employee_cannot_access_admin_material_page(): void
    {
        $employeeUser = User::factory()->create([
            'name' => 'Karyawan Satu',
            'username' => 'karyawan-materi',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->actingAs($employeeUser)
            ->get(route('admin.materi.index'))
            ->assertRedirect(route('employee.dashboard'));
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Materi',
            'username' => 'admin-materi',
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
            'name' => 'Seed Admin Materi',
            'username' => 'seed-admin-materi',
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

    /**
     * @return array{id:int,file_path:string,title:string}
     */
    private function seedFileMaterial(int $trainingId): array
    {
        $now = Carbon::now();
        $filePath = 'training-materials/'.$trainingId.'/handbook-k3.pdf';

        DB::table('training_materials')->insert([
            'id' => 1,
            'training_id' => $trainingId,
            'title' => 'Handbook Lama',
            'description' => 'Materi file lama.',
            'material_type' => 'file',
            'file_path' => $filePath,
            'url' => null,
            'file_type' => 'pdf',
            'file_size' => 1024,
            'order_number' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'id' => 1,
            'file_path' => $filePath,
            'title' => 'Handbook Lama',
        ];
    }

    /**
     * @return array{id:int,title:string}
     */
    private function seedLinkMaterial(int $trainingId): array
    {
        $now = Carbon::now();

        DB::table('training_materials')->insert([
            'id' => 1,
            'training_id' => $trainingId,
            'title' => 'Link Eksternal',
            'description' => 'Materi link eksternal.',
            'material_type' => 'link',
            'file_path' => null,
            'url' => 'https://example.com/materi-training',
            'file_type' => null,
            'file_size' => null,
            'order_number' => 3,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'id' => 1,
            'title' => 'Link Eksternal',
        ];
    }

    /**
     * @return array{id:int}
     */
    private function seedEmployee(): array
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
            'username' => 'budi-material',
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

        return ['id' => 1];
    }
}
