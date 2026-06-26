<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_position_index_and_empty_state(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.jabatan.index'))
            ->assertOk()
            ->assertSeeText('Master Data Jabatan')
            ->assertSeeText('Belum ada data jabatan.')
            ->assertSeeText('Tambah Jabatan');
    }

    public function test_admin_can_create_position(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.jabatan.store'), [
                'name' => 'Supervisor',
                'description' => 'Supervisor operasional',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.jabatan.index'));

        $this->assertDatabaseHas('positions', [
            'name' => 'Supervisor',
            'description' => 'Supervisor operasional',
            'is_active' => true,
        ]);
    }

    public function test_position_create_validation_errors_are_returned(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('admin.jabatan.create'))
            ->post(route('admin.jabatan.store'), [
                'name' => '',
                'description' => 123,
                'is_active' => '',
            ])
            ->assertRedirect(route('admin.jabatan.create'))
            ->assertSessionHasErrors([
                'name' => 'Nama jabatan wajib diisi.',
                'description' => 'Deskripsi jabatan harus berupa teks.',
                'is_active' => 'Status jabatan wajib dipilih.',
            ]);
    }

    public function test_admin_can_edit_position_and_keep_name_when_unique_rule_ignores_self(): void
    {
        $admin = $this->makeAdmin();
        $position = $this->seedPositions()[0];

        $this->actingAs($admin)
            ->put(route('admin.jabatan.update', $position['id']), [
                'name' => 'Supervisor Baru',
                'description' => 'Perubahan deskripsi',
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.jabatan.index'));

        $this->assertDatabaseHas('positions', [
            'id' => $position['id'],
            'name' => 'Supervisor Baru',
            'description' => 'Perubahan deskripsi',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_view_position_show_page(): void
    {
        $admin = $this->makeAdmin();
        $position = $this->seedPositions()[0];

        $this->actingAs($admin)
            ->get(route('admin.jabatan.show', $position['id']))
            ->assertOk()
            ->assertSeeText('Detail Jabatan')
            ->assertSeeText($position['name'])
            ->assertSeeText('Karyawan Terkait');
    }

    public function test_admin_can_filter_positions_by_query_and_status(): void
    {
        $admin = $this->makeAdmin();
        $this->seedPositions();

        $this->actingAs($admin)
            ->get(route('admin.jabatan.index', ['q' => 'Supervisor', 'status' => 'inactive']))
            ->assertOk()
            ->assertSeeText('Supervisor')
            ->assertDontSeeText('Staff');
    }

    public function test_admin_can_toggle_position_status(): void
    {
        $admin = $this->makeAdmin();
        $position = $this->seedPositions()[0];

        $this->actingAs($admin)
            ->patch(route('admin.jabatan.status', $position['id']), ['is_active' => 0])
            ->assertRedirect();

        $this->assertDatabaseHas('positions', [
            'id' => $position['id'],
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_delete_position_that_is_used_by_employee(): void
    {
        $admin = $this->makeAdmin();
        $position = $this->seedPositions()[0];

        $this->actingAs($admin)
            ->delete(route('admin.jabatan.destroy', $position['id']))
            ->assertRedirect();

        $this->assertDatabaseHas('positions', [
            'id' => $position['id'],
        ]);
    }

    public function test_admin_can_delete_unused_position_permanently(): void
    {
        $admin = $this->makeAdmin();
        $position = $this->seedPositions()[1];

        $this->actingAs($admin)
            ->delete(route('admin.jabatan.destroy', $position['id']))
            ->assertRedirect(route('admin.jabatan.index'));

        $this->assertDatabaseMissing('positions', [
            'id' => $position['id'],
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-jabatan',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    /**
     * @return array<int, array{id:int,name:string,description:?string,is_active:bool}>
     */
    private function seedPositions(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'Operasional', 'description' => 'Divisi operasional', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-jabatan',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Supervisor', 'description' => 'Supervisor operasional', 'is_active' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employees')->insert([
            ['user_id' => $employeeUser->id, 'employee_number' => 'EMP-901', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true],
            ['id' => 2, 'name' => 'Supervisor', 'description' => 'Supervisor operasional', 'is_active' => false],
        ];
    }
}
