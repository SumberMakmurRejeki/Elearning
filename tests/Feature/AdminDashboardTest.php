<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Services\AdminDashboardService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_dashboard_and_see_summary_cards(): void
    {
        $admin = $this->seedDashboardData();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSeeText('Dashboard Admin')
            ->assertSeeText('Total Karyawan')
            ->assertSeeText('Total Training')
            ->assertSeeText('Training Aktif')
            ->assertSeeText('Training Selesai')
            ->assertSeeText('Rata-rata Nilai Post-Test')
            ->assertSeeText('Jumlah Karyawan Lulus')
            ->assertSeeText('Jumlah Karyawan Tidak Lulus');
    }

    public function test_preview_route_uses_same_dashboard_view(): void
    {
        $admin = $this->seedDashboardData();

        $this->actingAs($admin)
            ->get('/ui-preview/admin')
            ->assertOk()
            ->assertSeeText('Dashboard Admin');
    }

    public function test_dashboard_filter_changes_selected_training_slice(): void
    {
        $admin = $this->seedDashboardData();
        $trainingId = (int) DB::table('trainings')->where('title', 'Training Keselamatan Kerja')->value('id');

        $this->actingAs($admin)
            ->get('/admin/dashboard?training_id='.$trainingId)
            ->assertOk()
            ->assertSeeText('Training Keselamatan Kerja')
            ->assertDontSeeText('Training Kepemimpinan');
    }

    public function test_dashboard_empty_state_is_shown_when_no_training_matches_filter(): void
    {
        $admin = $this->seedDashboardData();

        $this->actingAs($admin)
            ->get('/admin/dashboard?month=1&year=2000')
            ->assertOk()
            ->assertSeeText('Data dashboard belum tersedia')
            ->assertSeeText('Tidak ada data sesuai filter yang dipilih');
    }

    public function test_dashboard_error_state_is_shown_when_data_load_fails(): void
    {
        $admin = $this->seedDashboardData();

        $this->app->bind(AdminDashboardService::class, static fn (): AdminDashboardService => new class extends AdminDashboardService {
            public function build(int $month, int $year, ?int $trainingId): array
            {
                throw new RuntimeException('dashboard failed');
            }
        });

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSeeText('Gagal memuat dashboard')
            ->assertSeeText('Data dashboard gagal dimuat');
    }

    private function seedDashboardData(): User
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Operasional', 'description' => 'Divisi operasional perusahaan', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-dashboard',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeOne = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-dashboard',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeTwo = User::factory()->create([
            'name' => 'Sari Wulandari',
            'username' => 'sari-dashboard',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['user_id' => $employeeOne->id, 'employee_number' => 'EMP-100', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $employeeTwo->id, 'employee_number' => 'EMP-101', 'division_id' => 2, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            [
                'title' => 'Training Keselamatan Kerja',
                'description' => 'Training inti bulan ini',
                'start_date' => $now->startOfMonth()->toDateString(),
                'end_date' => $now->copy()->addDays(10)->toDateString(),
                'status' => 'published',
                'has_pre_test' => true,
                'has_post_test' => true,
                'passing_grade' => 75,
                'allow_post_test_retake' => true,
                'max_post_test_attempt' => 3,
                'show_score_to_employee' => true,
                'created_by' => $admin->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Training Kepemimpinan',
                'description' => 'Training selesai bulan ini',
                'start_date' => $now->copy()->subDays(18)->toDateString(),
                'end_date' => $now->copy()->subDays(2)->toDateString(),
                'status' => 'archived',
                'has_pre_test' => true,
                'has_post_test' => true,
                'passing_grade' => 80,
                'allow_post_test_retake' => false,
                'max_post_test_attempt' => 2,
                'show_score_to_employee' => false,
                'created_by' => $admin->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $trainingIdOne = (int) DB::table('trainings')->where('title', 'Training Keselamatan Kerja')->value('id');
        $trainingIdTwo = (int) DB::table('trainings')->where('title', 'Training Kepemimpinan')->value('id');

        $employeeOneId = (int) DB::table('employees')->where('employee_number', 'EMP-100')->value('id');
        $employeeTwoId = (int) DB::table('employees')->where('employee_number', 'EMP-101')->value('id');

        DB::table('employee_training_progress')->insert([
            [
                'employee_id' => $employeeOneId,
                'training_id' => $trainingIdOne,
                'assignment_id' => null,
                'status' => 'in_progress',
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
                'employee_id' => $employeeTwoId,
                'training_id' => $trainingIdOne,
                'assignment_id' => null,
                'status' => 'completed',
                'pre_test_completed_at' => $now,
                'material_completed_at' => $now,
                'post_test_completed_at' => $now,
                'final_score' => 85,
                'final_status' => 'passed',
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeOneId,
                'training_id' => $trainingIdTwo,
                'assignment_id' => null,
                'status' => 'completed',
                'pre_test_completed_at' => $now,
                'material_completed_at' => $now,
                'post_test_completed_at' => $now,
                'final_score' => 60,
                'final_status' => 'failed',
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('test_attempts')->insert([
            [
                'employee_id' => $employeeOneId,
                'training_id' => $trainingIdOne,
                'test_type' => 'pre_test',
                'attempt_number' => 1,
                'status' => 'submitted',
                'started_at' => $now,
                'submitted_at' => $now,
                'mcq_score' => 70,
                'essay_score' => 0,
                'final_score' => 70,
                'grading_status' => 'auto_graded',
                'pass_status' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeTwoId,
                'training_id' => $trainingIdOne,
                'test_type' => 'pre_test',
                'attempt_number' => 1,
                'status' => 'submitted',
                'started_at' => $now,
                'submitted_at' => $now,
                'mcq_score' => 90,
                'essay_score' => 0,
                'final_score' => 90,
                'grading_status' => 'auto_graded',
                'pass_status' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeOneId,
                'training_id' => $trainingIdOne,
                'test_type' => 'post_test',
                'attempt_number' => 1,
                'status' => 'submitted',
                'started_at' => $now,
                'submitted_at' => $now,
                'mcq_score' => 80,
                'essay_score' => 0,
                'final_score' => 80,
                'grading_status' => 'auto_graded',
                'pass_status' => 'passed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeTwoId,
                'training_id' => $trainingIdOne,
                'test_type' => 'post_test',
                'attempt_number' => 1,
                'status' => 'submitted',
                'started_at' => $now,
                'submitted_at' => $now,
                'mcq_score' => 70,
                'essay_score' => 0,
                'final_score' => 70,
                'grading_status' => 'auto_graded',
                'pass_status' => 'failed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeOneId,
                'training_id' => $trainingIdTwo,
                'test_type' => 'pre_test',
                'attempt_number' => 1,
                'status' => 'submitted',
                'started_at' => $now,
                'submitted_at' => $now,
                'mcq_score' => 60,
                'essay_score' => 0,
                'final_score' => 60,
                'grading_status' => 'auto_graded',
                'pass_status' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeOneId,
                'training_id' => $trainingIdTwo,
                'test_type' => 'post_test',
                'attempt_number' => 1,
                'status' => 'submitted',
                'started_at' => $now,
                'submitted_at' => $now,
                'mcq_score' => 50,
                'essay_score' => 0,
                'final_score' => 50,
                'grading_status' => 'auto_graded',
                'pass_status' => 'failed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        return $admin;
    }
}
