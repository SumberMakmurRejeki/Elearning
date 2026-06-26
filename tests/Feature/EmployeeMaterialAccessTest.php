<?php

namespace Tests\Feature;

use App\Models\MaterialAccessLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeMaterialAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_open_material_list_and_see_active_materials(): void
    {
        [$employeeUser] = $this->seedMaterialData();

        $this->actingAs($employeeUser)
            ->get(route('employee.material.index', ['training' => 1]))
            ->assertOk()
            ->assertSeeText('Materi Training')
            ->assertSeeText('Modul Keselamatan')
            ->assertSeeText('Video Training')
            ->assertDontSeeText('Materi Nonaktif');
    }

    public function test_employee_material_list_shows_empty_state_when_no_active_materials(): void
    {
        [$employeeUser] = $this->seedMaterialData();

        $this->actingAs($employeeUser)
            ->get(route('employee.material.index', ['training' => 2]))
            ->assertOk()
            ->assertSeeText('Belum ada materi aktif.');
    }

    public function test_employee_can_view_file_material_and_access_is_logged(): void
    {
        [$employeeUser] = $this->seedMaterialData();

        Storage::fake('local');
        Storage::disk('local')->put('trainings/1/modul.pdf', 'fake content');

        $this->actingAs($employeeUser)
            ->get(route('employee.material.view', ['training' => 1, 'material' => 1]))
            ->assertOk();

        $this->assertDatabaseHas('material_access_logs', [
            'employee_id' => 1,
            'training_id' => 1,
            'material_id' => 1,
        ]);
    }

    public function test_employee_can_download_file_material(): void
    {
        [$employeeUser] = $this->seedMaterialData();

        Storage::fake('local');
        Storage::disk('local')->put('trainings/1/modul.pdf', 'fake content');

        $response = $this->actingAs($employeeUser)
            ->get(route('employee.material.download', ['training' => 1, 'material' => 1]));

        $response->assertOk();

        $this->assertDatabaseHas('material_access_logs', [
            'employee_id' => 1,
            'training_id' => 1,
            'material_id' => 1,
        ]);
    }

    public function test_employee_can_open_link_material_and_access_is_logged(): void
    {
        [$employeeUser] = $this->seedMaterialData();

        $this->actingAs($employeeUser)
            ->get(route('employee.material.open-link', ['training' => 1, 'material' => 2]))
            ->assertRedirect('https://example.com/video');

        $this->assertDatabaseHas('material_access_logs', [
            'employee_id' => 1,
            'training_id' => 1,
            'material_id' => 2,
        ]);
    }

    public function test_material_access_updates_progress_when_all_active_materials_opened(): void
    {
        [$employeeUser] = $this->seedMaterialData();

        Storage::fake('local');
        Storage::disk('local')->put('trainings/1/modul.pdf', 'fake content');

        // Open file material
        $this->actingAs($employeeUser)
            ->get(route('employee.material.view', ['training' => 1, 'material' => 1]));

        // Open link material
        $this->actingAs($employeeUser)
            ->get(route('employee.material.open-link', ['training' => 1, 'material' => 2]));

        $this->assertDatabaseHas('material_access_logs', [
            'employee_id' => 1,
            'training_id' => 1,
            'material_id' => 1,
        ]);

        $this->assertDatabaseHas('material_access_logs', [
            'employee_id' => 1,
            'training_id' => 1,
            'material_id' => 2,
        ]);

        $this->assertDatabaseHas('employee_training_progress', [
            'employee_id' => 1,
            'training_id' => 1,
        ]);

        $progress = DB::table('employee_training_progress')
            ->where('employee_id', 1)
            ->where('training_id', 1)
            ->first();

        $this->assertNotNull($progress->material_completed_at);
    }

    public function test_employee_cannot_access_material_from_unassigned_training(): void
    {
        [$employeeUser] = $this->seedMaterialData();

        $this->actingAs($employeeUser)
            ->get(route('employee.material.index', ['training' => 3]))
            ->assertNotFound();
    }

    public function test_employee_cannot_access_inactive_material(): void
    {
        [$employeeUser] = $this->seedMaterialData();

        Storage::fake('local');

        $this->actingAs($employeeUser)
            ->get(route('employee.material.view', ['training' => 1, 'material' => 3]))
            ->assertRedirect(route('employee.material.index', ['training' => 1]))
            ->assertSessionHas('error');
    }

    public function test_admin_cannot_access_employee_material_pages(): void
    {
        [, $adminUser] = $this->seedMaterialData();

        $this->actingAs($adminUser)
            ->get(route('employee.material.index', ['training' => 1]))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($adminUser)
            ->get(route('employee.material.view', ['training' => 1, 'material' => 1]))
            ->assertRedirect(route('admin.dashboard'));
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedMaterialData(): array
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
            'username' => 'budi-material-access',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-material-access',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-200', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 1,
                'title' => 'Training Keselamatan Kerja',
                'description' => 'Training dengan materi aktif',
                'start_date' => $now->copy()->subDays(5)->toDateString(),
                'end_date' => $now->copy()->addDays(10)->toDateString(),
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
            [
                'id' => 2,
                'title' => 'Training Tanpa Materi',
                'description' => 'Training tanpa materi aktif',
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
            ['id' => 2, 'training_id' => 2, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(1)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('training_materials')->insert([
            [
                'id' => 1,
                'training_id' => 1,
                'title' => 'Modul Keselamatan',
                'description' => 'PDF materi keselamatan kerja',
                'material_type' => 'file',
                'file_path' => 'trainings/1/modul.pdf',
                'url' => null,
                'file_type' => 'pdf',
                'file_size' => 1048576,
                'order_number' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'training_id' => 1,
                'title' => 'Video Training',
                'description' => 'Video materi external link',
                'material_type' => 'link',
                'file_path' => null,
                'url' => 'https://example.com/video',
                'file_type' => null,
                'file_size' => null,
                'order_number' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'training_id' => 1,
                'title' => 'Materi Nonaktif',
                'description' => 'Materi yang sudah dinonaktifkan',
                'material_type' => 'file',
                'file_path' => 'trainings/1/old.pdf',
                'url' => null,
                'file_type' => 'pdf',
                'file_size' => 512000,
                'order_number' => 3,
                'is_active' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => 1,
                'training_id' => 1,
                'assignment_id' => 1,
                'status' => 'in_material',
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

        return [$employeeUser, $adminUser];
    }
}
