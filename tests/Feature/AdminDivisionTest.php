<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDivisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_division_index_and_empty_state(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.divisi.index'))
            ->assertOk()
            ->assertSeeText('Master Data Divisi')
            ->assertSeeText('Belum ada data divisi.')
            ->assertSeeText('Tambah Divisi');
    }

    public function test_admin_can_create_division(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.divisi.store'), [
                'name' => 'HRD',
                'description' => 'Human Resource Development',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.divisi.index'));

        $this->assertDatabaseHas('divisions', [
            'name' => 'HRD',
            'description' => 'Human Resource Development',
            'is_active' => true,
        ]);
    }

    public function test_division_create_validation_errors_are_returned(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('admin.divisi.create'))
            ->post(route('admin.divisi.store'), [
                'name' => '',
                'description' => 123,
                'is_active' => '',
            ])
            ->assertRedirect(route('admin.divisi.create'))
            ->assertSessionHasErrors([
                'name' => 'Nama divisi wajib diisi.',
                'description' => 'Deskripsi divisi harus berupa teks.',
                'is_active' => 'Status divisi wajib dipilih.',
            ]);
    }

    public function test_admin_can_edit_division_and_keep_name_when_unique_rule_ignores_self(): void
    {
        $admin = $this->makeAdmin();
        $division = $this->seedDivisions()[0];

        $this->actingAs($admin)
            ->put(route('admin.divisi.update', $division['id']), [
                'name' => 'HRD Baru',
                'description' => 'Perubahan deskripsi',
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.divisi.index'));

        $this->assertDatabaseHas('divisions', [
            'id' => $division['id'],
            'name' => 'HRD Baru',
            'description' => 'Perubahan deskripsi',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_view_division_show_page(): void
    {
        $admin = $this->makeAdmin();
        $division = $this->seedDivisions()[0];

        $this->actingAs($admin)
            ->get(route('admin.divisi.show', $division['id']))
            ->assertOk()
            ->assertSeeText('Detail Divisi')
            ->assertSeeText($division['name'])
            ->assertSeeText('Karyawan Terkait');
    }

    public function test_admin_can_filter_divisions_by_query_and_status(): void
    {
        $admin = $this->makeAdmin();
        $this->seedDivisions();

        $this->actingAs($admin)
            ->get(route('admin.divisi.index', ['q' => 'Operasional', 'status' => 'inactive']))
            ->assertOk()
            ->assertSeeText('Operasional')
            ->assertDontSeeText('HRD');
    }

    public function test_admin_can_toggle_division_status(): void
    {
        $admin = $this->makeAdmin();
        $division = $this->seedDivisions()[0];

        $this->actingAs($admin)
            ->patch(route('admin.divisi.status', $division['id']), ['is_active' => 0])
            ->assertRedirect();

        $this->assertDatabaseHas('divisions', [
            'id' => $division['id'],
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_delete_division_that_is_used_by_employee(): void
    {
        $admin = $this->makeAdmin();
        $division = $this->seedDivisions()[0];

        $this->actingAs($admin)
            ->delete(route('admin.divisi.destroy', $division['id']))
            ->assertRedirect();

        $this->assertDatabaseHas('divisions', [
            'id' => $division['id'],
        ]);
    }

    public function test_admin_can_delete_unused_division_permanently(): void
    {
        $admin = $this->makeAdmin();
        $division = $this->seedDivisions()[1];

        $this->actingAs($admin)
            ->delete(route('admin.divisi.destroy', $division['id']))
            ->assertRedirect(route('admin.divisi.index'));

        $this->assertDatabaseMissing('divisions', [
            'id' => $division['id'],
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-divisi',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    /**
     * @return array<int, array{id:int,name:string,description:?string,is_active:bool}>
     */
    private function seedDivisions(): array
    {
        $now = Carbon::now();

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Seed Admin',
            'username' => 'seed-admin-divisi',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Operasional', 'description' => 'Divisi operasional perusahaan', 'is_active' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employees')->insert([
            ['user_id' => $adminUser->id, 'employee_number' => 'EMP-900', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return [
            ['id' => 1, 'name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true],
            ['id' => 2, 'name' => 'Operasional', 'description' => 'Divisi operasional perusahaan', 'is_active' => false],
        ];
    }
}
