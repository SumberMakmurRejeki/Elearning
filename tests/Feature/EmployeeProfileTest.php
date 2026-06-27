<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_own_profile(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee)
            ->get(route('employee.profile.show'))
            ->assertOk()
            ->assertSeeText('Profil Karyawan')
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('EMP-EPF-001')
            ->assertSeeText('HRD')
            ->assertSeeText('Staff');
    }

    public function test_employee_can_update_own_name_and_username(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee)
            ->patch(route('employee.profile.update'), [
                'name' => 'Budi Updated',
                'username' => 'budi-updated',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Budi Updated',
            'username' => 'budi-updated',
        ]);
    }

    public function test_employee_can_change_password(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee)
            ->patch(route('employee.profile.update-password'), [
                'current_password' => 'password123',
                'new_password' => 'newpassword',
                'new_password_confirmation' => 'newpassword',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Password berhasil diubah.');

        $employee->refresh();
        $this->assertTrue(Hash::check('newpassword', $employee->password));
    }

    public function test_employee_cannot_change_password_with_wrong_old_password(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee)
            ->patch(route('employee.profile.update-password'), [
                'current_password' => 'wrongpassword',
                'new_password' => 'newpassword',
                'new_password_confirmation' => 'newpassword',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_admin_cannot_access_employee_profile(): void
    {
        $admin = User::factory()->create(['name' => 'Admin', 'username' => 'admin-epf', 'role' => 'admin', 'is_active' => true, 'password' => 'password123']);

        $this->actingAs($admin)
            ->get(route('employee.profile.show'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_guest_cannot_access_employee_profile(): void
    {
        $this->get(route('employee.profile.show'))
            ->assertRedirect(route('login'));
    }

    public function test_employee_cannot_change_another_employees_profile(): void
    {
        $employee1 = $this->createEmployee();
        $employee2 = $this->createEmployee('EMP-EPF-002', 'other-employee');

        $this->actingAs($employee1)
            ->patch(route('employee.profile.update'), [
                'name' => 'Hacker',
                'username' => 'hacker',
            ]);

        // employee2 should be unchanged
        $employee2->refresh();
        $this->assertNotEquals('Hacker', $employee2->name);
    }

    private function createEmployee(string $nip = 'EMP-EPF-001', string $username = 'budi-employee-profile'): User
    {
        $now = Carbon::now();

        if (! DB::table('divisions')->where('id', 1)->exists()) {
            DB::table('divisions')->insert([
                ['id' => 1, 'name' => 'HRD', 'description' => 'HR', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
        if (! DB::table('positions')->where('id', 1)->exists()) {
            DB::table('positions')->insert([
                ['id' => 1, 'name' => 'Staff', 'description' => 'Staff', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => $username,
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password123',
        ]);

        DB::table('employees')->insert([
            ['user_id' => $user->id, 'employee_number' => $nip, 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return $user;
    }
}
