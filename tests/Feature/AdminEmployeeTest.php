<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_employee_index_and_empty_state(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.karyawan.index'))
            ->assertOk()
            ->assertSeeText('Master Data Karyawan')
            ->assertSeeText('Belum ada data karyawan.')
            ->assertSeeText('Tambah Karyawan');
    }

    public function test_admin_can_create_employee(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();

        $this->actingAs($admin)
            ->post(route('admin.karyawan.store'), [
                'name' => 'Budi Santoso',
                'username' => 'budi.santoso',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'employee_number' => 'EMP-001',
                'division_id' => $reference['division_id'],
                'position_id' => $reference['position_id'],
                'is_active' => true,
                'role' => 'karyawan',
            ])
            ->assertRedirect(route('admin.karyawan.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'budi.santoso',
            'name' => 'Budi Santoso',
            'role' => 'karyawan',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('employees', [
            'employee_number' => 'EMP-001',
            'division_id' => $reference['division_id'],
            'position_id' => $reference['position_id'],
            'is_active' => true,
        ]);
    }

    public function test_employee_create_validation_errors_are_returned(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('admin.karyawan.create'))
            ->post(route('admin.karyawan.store'), [
                'name' => '',
                'username' => '',
                'password' => 'short',
                'password_confirmation' => 'different',
                'employee_number' => '',
                'division_id' => '',
                'position_id' => '',
                'is_active' => '',
            ])
            ->assertRedirect(route('admin.karyawan.create'))
            ->assertSessionHasErrors([
                'name' => 'Nama karyawan wajib diisi.',
                'username' => 'Username wajib diisi.',
                'password' => 'Password minimal 8 karakter.',
                'division_id' => 'Divisi wajib dipilih.',
                'position_id' => 'Jabatan wajib dipilih.',
                'is_active' => 'Status akun wajib dipilih.',
                'role' => 'Role wajib dipilih.',
            ]);
    }

    public function test_admin_can_edit_employee_and_keep_username_unique_rule_ignoring_self(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $employee = $this->makeEmployee($reference, [
            'employee_number' => 'EMP-010',
            'name' => 'Budi Santoso',
            'username' => 'budi.edit',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.karyawan.update', $employee), [
                'name' => 'Budi Santoso Baru',
                'username' => 'budi.edit',
                'password' => '',
                'password_confirmation' => '',
                'employee_number' => 'EMP-011',
                'division_id' => $reference['division_id'],
                'position_id' => $reference['position_id'],
                'is_active' => false,
                'role' => 'karyawan',
            ])
            ->assertRedirect(route('admin.karyawan.index'));

        $this->assertDatabaseHas('users', [
            'id' => $employee->user_id,
            'name' => 'Budi Santoso Baru',
            'username' => 'budi.edit',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'employee_number' => 'EMP-011',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_view_employee_show_page(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $employee = $this->makeEmployee($reference, [
            'employee_number' => 'EMP-020',
            'name' => 'Siti Aminah',
            'username' => 'siti.show',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.karyawan.show', $employee))
            ->assertOk()
            ->assertSeeText('Detail Karyawan')
            ->assertSeeText('Siti Aminah')
            ->assertSeeText('Reset Password');
    }

    public function test_admin_can_filter_employees_by_query_division_position_and_status(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $this->makeEmployee($reference, [
            'employee_number' => 'EMP-100',
            'name' => 'Budi Operasional',
            'username' => 'budi.ops',
            'is_active' => true,
        ]);
        $this->makeEmployee($reference, [
            'employee_number' => 'EMP-200',
            'name' => 'Sari Nonaktif',
            'username' => 'sari.inactive',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.karyawan.index', [
                'q' => 'Sari',
                'division_id' => $reference['division_id'],
                'position_id' => $reference['position_id'],
                'status' => 'inactive',
            ]))
            ->assertOk()
            ->assertSeeText('Sari Nonaktif')
            ->assertDontSeeText('Budi Operasional');
    }

    public function test_admin_can_toggle_employee_status(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $employee = $this->makeEmployee($reference, [
            'employee_number' => 'EMP-030',
            'name' => 'Toggle User',
            'username' => 'toggle.user',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.karyawan.status', $employee), ['is_active' => 0])
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $employee->user_id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_reset_employee_password(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $employee = $this->makeEmployee($reference, [
            'employee_number' => 'EMP-040',
            'name' => 'Password User',
            'username' => 'password.user',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.karyawan.reset-password', $employee), [
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password123', $employee->user->fresh()->password));
    }

    public function test_admin_cannot_delete_employee_with_training_dependencies(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $employee = $this->makeEmployee($reference, [
            'employee_number' => 'EMP-050',
            'name' => 'Linked User',
            'username' => 'linked.user',
        ]);

        $trainingId = DB::table('trainings')->insertGetId([
            'title' => 'Security Awareness',
            'description' => null,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => 'published',
            'has_pre_test' => false,
            'has_post_test' => false,
            'passing_grade' => null,
            'allow_post_test_retake' => false,
            'max_post_test_attempt' => null,
            'show_score_to_employee' => false,
            'created_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('test_attempts')->insert([
            'employee_id' => $employee->id,
            'training_id' => $trainingId,
            'test_type' => 'pre_test',
            'attempt_number' => 1,
            'status' => 'in_progress',
            'started_at' => now(),
            'submitted_at' => null,
            'mcq_score' => 0,
            'essay_score' => 0,
            'final_score' => null,
            'grading_status' => 'auto_graded',
            'pass_status' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.karyawan.destroy', $employee))
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
        ]);
    }

    public function test_admin_can_create_employee_as_admin_role(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();

        $this->actingAs($admin)
            ->post(route('admin.karyawan.store'), [
                'name' => 'Admin Baru',
                'username' => 'admin.baru',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'employee_number' => 'EMP-ADMIN-001',
                'division_id' => $reference['division_id'],
                'position_id' => $reference['position_id'],
                'is_active' => true,
                'role' => 'admin',
            ])
            ->assertRedirect(route('admin.karyawan.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'admin.baru',
            'name' => 'Admin Baru',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_create_employee_as_karyawan_role(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();

        $this->actingAs($admin)
            ->post(route('admin.karyawan.store'), [
                'name' => 'Karyawan Baru',
                'username' => 'karyawan.baru',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'employee_number' => 'EMP-KRY-001',
                'division_id' => $reference['division_id'],
                'position_id' => $reference['position_id'],
                'is_active' => true,
                'role' => 'karyawan',
            ])
            ->assertRedirect(route('admin.karyawan.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'karyawan.baru',
            'role' => 'karyawan',
        ]);
    }

    public function test_employee_create_rejects_invalid_role(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();

        $this->actingAs($admin)
            ->from(route('admin.karyawan.create'))
            ->post(route('admin.karyawan.store'), [
                'name' => 'Bad Role',
                'username' => 'bad.role',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'employee_number' => 'EMP-BAD',
                'division_id' => $reference['division_id'],
                'position_id' => $reference['position_id'],
                'is_active' => true,
                'role' => 'superadmin',
            ])
            ->assertRedirect(route('admin.karyawan.create'))
            ->assertSessionHasErrors('role');
    }

    public function test_admin_can_update_employee_role(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $employee = $this->makeEmployee($reference, [
            'employee_number' => 'EMP-ROLE-001',
            'name' => 'Role User',
            'username' => 'role.user',
        ]);

        // Verify initial role is karyawan
        $this->assertEquals('karyawan', $employee->user->role);

        // Update role to admin
        $this->actingAs($admin)
            ->put(route('admin.karyawan.update', $employee), [
                'name' => 'Role User Updated',
                'username' => 'role.user',
                'password' => '',
                'password_confirmation' => '',
                'employee_number' => 'EMP-ROLE-001',
                'division_id' => $reference['division_id'],
                'position_id' => $reference['position_id'],
                'is_active' => true,
                'role' => 'admin',
            ])
            ->assertRedirect(route('admin.karyawan.index'));

        $this->assertDatabaseHas('users', [
            'id' => $employee->user_id,
            'role' => 'admin',
            'name' => 'Role User Updated',
        ]);
    }

    public function test_employee_update_rejects_invalid_role(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $employee = $this->makeEmployee($reference, [
            'employee_number' => 'EMP-ROLE-002',
            'name' => 'Role Update User',
            'username' => 'role.update',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.karyawan.edit', $employee))
            ->put(route('admin.karyawan.update', $employee), [
                'name' => 'Role Update User',
                'username' => 'role.update',
                'password' => '',
                'password_confirmation' => '',
                'employee_number' => 'EMP-ROLE-002',
                'division_id' => $reference['division_id'],
                'position_id' => $reference['position_id'],
                'is_active' => true,
                'role' => 'invalid-role',
            ])
            ->assertRedirect(route('admin.karyawan.edit', $employee))
            ->assertSessionHasErrors('role');
    }

    public function test_admin_index_shows_role_badge(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $this->makeEmployee($reference, [
            'employee_number' => 'EMP-BADGE-001',
            'name' => 'Badge User',
            'username' => 'badge.user',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.karyawan.index'))
            ->assertOk()
            ->assertSeeText('Karyawan')
            ->assertSeeText('Admin');
    }

    public function test_admin_can_delete_unused_employee_permanently(): void
    {
        $admin = $this->makeAdmin();
        $reference = $this->seedReferenceData();
        $employee = $this->makeEmployee($reference, [
            'employee_number' => 'EMP-060',
            'name' => 'Delete User',
            'username' => 'delete.user',
        ]);

        $userId = $employee->user_id;

        $this->actingAs($admin)
            ->delete(route('admin.karyawan.destroy', $employee))
            ->assertRedirect(route('admin.karyawan.index'));

        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-karyawan',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    /**
     * @return array{division_id:int,position_id:int}
     */
    private function seedReferenceData(): array
    {
        $now = Carbon::now();

        $divisionId = DB::table('divisions')->insertGetId([
            'name' => 'Operasional',
            'description' => 'Divisi operasional',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $positionId = DB::table('positions')->insertGetId([
            'name' => 'Staff',
            'description' => 'Staff umum',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'division_id' => $divisionId,
            'position_id' => $positionId,
        ];
    }

    /**
     * @param array{division_id:int,position_id:int} $reference
     */
    private function makeEmployee(array $reference, array $overrides = []): Employee
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Karyawan Test',
            'username' => 'karyawan-test-'.uniqid('', true),
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ], Arr::only($overrides, ['name', 'username', 'is_active'])));

        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_number' => $overrides['employee_number'] ?? 'EMP-'.random_int(100, 999),
            'division_id' => $reference['division_id'],
            'position_id' => $reference['position_id'],
            'is_active' => $overrides['is_active'] ?? true,
        ]);

        return $employee->load(['user', 'division', 'position']);
    }
}
