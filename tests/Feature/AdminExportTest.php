<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_pdf(): void
    {
        [$admin] = $this->seedExportData();

        $response = $this->actingAs($admin)
            ->get(route('admin.laporan.export-pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_export_excel(): void
    {
        [$admin] = $this->seedExportData();

        $response = $this->actingAs($admin)
            ->get(route('admin.laporan.export-excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_pdf_follows_filter(): void
    {
        [$admin] = $this->seedExportData();

        // Filter by training_id=1 which has data
        $response = $this->actingAs($admin)
            ->get(route('admin.laporan.export-pdf', ['training_id' => 1]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_excel_follows_filter(): void
    {
        [$admin] = $this->seedExportData();

        $response = $this->actingAs($admin)
            ->get(route('admin.laporan.export-excel', ['training_id' => 1]));

        $response->assertStatus(200);
    }

    public function test_employee_cannot_access_export_pdf(): void
    {
        [, $employee] = $this->seedExportData();

        $this->actingAs($employee)
            ->get(route('admin.laporan.export-pdf'))
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_employee_cannot_access_export_excel(): void
    {
        [, $employee] = $this->seedExportData();

        $this->actingAs($employee)
            ->get(route('admin.laporan.export-excel'))
            ->assertRedirect(route('employee.dashboard'));
    }

    public function test_guest_cannot_access_export_routes(): void
    {
        $this->get(route('admin.laporan.export-pdf'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.laporan.export-excel'))
            ->assertRedirect(route('login'));
    }

    public function test_export_handles_empty_data_gracefully(): void
    {
        $now = Carbon::now();

        $admin = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-export-empty',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        // No report data seeded — export should still work (will produce empty PDF)
        $response = $this->actingAs($admin)
            ->get(route('admin.laporan.export-pdf'));

        $response->assertStatus(200);
    }

    /**
     * @return array{0:User,1:User}
     */
    private function seedExportData(): array
    {
        $now = Carbon::now();

        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'HRD', 'description' => 'Human Resource Development', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('positions')->insert([
            ['id' => 1, 'name' => 'Staff', 'description' => 'Staff umum', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Training',
            'username' => 'admin-export-test',
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi-export-test',
            'role' => 'karyawan',
            'is_active' => true,
            'password' => 'password',
        ]);

        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => $employeeUser->id, 'employee_number' => 'EMP-EXP-001', 'division_id' => 1, 'position_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('trainings')->insert([
            ['id' => 1, 'title' => 'Training Keselamatan Kerja', 'description' => 'Training dengan pre-test dan post-test', 'start_date' => $now->copy()->subDays(10)->toDateString(), 'end_date' => $now->copy()->addDays(5)->toDateString(), 'status' => 'published', 'has_pre_test' => true, 'has_post_test' => true, 'passing_grade' => 75, 'allow_post_test_retake' => false, 'max_post_test_attempt' => null, 'show_score_to_employee' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('questions')->insert([
            ['id' => 1, 'training_id' => 1, 'test_type' => 'pre_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Apa itu K3?', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'training_id' => 1, 'test_type' => 'post_test', 'question_type' => 'multiple_choice', 'order_number' => 1, 'question_text' => 'Apa manfaat APD?', 'weight' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('question_options')->insert([
            ['id' => 1, 'question_id' => 1, 'option_label' => 'A', 'option_text' => 'Keselamatan Kerja', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'question_id' => 1, 'option_label' => 'B', 'option_text' => 'Bukan K3', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'question_id' => 2, 'option_label' => 'A', 'option_text' => 'Melindungi pekerja', 'is_correct' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'question_id' => 2, 'option_label' => 'B', 'option_text' => 'Tidak berguna', 'is_correct' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('test_attempts')->insert([
            [
                'id' => 1,
                'employee_id' => 1,
                'training_id' => 1,
                'test_type' => 'pre_test',
                'attempt_number' => 1,
                'status' => 'completed',
                'started_at' => $now->copy()->subDays(7),
                'submitted_at' => $now->copy()->subDays(7),
                'mcq_score' => 100,
                'essay_score' => 0,
                'final_score' => 100,
                'grading_status' => 'auto_graded',
                'pass_status' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'employee_id' => 1,
                'training_id' => 1,
                'test_type' => 'post_test',
                'attempt_number' => 1,
                'status' => 'completed',
                'started_at' => $now->copy()->subDays(4),
                'submitted_at' => $now->copy()->subDays(4),
                'mcq_score' => 85,
                'essay_score' => 0,
                'final_score' => 85,
                'grading_status' => 'auto_graded',
                'pass_status' => 'passed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('training_assignments')->insert([
            ['id' => 1, 'training_id' => 1, 'target_type' => 'employee', 'target_id' => 1, 'assigned_at' => $now->copy()->subDays(8)->toDateString(), 'deadline' => null, 'is_active' => true, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('employee_training_progress')->insert([
            [
                'id' => 1,
                'employee_id' => 1,
                'training_id' => 1,
                'assignment_id' => 1,
                'status' => 'passed',
                'pre_test_completed_at' => $now->copy()->subDays(7),
                'material_completed_at' => $now->copy()->subDays(5),
                'post_test_completed_at' => $now->copy()->subDays(4),
                'final_score' => 85,
                'final_status' => 'passed',
                'completed_at' => $now->copy()->subDays(4),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        return [$admin, $employeeUser];
    }
}
