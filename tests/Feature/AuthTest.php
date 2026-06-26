<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_preview_admin_to_login(): void
    {
        $this->get('/ui-preview/admin')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_admin_jabatan_to_login(): void
    {
        $this->get('/admin/jabatan')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_admin_karyawan_to_login(): void
    {
        $this->get('/admin/karyawan')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_employee_dashboard_to_login(): void
    {
        $this->get('/karyawan/dashboard')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_preview_employee_to_login(): void
    {
        $this->get('/ui-preview/employee')->assertRedirect(route('login'));
    }

    public function test_active_admin_can_login_and_is_redirected_to_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-test',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->post('/login', [
            'username' => 'admin-test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_active_employee_can_login_and_is_redirected_to_employee_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-test',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->post('/login', [
            'username' => 'budi-test',
            'password' => 'password',
        ])->assertRedirect(route('employee.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_wrong_password_shows_generic_failure(): void
    {
        User::factory()->create([
            'username' => 'wrong-pass-user',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->from('/login')
            ->post('/login', [
                'username' => 'wrong-pass-user',
                'password' => 'bad-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['auth' => 'Username atau password salah.']);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'inactive-user',
            'role' => 'karyawan',
            'is_active' => false,
            'password' => 'password',
        ]);

        $this->from('/login')
            ->post('/login', [
                'username' => 'inactive-user',
                'password' => 'password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['auth' => 'Akun Anda sedang nonaktif. Hubungi admin.']);

        $this->assertGuest();
    }

    public function test_employee_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'employee-role',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_admin_cannot_access_employee_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'admin-role',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->get('/karyawan/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'username' => 'logout-user',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
