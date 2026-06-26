<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\EmployeeDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class EmployeeDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_open_dashboard_and_see_own_summary_and_recent_trainings(): void
    {
        [$employeeUser] = $this->seedDashboardData();

        $this->actingAs($employeeUser)
            ->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard Karyawan')
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('Total Training')
            ->assertSeeText('Belum Mulai')
            ->assertSeeText('Sedang Berjalan')
            ->assertSeeText('Selesai')
            ->assertSeeText('Lulus')
            ->assertSeeText('Tidak Lulus')
            ->assertSeeText('Training Terbaru')
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertSeeText('Training Kepemimpinan')
            ->assertDontSeeText('Training Audit Internal');
    }

    public function test_employee_dashboard_shows_empty_state_when_employee_has_no_training(): void
    {
        $employeeUser = User::factory()->create([
            'name' => 'Karyawan Kosong',
            'username' => 'karyawan-kosong',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

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
        DB::table('employees')->insert([
            'user_id' => $employeeUser->id,
            'employee_number' => 'EMP-900',
            'division_id' => 1,
            'position_id' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->actingAs($employeeUser)
            ->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSeeText('Belum ada training yang diberikan.')
            ->assertSeeText('Training Anda akan tampil di sini setelah admin membuat penugasan.');
    }

    public function test_employee_dashboard_only_shows_logged_in_employee_data(): void
    {
        [$employeeUser] = $this->seedDashboardData();

        $this->actingAs($employeeUser)
            ->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSeeText('Budi Santoso')
            ->assertDontSeeText('Sari Wulandari')
            ->assertDontSeeText('Training Audit Internal');
    }

    public function test_employee_dashboard_shows_safe_error_state_when_data_load_fails(): void
    {
        [$employeeUser] = $this->seedDashboardData();

        $this->app->bind(EmployeeDashboardService::class, static fn (): EmployeeDashboardService => new class extends EmployeeDashboardService {
            public function buildForUser(User $user): array
            {
                throw new RuntimeException('employee dashboard failed');
            }
        });

        $this->actingAs($employeeUser)
            ->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSeeText('Gagal memuat dashboard')
            ->assertSeeText('Data dashboard gagal dimuat.');
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedDashboardData(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Operasional', 'description' => 'Divisi operasional perusahaan', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $employeeOne = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-employee-dashboard',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeTwo = User::factory()->create([
            'name' => 'Sari Wulandari',
            'username' => 'sari-employee-dashboard',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeOne->id, 'employee_number' => 'EMP-100', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'user_id' => $employeeTwo->id, 'employee_number' => 'EMP-101', 'division_id' => 2, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'id' => 1,
                'title' => 'Training Keselamatan Kerja',
                'description' => 'Training inti bulan ini',
                'start_date' => $now->copy()->subDays(5)->toDateString(),
                'end_date' => $now->copy()->addDays(10)->toDateString(),
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
                'id' => 2,
                'title' => 'Training Kepemimpinan',
                'description' => 'Training kepemimpinan tim',
                'start_date' => $now->copy()->subDays(20)->toDateString(),
                'end_date' => $now->copy()->subDays(1)->toDateString(),
                'status' => 'archived',
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
                'title' => 'Training Audit Internal',
                'description' => 'Training hanya untuk employee lain',
                'start_date' => $now->copy()->subDays(2)->toDateString(),
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
            ['id' => 2, 'training_id' => 2, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(15)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'training_id' => 3, 'target_type' => 'employee', 'target_id' => 2, 'assigned_at' => $now->copy()->subDays(1)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            'id' => 999,
            'title' => 'Training Dasar Komunikasi',
            'description' => 'Training belum mulai.',
            'start_date' => $now->copy()->addDays(2)->toDateString(),
            'end_date' => $now->copy()->addDays(30)->toDateString(),
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
        ]);

        DB::table('employee_training_progress')->insert([
            ['employee_id' => 1, 'training_id' => 1, 'assignment_id' => 1, 'status' => 'in_material', 'pre_test_completed_at' => null, 'material_completed_at' => null, 'post_test_completed_at' => null, 'final_score' => null, 'final_status' => null, 'completed_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 1, 'training_id' => 2, 'assignment_id' => 2, 'status' => 'failed', 'pre_test_completed_at' => $now, 'material_completed_at' => $now, 'post_test_completed_at' => $now, 'final_score' => 60, 'final_status' => 'failed', 'completed_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 1, 'training_id' => 999, 'assignment_id' => null, 'status' => 'not_started', 'pre_test_completed_at' => null, 'material_completed_at' => null, 'post_test_completed_at' => null, 'final_score' => null, 'final_status' => null, 'completed_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            'employee_id' => 2,
            'training_id' => 3,
            'assignment_id' => 3,
            'status' => 'passed',
            'pre_test_completed_at' => $now,
            'material_completed_at' => $now,
            'post_test_completed_at' => $now,
            'final_score' => 90,
            'final_status' => 'passed',
            'completed_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$employeeOne, $employeeTwo];
    }
}
