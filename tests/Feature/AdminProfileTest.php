<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_own_profile(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.profile.show'))
            ->assertOk()
            ->assertSeeText('Profil Admin')
            ->assertSeeText('Admin Testing');
    }

    public function test_admin_can_update_own_name_and_username(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.profile.update'), [
                'name' => 'Admin Updated',
                'username' => 'admin-updated',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin Updated',
            'username' => 'admin-updated',
        ]);
    }

    public function test_admin_cannot_set_duplicate_username(): void
    {
        $admin = $this->createAdmin();
        $otherAdmin = User::factory()->create(['username' => 'other-admin', 'role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.profile.update'), [
                'name' => 'Admin Duplicate',
                'username' => 'other-admin',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_admin_can_change_password_with_correct_old_password(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.profile.update-password'), [
                'current_password' => 'password123',
                'new_password' => 'newpassword',
                'new_password_confirmation' => 'newpassword',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Password berhasil diubah.');

        $admin->refresh();
        $this->assertTrue(Hash::check('newpassword', $admin->password));
    }

    public function test_admin_cannot_change_password_with_wrong_old_password(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.profile.update-password'), [
                'current_password' => 'wrongpassword',
                'new_password' => 'newpassword',
                'new_password_confirmation' => 'newpassword',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_admin_cannot_change_password_below_8_characters(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.profile.update-password'), [
                'current_password' => 'password123',
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('new_password');
    }

    public function test_admin_cannot_change_password_without_confirmation(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.profile.update-password'), [
                'current_password' => 'password123',
                'new_password' => 'newpassword',
                'new_password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors('new_password');
    }

    public function test_employee_cannot_access_admin_profile(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee)
            ->get(route('admin.profile.show'))
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_guest_cannot_access_admin_profile(): void
    {
        $this->get(route('admin.profile.show'))
            ->assertRedirect(route('login'));
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Testing',
            'username' => 'admin-profile-test',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password123',
        ]);
    }

    private function createEmployee(): User
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'HR', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $user = User::factory()->create([
            'name' => 'Employee Test',
            'username' => 'employee-profile-test',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password123',
        ]);

        DB::table('employees')->insert([
            ['user_id' => $user->id, 'employee_number' => 'EMP-PRF-001', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        return $user;
    }
}
