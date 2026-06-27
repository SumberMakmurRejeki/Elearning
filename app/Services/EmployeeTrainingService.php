<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTrainingProgress;
use App\Models\TestAttempt;
use App\Models\Training;
use App\Models\TrainingAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeTrainingService
{
    /**
     * @return array<string, mixed>
     */
    public function buildIndexForUser(User $user, string $search = '', string $statusFilter = ''): array
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return [
                'employee' => null,
                'trainings' => collect(),
                'search' => $search,
                'statusFilter' => $statusFilter,
                'statusOptions' => $this->statusOptions(),
                'hasEmptyState' => true,
            ];
        }

        $rows = $this->trainingRows($employee, $search);

        if ($statusFilter !== '') {
            $rows = $rows->filter(static fn (array $row): bool => $row['progressGroup'] === $statusFilter)->values();
        }

        return [
            'employee' => $employee->loadMissing('user'),
            'trainings' => $rows,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'statusOptions' => $this->statusOptions(),
            'hasEmptyState' => $rows->isEmpty(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildShowForUser(User $user, Training $training): ?array
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return null;
        }

        $assignment = $this->resolveAssignmentForEmployee($employee, $training);

        if ($assignment === null) {
            return null;
        }

        $progress = $assignment->progressRecords->first()
            ?? EmployeeTrainingProgress::query()
                ->where('employee_id', $employee->id)
                ->where('training_id', $training->id)
                ->first();

        $progressGroup = $this->progressGroup($progress);
        $trainingStatusVariant = match ($training->status) {
            'published' => 'success',
            'archived' => 'neutral',
            default => 'warning',
        };

        $activeMaterialsCount = (int) DB::table('training_materials')
            ->where('training_id', $training->id)
            ->where('is_active', true)
            ->count();

        $preTestQuestionCount = (int) DB::table('questions')
            ->where('training_id', $training->id)
            ->where('test_type', 'pre_test')
            ->where('is_active', true)
            ->count();

        $postTestQuestionCount = (int) DB::table('questions')
            ->where('training_id', $training->id)
            ->where('test_type', 'post_test')
            ->where('is_active', true)
            ->count();

        $finalStatus = $progress?->final_status;

        $postTestAttempts = TestAttempt::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->where('test_type', 'post_test')
            ->get();

        $attemptCount = $postTestAttempts->count();

        $bestScore = $postTestAttempts->filter(fn (TestAttempt $a): bool => $a->final_score !== null)
            ->max(fn (TestAttempt $a): float => (float) $a->final_score);

        $hasPendingEssay = $postTestAttempts->contains(fn (TestAttempt $a): bool => $a->grading_status === 'waiting_manual_review');

        $isPassed = $progress?->final_status === 'passed';

        $retakeAllowed = $training->allow_post_test_retake
            && ! $isPassed
            && ! $hasPendingEssay
            && $postTestAttempts->isNotEmpty();

        $maxAttempts = $training->max_post_test_attempt;

        if ($retakeAllowed && $maxAttempts !== null && $attemptCount >= $maxAttempts) {
            $retakeAllowed = false;
        }

        $retakeAttemptsRemaining = $maxAttempts !== null ? max(0, $maxAttempts - $attemptCount) : null;

        return [
            'employee' => $employee->loadMissing('user'),
            'training' => $training,
            'assignment' => $assignment,
            'progress' => $progress,
            'trainingStatusLabel' => ucfirst($training->status),
            'trainingStatusVariant' => $trainingStatusVariant,
            'progressGroup' => $progressGroup,
            'progressStatusLabel' => $this->statusLabel($progressGroup),
            'progressStatusVariant' => $this->statusVariant($progressGroup),
            'assignedAt' => $assignment->assigned_at?->translatedFormat('d M Y') ?? '-',
            'deadline' => $assignment->deadline?->translatedFormat('d M Y') ?? '-',
            'hasPreTest' => (bool) $training->has_pre_test,
            'preTestQuestionCount' => $preTestQuestionCount,
            'hasPostTest' => (bool) $training->has_post_test,
            'postTestQuestionCount' => $postTestQuestionCount,
            'passingGradeLabel' => $training->passing_grade !== null ? number_format((float) $training->passing_grade, 0) : '-',
            'showScoreToEmployee' => (bool) $training->show_score_to_employee,
            'finalScoreLabel' => $progress?->final_score !== null ? number_format((float) $progress->final_score, 0) : '-',
            'finalStatusLabel' => $this->finalStatusLabel($finalStatus),
            'finalStatusVariant' => $this->finalStatusVariant($finalStatus),
            'activeMaterialsCount' => $activeMaterialsCount,
            'stepCards' => $this->stepCards($training, $progress, $activeMaterialsCount, $preTestQuestionCount, $postTestQuestionCount),
            'primaryAction' => $this->primaryAction($training, $progress, $activeMaterialsCount, $preTestQuestionCount, $postTestQuestionCount),
            'secondaryAction' => $this->secondaryAction($training, $progress, $activeMaterialsCount),
            'attemptCount' => $attemptCount,
            'bestScoreLabel' => $bestScore !== null ? number_format((float) $bestScore, 2) : null,
            'retakeAllowed' => $retakeAllowed,
            'retakeAttemptsRemaining' => $retakeAttemptsRemaining,
            'hasPendingEssay' => $hasPendingEssay,
            'isPassed' => $isPassed,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function trainingRows(Employee $employee, string $search): Collection
    {
        $rows = TrainingAssignment::query()
            ->with([
                'training',
                'progressRecords' => static fn ($query) => $query->where('employee_id', $employee->id),
            ])
            ->where(static function ($query) use ($employee): void {
                $query->where(static function ($builder) use ($employee): void {
                    $builder->where('target_type', 'employee')
                        ->where('target_id', $employee->id);
                })->orWhere(static function ($builder) use ($employee): void {
                    $builder->where('target_type', 'division')
                        ->where('target_id', $employee->division_id);
                })->orWhere(static function ($builder) use ($employee): void {
                    $builder->where('target_type', 'position')
                        ->where('target_id', $employee->position_id);
                });
            })
            ->when($search !== '', static function ($query) use ($search): void {
                $query->whereHas('training', static function ($trainingQuery) use ($search): void {
                    $trainingQuery->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get();

        return $rows->map(function (TrainingAssignment $assignment): array {
            $training = $assignment->training;
            $progress = $assignment->progressRecords->first();
            $progressGroup = $this->progressGroup($progress);

            return [
                'training' => $training,
                'assignment' => $assignment,
                'progress' => $progress,
                'progressGroup' => $progressGroup,
                'statusLabel' => $this->statusLabel($progressGroup),
                'statusVariant' => $this->statusVariant($progressGroup),
                'trainingStatusLabel' => ucfirst((string) $training?->status),
                'trainingStatusVariant' => match ($training?->status) {
                    'published' => 'success',
                    'archived' => 'neutral',
                    default => 'warning',
                },
                'assignedAt' => $assignment->assigned_at?->translatedFormat('d M Y') ?? '-',
                'deadline' => $assignment->deadline?->translatedFormat('d M Y') ?? '-',
                'activeMaterialsCount' => (int) DB::table('training_materials')
                    ->where('training_id', $training?->id)
                    ->where('is_active', true)
                    ->count(),
                'stepCards' => $this->stepCards($training, $progress, (int) DB::table('training_materials')
                    ->where('training_id', $training?->id)
                    ->where('is_active', true)
                    ->count(), (int) DB::table('questions')
                    ->where('training_id', $training?->id)
                    ->where('test_type', 'pre_test')
                    ->where('is_active', true)
                    ->count(), (int) DB::table('questions')
                    ->where('training_id', $training?->id)
                    ->where('test_type', 'post_test')
                    ->where('is_active', true)
                    ->count()),
            ];
        });
    }

    private function resolveAssignmentForEmployee(Employee $employee, Training $training): ?TrainingAssignment
    {
        return TrainingAssignment::query()
            ->with(['progressRecords' => static fn ($query) => $query->where('employee_id', $employee->id)])
            ->where('training_id', $training->id)
            ->where(static function ($query) use ($employee): void {
                $query->where(static function ($builder) use ($employee): void {
                    $builder->where('target_type', 'employee')
                        ->where('target_id', $employee->id);
                })->orWhere(static function ($builder) use ($employee): void {
                    $builder->where('target_type', 'division')
                        ->where('target_id', $employee->division_id);
                })->orWhere(static function ($builder) use ($employee): void {
                    $builder->where('target_type', 'position')
                        ->where('target_id', $employee->position_id);
                });
            })
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->first();
    }

    private function progressGroup(?EmployeeTrainingProgress $progress): string
    {
        if ($progress === null || $progress->status === 'not_started') {
            return 'not_started';
        }

        $status = (string) $progress->status;
        $finalStatus = (string) ($progress->final_status ?? '');

        if (in_array($finalStatus, ['passed', 'failed'], true)) {
            return $finalStatus;
        }

        return match ($status) {
            'pre_test_completed', 'in_progress', 'in_material', 'material_completed', 'post_test_completed', 'waiting_essay_review', 'completed' => 'in_progress',
            default => 'in_progress',
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'not_started' => 'Belum Mulai',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function statusVariant(string $status): string
    {
        return match ($status) {
            'not_started' => 'neutral',
            'passed' => 'success',
            'failed' => 'danger',
            'completed' => 'ink',
            default => 'info',
        };
    }

    private function finalStatusLabel(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        return match ($status) {
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function finalStatusVariant(?string $status): string
    {
        return match ($status) {
            'passed' => 'success',
            'failed' => 'danger',
            null => 'neutral',
            default => 'info',
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function stepCards(?Training $training, ?EmployeeTrainingProgress $progress, int $activeMaterialsCount, int $preTestQuestionCount, int $postTestQuestionCount): array
    {
        $preTestDone = $progress !== null && in_array((string) $progress->status, ['pre_test_completed', 'in_material', 'material_completed', 'post_test_completed', 'waiting_essay_review', 'completed', 'passed', 'failed'], true);
        $materialsDone = $progress !== null && in_array((string) $progress->status, ['material_completed', 'post_test_completed', 'waiting_essay_review', 'completed', 'passed', 'failed'], true);
        $postTestDone = $progress !== null && in_array((string) $progress->status, ['post_test_completed', 'waiting_essay_review', 'completed', 'passed', 'failed'], true);

        return [
            [
                'label' => 'Pre-Test',
                'value' => $training?->has_pre_test ? ($preTestQuestionCount > 0 ? 'Tersedia' : 'Belum tersedia') : 'Tidak ada',
                'status' => $training?->has_pre_test ? ($preTestDone ? 'done' : 'current') : 'inactive',
            ],
            [
                'label' => 'Materi',
                'value' => $activeMaterialsCount > 0 ? ($materialsDone ? 'Selesai' : 'Tersedia') : 'Belum tersedia',
                'status' => $training?->has_pre_test && ! $preTestDone ? 'locked' : ($materialsDone ? 'done' : 'current'),
            ],
            [
                'label' => 'Post-Test',
                'value' => $training?->has_post_test ? ($postTestQuestionCount > 0 ? ($postTestDone ? 'Selesai' : 'Tersedia') : 'Belum tersedia') : 'Tidak ada',
                'status' => $training?->has_post_test ? (($training?->has_pre_test && ! $preTestDone) || ($activeMaterialsCount > 0 && ! $materialsDone) ? 'locked' : ($postTestDone ? 'done' : 'current')) : 'inactive',
            ],
            [
                'label' => 'Hasil',
                'value' => $progress?->final_status !== null ? $this->finalStatusLabel((string) $progress->final_status) ?? '-' : 'Belum tersedia',
                'status' => $progress?->final_status !== null ? 'done' : 'locked',
            ],
        ];
    }

    /**
     * @return array<string, string>|null
     */
    private function primaryAction(?Training $training, ?EmployeeTrainingProgress $progress, int $activeMaterialsCount, int $preTestQuestionCount, int $postTestQuestionCount): ?array
    {
        if ($training === null) {
            return null;
        }

        $preTestDone = $progress !== null && in_array((string) $progress->status, ['pre_test_completed', 'in_material', 'material_completed', 'post_test_completed', 'waiting_essay_review', 'completed', 'passed', 'failed'], true);
        $materialsDone = $progress !== null && in_array((string) $progress->status, ['material_completed', 'post_test_completed', 'waiting_essay_review', 'completed', 'passed', 'failed'], true);
        $postTestDone = $progress !== null && in_array((string) $progress->status, ['post_test_completed', 'waiting_essay_review', 'completed', 'passed', 'failed'], true);

        if ($training->has_pre_test && ! $preTestDone) {
            return [
                'label' => 'Mulai Pre-Test',
                'href' => route('employee.training.action', [$training, 'pre-test']),
            ];
        }

        if ($activeMaterialsCount > 0 && ! $materialsDone) {
            return [
                'label' => 'Lihat Materi',
                'href' => route('employee.training.action', [$training, 'materi']),
            ];
        }

        if ($training->has_post_test && ! $postTestDone) {
            return [
                'label' => 'Mulai Post-Test',
                'href' => route('employee.training.action', [$training, 'post-test']),
            ];
        }

        if ($progress?->final_status !== null) {
            return [
                'label' => 'Lihat Hasil',
                'href' => route('employee.training.action', [$training, 'hasil']),
            ];
        }

        return null;
    }

    /**
     * @return array<string, string>|null
     */
    private function secondaryAction(?Training $training, ?EmployeeTrainingProgress $progress, int $activeMaterialsCount): ?array
    {
        if ($training === null || $progress?->final_status === null) {
            return null;
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return [
            '' => 'Semua Status',
            'not_started' => 'Belum Mulai',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
        ];
    }
}
