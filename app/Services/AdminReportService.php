<?php

namespace App\Services;

use App\Models\Division;
use App\Models\EmployeeTrainingProgress;
use App\Models\Position;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\Training;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    // ============================================================
    // TEST RESULTS — Index
    // ============================================================

    /**
     * @param array{q?:string,test_type?:string,training_id?:string,division_id?:string,position_id?:string,grading_status?:string,pass_status?:string,date_from?:string,date_to?:string} $filters
     */
    public function testResultIndex(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = trim((string) ($filters['q'] ?? ''));
        $testType = (string) ($filters['test_type'] ?? '');
        $trainingId = (string) ($filters['training_id'] ?? '');
        $divisionId = (string) ($filters['division_id'] ?? '');
        $positionId = (string) ($filters['position_id'] ?? '');
        $gradingStatus = (string) ($filters['grading_status'] ?? '');
        $passStatus = (string) ($filters['pass_status'] ?? '');
        $dateFrom = (string) ($filters['date_from'] ?? '');
        $dateTo = (string) ($filters['date_to'] ?? '');

        return TestAttempt::query()
            ->select('test_attempts.*')
            ->join('employees', 'employees.id', '=', 'test_attempts.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->with(['training', 'employee.user', 'employee.division', 'employee.position'])
            ->when($query !== '', static function ($builder) use ($query): void {
                $builder->where(static function ($search) use ($query): void {
                    $search->where('users.name', 'like', '%'.$query.'%')
                        ->orWhere('employees.employee_number', 'like', '%'.$query.'%')
                        ->orWhereIn('test_attempts.training_id', Training::query()->select('id')->where('title', 'like', '%'.$query.'%'));
                });
            })
            ->when($testType !== '', static fn ($builder) => $builder->where('test_attempts.test_type', $testType))
            ->when($trainingId !== '', static fn ($builder) => $builder->where('test_attempts.training_id', (int) $trainingId))
            ->when($divisionId !== '', static fn ($builder) => $builder->where('employees.division_id', (int) $divisionId))
            ->when($positionId !== '', static fn ($builder) => $builder->where('employees.position_id', (int) $positionId))
            ->when($gradingStatus !== '', static fn ($builder) => $builder->where('test_attempts.grading_status', $gradingStatus))
            ->when($passStatus !== '', static fn ($builder) => $builder->where('test_attempts.pass_status', $passStatus))
            ->when($dateFrom !== '', static fn ($builder) => $builder->whereDate('test_attempts.submitted_at', '>=', $dateFrom))
            ->when($dateTo !== '', static fn ($builder) => $builder->whereDate('test_attempts.submitted_at', '<=', $dateTo))
            ->orderByDesc('test_attempts.submitted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{trainingOptions:array<int|string,string>,divisionOptions:array<int|string,string>,positionOptions:array<int|string,string>,testTypeOptions:array<string,string>,gradingStatusOptions:array<string,string>,passStatusOptions:array<string,string>}
     */
    public function testResultFilterOptions(): array
    {
        return [
            'trainingOptions' => Training::query()->orderBy('title')->pluck('title', 'id')->all(),
            'divisionOptions' => Division::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
            'positionOptions' => Position::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
            'testTypeOptions' => ['pre_test' => 'Pre-Test', 'post_test' => 'Post-Test'],
            'gradingStatusOptions' => [
                'auto_graded' => 'Auto-Graded',
                'waiting_manual_review' => 'Menunggu Penilaian',
                'manual_reviewed' => 'Selesai Dinilai',
            ],
            'passStatusOptions' => [
                'passed' => 'Lulus',
                'failed' => 'Tidak Lulus',
            ],
        ];
    }

    // ============================================================
    // TEST RESULTS — Detail
    // ============================================================

    /**
     * @return array{attempt:TestAttempt,answers:Collection<int,TestAnswer>,attemptLabel:string,gradingStatusLabel:string,passStatusLabel:string|null}
     */
    public function testResultDetail(TestAttempt $attempt): array
    {
        $attempt->load(['training', 'employee.user', 'employee.division', 'employee.position']);

        $answers = TestAnswer::query()
            ->with(['question.options', 'selectedOption'])
            ->where('attempt_id', $attempt->id)
            ->get();

        // Sort answers by question order_number in PHP to avoid SQLite join issues
        $answers = $answers->sortBy(static function (TestAnswer $answer): int {
            return $answer->question?->order_number ?? PHP_INT_MAX;
        })->values();

        return [
            'attempt' => $attempt,
            'answers' => $answers,
            'attemptLabel' => 'Attempt #'.$attempt->attempt_number,
            'gradingStatusLabel' => $this->gradingStatusLabel($attempt->grading_status),
            'passStatusLabel' => $this->passStatusLabel($attempt->pass_status),
        ];
    }

    // ============================================================
    // TRAINING REPORTS — Index
    // ============================================================

    /**
     * @param array{month?:string,year?:string,training_id?:string,division_id?:string,position_id?:string,progress_status?:string,final_status?:string} $filters
     */
    public function trainingReportIndex(array $filters = []): array
    {
        $month = (string) ($filters['month'] ?? '');
        $year = (string) ($filters['year'] ?? '');
        $trainingId = (string) ($filters['training_id'] ?? '');
        $divisionId = (string) ($filters['division_id'] ?? '');
        $positionId = (string) ($filters['position_id'] ?? '');
        $progressStatus = (string) ($filters['progress_status'] ?? '');
        $finalStatus = (string) ($filters['final_status'] ?? '');

        // Base training query
        $trainingQuery = Training::query()
            ->withCount(['progressRecords as total_employees'])
            ->when($trainingId !== '', static fn ($builder) => $builder->where('trainings.id', (int) $trainingId))
            ->orderBy('trainings.title');

        // Build filtered progress subquery for conditional aggregates
        $progressBase = EmployeeTrainingProgress::query()
            ->join('employees', 'employees.id', '=', 'employee_training_progress.employee_id')
            ->when($divisionId !== '', static fn ($builder) => $builder->where('employees.division_id', (int) $divisionId))
            ->when($positionId !== '', static fn ($builder) => $builder->where('employees.position_id', (int) $positionId))
            ->when($progressStatus !== '', static fn ($builder) => $builder->where('employee_training_progress.status', $progressStatus))
            ->when($finalStatus !== '', static fn ($builder) => $builder->where('employee_training_progress.final_status', $finalStatus))
            ->when($year !== '', static function ($builder) use ($year, $month): void {
                if ($month !== '') {
                    $builder->whereMonth('employee_training_progress.created_at', (int) $month)
                        ->whereYear('employee_training_progress.created_at', (int) $year);
                } else {
                    $builder->whereYear('employee_training_progress.created_at', (int) $year);
                }
            });

        // Compute summary across all filtered trainings
        $summary = $this->calculateReportSummary($progressBase->get(), $trainingQuery->pluck('id')->all());

        // Attach conditional aggregates per training
        $reportRows = $trainingQuery->get()->map(function (Training $training) use ($progressBase): array {
            $trainingProgress = clone $progressBase;
            $filtered = $trainingProgress->where('employee_training_progress.training_id', $training->id)->get();

            $notStarted = (int) DB::table('training_assignments')
                ->where('training_id', $training->id)
                ->whereNotExists(static function ($q) use ($training): void {
                    $q->select(DB::raw(1))
                        ->from('employee_training_progress')
                        ->whereColumn('employee_training_progress.employee_id', 'training_assignments.target_id')
                        ->where('training_assignments.target_type', 'employee')
                        ->where('employee_training_progress.training_id', $training->id);
                })
                ->count();

            return [
                'training' => $training,
                'total_employees' => $training->total_employees,
                'not_started' => $notStarted,
                'in_progress' => $filtered->whereIn('status', ['not_started', 'pre_test_completed', 'in_material', 'material_completed'])->count(),
                'completed' => $filtered->whereIn('status', ['post_test_completed', 'waiting_essay_review', 'passed', 'failed'])->count(),
                'passed' => $filtered->where('final_status', 'passed')->count(),
                'failed' => $filtered->where('final_status', 'failed')->count(),
                'waiting_review' => $filtered->where('status', 'waiting_essay_review')->count(),
                'avg_pre_test' => $this->avgScoreForTraining($training->id, 'pre_test'),
                'avg_post_test' => $this->avgScoreForTraining($training->id, 'post_test'),
                'completion_pct' => $training->total_employees > 0
                    ? round(($filtered->whereIn('status', ['passed', 'failed', 'post_test_completed'])->count() / $training->total_employees) * 100, 1)
                    : 0,
            ];
        });

        return [
            'reportRows' => $reportRows,
            'summary' => $summary,
            'hasEmptyState' => $reportRows->every(static fn (array $row): bool => $row['total_employees'] === 0 && $reportRows->count() > 0) || $reportRows->isEmpty(),
        ];
    }

    /**
     * @return array{total_assignments:int,total_completed:int,total_passed:int,total_failed:int,total_waiting_review:int,avg_post_test:float|int}
     */
    private function calculateReportSummary(Collection $progress, array $trainingIds): array
    {
        $totalCompleted = $progress->whereIn('status', ['passed', 'failed', 'post_test_completed'])->count();
        $totalPassed = $progress->where('final_status', 'passed')->count();
        $totalFailed = $progress->where('final_status', 'failed')->count();
        $totalWaitingReview = $progress->where('status', 'waiting_essay_review')->count();

        $avgPostTest = 0.0;
        if (! empty($trainingIds)) {
            $avgPostTest = TestAttempt::query()
                ->whereIn('training_id', $trainingIds)
                ->where('test_type', 'post_test')
                ->whereNotNull('final_score')
                ->avg('final_score') ?? 0;
        }

        return [
            'total_assignments' => $progress->count(),
            'total_completed' => $totalCompleted,
            'total_passed' => $totalPassed,
            'total_failed' => $totalFailed,
            'total_waiting_review' => $totalWaitingReview,
            'avg_post_test' => round((float) $avgPostTest, 1),
        ];
    }

    private function avgScoreForTraining(int $trainingId, string $testType): float|int
    {
        return TestAttempt::query()
            ->where('training_id', $trainingId)
            ->where('test_type', $testType)
            ->whereNotNull('final_score')
            ->avg('final_score') ?? 0;
    }

    /**
     * @return array{monthOptions:array<int,string>,yearOptions:array<int,string>,trainingOptions:array<int|string,string>,divisionOptions:array<int|string,string>,positionOptions:array<int|string,string>,progressStatusOptions:array<string,string>,finalStatusOptions:array<string,string>}
     */
    public function trainingReportFilterOptions(): array
    {
        $currentYear = (int) now()->year;

        return [
            'monthOptions' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ],
            'yearOptions' => [
                $currentYear => (string) $currentYear,
                $currentYear - 1 => (string) ($currentYear - 1),
                $currentYear - 2 => (string) ($currentYear - 2),
            ],
            'trainingOptions' => Training::query()->orderBy('title')->pluck('title', 'id')->all(),
            'divisionOptions' => Division::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
            'positionOptions' => Position::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
            'progressStatusOptions' => [
                'not_started' => 'Belum Mulai',
                'pre_test_completed' => 'Pre-Test Selesai',
                'in_material' => 'Sedang Berjalan',
                'material_completed' => 'Materi Selesai',
                'post_test_completed' => 'Post-Test Selesai',
                'waiting_essay_review' => 'Menunggu Penilaian',
                'passed' => 'Lulus',
                'failed' => 'Tidak Lulus',
            ],
            'finalStatusOptions' => [
                'passed' => 'Lulus',
                'failed' => 'Tidak Lulus',
            ],
        ];
    }

    // ============================================================
    // TRAINING REPORTS — Detail (per training employee progress)
    // ============================================================

    /**
     * @return array{training:Training,employeeRows:Collection<int,array<string,mixed>>}
     */
    public function trainingReportDetail(Training $training, array $filters = []): array
    {
        $divisionId = (string) ($filters['division_id'] ?? '');
        $positionId = (string) ($filters['position_id'] ?? '');
        $progressStatus = (string) ($filters['progress_status'] ?? '');
        $finalStatus = (string) ($filters['final_status'] ?? '');

        $progressRecords = EmployeeTrainingProgress::query()
            ->where('training_id', $training->id)
            ->join('employees', 'employees.id', '=', 'employee_training_progress.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->with(['employee.user', 'employee.division', 'employee.position'])
            ->when($divisionId !== '', static fn ($builder) => $builder->where('employees.division_id', (int) $divisionId))
            ->when($positionId !== '', static fn ($builder) => $builder->where('employees.position_id', (int) $positionId))
            ->when($progressStatus !== '', static fn ($builder) => $builder->where('employee_training_progress.status', $progressStatus))
            ->when($finalStatus !== '', static fn ($builder) => $builder->where('employee_training_progress.final_status', $finalStatus))
            ->orderBy('users.name')
            ->get();

        $employeeRows = $progressRecords->map(function (EmployeeTrainingProgress $progress): array {
            $latestPreTest = TestAttempt::query()
                ->where('employee_id', $progress->employee_id)
                ->where('training_id', $progress->training_id)
                ->where('test_type', 'pre_test')
                ->orderByDesc('submitted_at')
                ->first();

            $latestPostTest = TestAttempt::query()
                ->where('employee_id', $progress->employee_id)
                ->where('training_id', $progress->training_id)
                ->where('test_type', 'post_test')
                ->orderByDesc('submitted_at')
                ->first();

            return [
                'progress' => $progress,
                'employee' => $progress->employee,
                'latestPreTest' => $latestPreTest,
                'latestPostTest' => $latestPostTest,
                'statusLabel' => $this->statusLabel($progress->status),
                'finalStatusLabel' => $this->passStatusLabel($progress->final_status),
            ];
        });

        return [
            'training' => $training,
            'employeeRows' => $employeeRows,
        ];
    }

    // ============================================================
    // Status label helpers
    // ============================================================

    private function gradingStatusLabel(?string $status): string
    {
        return match ($status) {
            'auto_graded' => 'Auto-Graded',
            'waiting_manual_review' => 'Menunggu Penilaian',
            'manual_reviewed' => 'Selesai Dinilai',
            default => $status ?? '-',
        };
    }

    private function passStatusLabel(?string $status): ?string
    {
        return match ($status) {
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
            null => null,
            default => $status,
        };
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'not_started' => 'Belum Mulai',
            'pre_test_completed' => 'Pre-Test Selesai',
            'in_material' => 'Sedang Berjalan',
            'material_completed' => 'Materi Selesai',
            'post_test_completed' => 'Post-Test Selesai',
            'waiting_essay_review' => 'Menunggu Penilaian',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
            default => $status ?? '-',
        };
    }
}
