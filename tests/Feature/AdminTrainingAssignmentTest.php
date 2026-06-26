<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTrainingAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_assignment_index_and_empty_state(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.penugasan.index'))
            ->assertOk()
            ->assertSeeText('Penugasan Training')
            ->assertSeeText('Belum ada data penugasan training.')
            ->assertSeeText('Tambah Penugasan');
    }

    public function test_admin_can_assign_training_to_selected_employees(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $employees = $this->seedEmployees();

        $this->actingAs($admin)
            ->post(route('admin.penugasan.store'), [
                'training_id' => $trainingId,
                'target_type' => 'employee',
                'employee_ids' => [$employees[0]['id'], $employees[1]['id']],
            ])
            ->assertRedirect(route('admin.penugasan.index'))
            ->assertSessionHas('success', 'Penugasan berhasil untuk 2 karyawan.');

        $this->assertDatabaseCount('training_assignments', 2);
        $this->assertDatabaseHas('training_assignments', [
            'training_id' => $trainingId,
            'target_type' => 'employee',
            'target_id' => $employees[0]['id'],
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('employee_training_progress', [
            'employee_id' => $employees[0]['id'],
            'training_id' => $trainingId,
            'status' => 'not_started',
        ]);
        $this->assertDatabaseHas('employee_training_progress', [
            'employee_id' => $employees[1]['id'],
            'training_id' => $trainingId,
            'status' => 'not_started',
        ]);
    }

    public function test_admin_can_assign_training_to_division_and_skip_inactive_or_duplicate_employees(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $employees = $this->seedEmployees();

        DB::table('employee_training_progress')->insert([
            'employee_id' => $employees[0]['id'],
            'training_id' => $trainingId,
            'assignment_id' => null,
            'status' => 'not_started',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.penugasan.store'), [
                'training_id' => $trainingId,
                'target_type' => 'division',
                'division_id' => $employees[0]['division_id'],
            ])
            ->assertRedirect(route('admin.penugasan.index'))
            ->assertSessionHas('success', 'Penugasan berhasil untuk 1 karyawan. 1 karyawan dilewati karena sudah pernah ditugaskan.');

        $this->assertDatabaseCount('training_assignments', 1);
        $this->assertDatabaseHas('employee_training_progress', [
            'employee_id' => $employees[1]['id'],
            'training_id' => $trainingId,
            'status' => 'not_started',
        ]);
        $this->assertDatabaseMissing('employee_training_progress', [
            'employee_id' => $employees[2]['id'],
            'training_id' => $trainingId,
        ]);
    }

    public function test_admin_can_assign_training_to_position(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $employees = $this->seedEmployees();

        $this->actingAs($admin)
            ->post(route('admin.penugasan.store'), [
                'training_id' => $trainingId,
                'target_type' => 'position',
                'position_id' => $employees[1]['position_id'],
            ])
            ->assertRedirect(route('admin.penugasan.index'))
            ->assertSessionHas('success', 'Penugasan berhasil untuk 1 karyawan.');

        $this->assertDatabaseHas('training_assignments', [
            'training_id' => $trainingId,
            'target_type' => 'position',
            'target_id' => $employees[1]['position_id'],
        ]);
        $this->assertDatabaseHas('employee_training_progress', [
            'employee_id' => $employees[1]['id'],
            'training_id' => $trainingId,
            'status' => 'not_started',
        ]);
    }

    public function test_assignment_validation_errors_are_returned(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('admin.penugasan.create'))
            ->post(route('admin.penugasan.store'), [
                'training_id' => '',
                'target_type' => '',
                'employee_ids' => [],
            ])
            ->assertRedirect(route('admin.penugasan.create'))
            ->assertSessionHasErrors([
                'training_id' => 'Training wajib dipilih.',
                'target_type' => 'Target penugasan wajib dipilih.',
            ]);
    }

    public function test_assignment_requires_published_training(): void
    {
        $admin = $this->makeAdmin();
        $draftTrainingId = $this->seedDraftTraining();
        $employees = $this->seedEmployees();

        $this->actingAs($admin)
            ->from(route('admin.penugasan.create'))
            ->post(route('admin.penugasan.store'), [
                'training_id' => $draftTrainingId,
                'target_type' => 'employee',
                'employee_ids' => [$employees[0]['id']],
            ])
            ->assertRedirect(route('admin.penugasan.create'))
            ->assertSessionHasErrors([
                'training_id' => 'Training belum dapat ditugaskan.',
            ]);
    }

    public function test_assignment_requires_target_specific_selection(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $this->seedEmployees();

        $this->actingAs($admin)
            ->from(route('admin.penugasan.create'))
            ->post(route('admin.penugasan.store'), [
                'training_id' => $trainingId,
                'target_type' => 'division',
            ])
            ->assertRedirect(route('admin.penugasan.create'))
            ->assertSessionHasErrors([
                'division_id' => 'Divisi wajib dipilih.',
            ]);
    }

    public function test_assignment_shows_message_when_all_targets_already_have_training(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $employees = $this->seedEmployees();

        foreach ([$employees[0]['id'], $employees[1]['id']] as $employeeId) {
            DB::table('employee_training_progress')->insert([
                'employee_id' => $employeeId,
                'training_id' => $trainingId,
                'assignment_id' => null,
                'status' => 'not_started',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->actingAs($admin)
            ->post(route('admin.penugasan.store'), [
                'training_id' => $trainingId,
                'target_type' => 'employee',
                'employee_ids' => [$employees[0]['id'], $employees[1]['id']],
            ])
            ->assertRedirect(route('admin.penugasan.index'))
            ->assertSessionHas('error', 'Semua karyawan terpilih sudah memiliki penugasan training ini.');
    }

    public function test_admin_can_search_and_filter_assignments(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $employees = $this->seedEmployees();
        $assignmentId = $this->seedAssignmentForEmployee($trainingId, $employees[0]['id'], 'not_started');
        $this->seedAssignmentForEmployee($trainingId, $employees[1]['id'], 'in_material');

        $this->actingAs($admin)
            ->get(route('admin.penugasan.index', [
                'training_id' => $trainingId,
                'division_id' => $employees[0]['division_id'],
                'position_id' => $employees[0]['position_id'],
                'status' => 'not_started',
                'q' => 'Budi',
            ]))
            ->assertOk()
            ->assertSeeText('Budi Santoso')
            ->assertDontSeeText('Siti Aktif');

        $this->assertDatabaseHas('employee_training_progress', ['assignment_id' => $assignmentId]);
    }

    public function test_admin_can_view_assignment_detail(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $employees = $this->seedEmployees();
        $assignmentId = $this->seedAssignmentForEmployee($trainingId, $employees[0]['id'], 'not_started');
        $progressId = DB::table('employee_training_progress')->where('assignment_id', $assignmentId)->value('id');

        $this->actingAs($admin)
            ->get(route('admin.penugasan.show', $progressId))
            ->assertOk()
            ->assertSeeText('Detail Penugasan Training')
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('Belum Mulai');
    }

    public function test_admin_can_cancel_assignment_when_not_started(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $employees = $this->seedEmployees();
        $assignmentId = $this->seedAssignmentForEmployee($trainingId, $employees[0]['id'], 'not_started');
        $progressId = DB::table('employee_training_progress')->where('assignment_id', $assignmentId)->value('id');

        $this->actingAs($admin)
            ->delete(route('admin.penugasan.destroy', $progressId))
            ->assertRedirect(route('admin.penugasan.index'))
            ->assertSessionHas('success', 'Penugasan training berhasil dibatalkan.');

        $this->assertDatabaseMissing('employee_training_progress', ['id' => $progressId]);
        $this->assertDatabaseMissing('training_assignments', ['id' => $assignmentId]);
    }

    public function test_admin_cannot_cancel_assignment_when_training_has_started(): void
    {
        $admin = $this->makeAdmin();
        $trainingId = $this->seedPublishedTraining();
        $employees = $this->seedEmployees();
        $assignmentId = $this->seedAssignmentForEmployee($trainingId, $employees[0]['id'], 'in_material');
        $progressId = DB::table('employee_training_progress')->where('assignment_id', $assignmentId)->value('id');

        $this->actingAs($admin)
            ->delete(route('admin.penugasan.destroy', $progressId))
            ->assertRedirect()
            ->assertSessionHas('error', 'Penugasan tidak dapat dibatalkan karena karyawan sudah memulai training.');

        $this->assertDatabaseHas('employee_training_progress', ['id' => $progressId]);
        $this->assertDatabaseHas('training_assignments', ['id' => $assignmentId]);
    }

    public function test_employee_cannot_access_admin_assignment_page(): void
    {
        $employeeUser = User::factory()->create([
            'name' => 'Karyawan Penugasan',
            'username' => 'karyawan-penugasan',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->actingAs($employeeUser)
            ->get(route('admin.penugasan.index'))
            ->assertRedirect(route('employee.dashboard'));
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Penugasan',
            'username' => 'admin-penugasan',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function seedPublishedTraining(): int
    {
        $now = Carbon::now();
        $admin = User::factory()->create([
            'name' => 'Seed Admin Assignment',
            'username' => 'seed-admin-assignment',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('trainings')->insert([
            'id' => 1,
            'title' => 'Training Keselamatan Kerja',
            'description' => 'Training dasar keselamatan kerja.',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
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
        ]);

        return 1;
    }

    private function seedDraftTraining(): int
    {
        $now = Carbon::now();
        $admin = User::factory()->create([
            'name' => 'Seed Admin Draft Assignment',
            'username' => 'seed-admin-draft-assignment',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('trainings')->insert([
            'id' => 2,
            'title' => 'Training Draft',
            'description' => 'Training draft.',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
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
        ]);

        return 2;
    }

    /**
     * @return array<int, array{id:int,name:string,division_id:int,position_id:int,is_active:bool}>
     */
    private function seedEmployees(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'Divisi HRD', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Operasional', 'description' => 'Divisi Operasional', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Supervisor', 'description' => 'Supervisor', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $users = [
            User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budi-assignment', 'role' => 'karyawan', 'is_active' => true, 'password' => 'password']),
            User::factory()->create(['name' => 'Siti Aktif', 'username' => 'siti-active-assignment', 'role' => 'karyawan', 'is_active' => true, 'password' => 'password']),
            User::factory()->create(['name' => 'Joko Nonaktif', 'username' => 'joko-inactive-assignment', 'role' => 'karyawan', 'is_active' => false, 'password' => 'password']),
        ];

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $users[0]->id, 'employee_number' => 'EMP-001', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'user_id' => $users[1]->id, 'employee_number' => 'EMP-002', 'division_id' => 1, 'position_id' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'user_id' => $users[2]->id, 'employee_number' => 'EMP-003', 'division_id' => 1, 'position_id' => 2, 'is_active' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [
            ['id' => 1, 'name' => 'Budi Santoso', 'division_id' => 1, 'position_id' => 1, 'is_active' => true],
            ['id' => 2, 'name' => 'Siti Aktif', 'division_id' => 1, 'position_id' => 2, 'is_active' => true],
            ['id' => 3, 'name' => 'Joko Nonaktif', 'division_id' => 1, 'position_id' => 2, 'is_active' => false],
        ];
    }

    private function seedAssignmentForEmployee(int $trainingId, int $employeeId, string $status): int
    {
        $now = Carbon::now();
        $admin = User::factory()->create([
            'name' => 'Seed Creator Assignment',
            'username' => 'seed-creator-assignment-'.$employeeId.'-'.$status,
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('training_assignments')->insert([
            'id' => $employeeId,
            'training_id' => $trainingId,
            'target_type' => 'employee',
            'target_id' => $employeeId,
            'assigned_at' => $now->toDateString(),
            'deadline' => $now->copy()->addDays(30)->toDateString(),
            'is_active' => true,
            'created_by' => $admin->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('employee_training_progress')->insert([
            'employee_id' => $employeeId,
            'training_id' => $trainingId,
            'assignment_id' => $employeeId,
            'status' => $status,
            'pre_test_completed_at' => null,
            'material_completed_at' => null,
            'post_test_completed_at' => null,
            'final_score' => null,
            'final_status' => null,
            'completed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $employeeId;
    }
}
