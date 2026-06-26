<?php

namespace App\Services;

use App\Models\EmployeeTrainingProgress;
use App\Models\User;

class EmployeeDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function buildForUser(User $user): array
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return [
                'employee' => null,
                'summaryCards' => $this->summaryCards(0, 0, 0, 0, 0, 0),
                'recentTrainings' => collect(),
                'hasEmptyState' => true,
            ];
        }

        $progressRecords = EmployeeTrainingProgress::query()
            ->with(['training', 'assignment'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('assignment_id')
            ->orderByDesc('created_at')
            ->get();

        $total = $progressRecords->count();
        $notStarted = $progressRecords->where('status', 'not_started')->count();
        $inProgress = $progressRecords->filter(fn ($progress) => in_array((string) $progress->status, ['pre_test_completed', 'in_progress', 'in_material', 'material_completed', 'post_test_completed', 'waiting_essay_review'], true))->count();
        $completed = $progressRecords->filter(fn ($progress) => $progress->completed_at !== null || in_array((string) $progress->status, ['completed', 'passed', 'failed'], true))->count();
        $passed = $progressRecords->filter(fn ($progress) => ($progress->final_status ?? $progress->status) === 'passed')->count();
        $failed = $progressRecords->filter(fn ($progress) => ($progress->final_status ?? $progress->status) === 'failed')->count();

        $recentTrainings = $progressRecords
            ->sortByDesc(fn ($progress) => optional($progress->assignment?->assigned_at)->timestamp ?? optional($progress->created_at)->timestamp ?? 0)
            ->take(5)
            ->values();

        return [
            'employee' => $employee->loadMissing('user'),
            'summaryCards' => $this->summaryCards($total, $notStarted, $inProgress, $completed, $passed, $failed),
            'recentTrainings' => $recentTrainings,
            'hasEmptyState' => $total === 0,
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function summaryCards(int $total, int $notStarted, int $inProgress, int $completed, int $passed, int $failed): array
    {
        return [
            ['label' => 'Total Training', 'value' => (string) $total, 'description' => 'Semua training yang ditugaskan'],
            ['label' => 'Belum Mulai', 'value' => (string) $notStarted, 'description' => 'Training yang belum dimulai'],
            ['label' => 'Sedang Berjalan', 'value' => (string) $inProgress, 'description' => 'Training yang sedang dikerjakan'],
            ['label' => 'Selesai', 'value' => (string) $completed, 'description' => 'Training yang sudah selesai'],
            ['label' => 'Lulus', 'value' => (string) $passed, 'description' => 'Training dengan hasil lulus'],
            ['label' => 'Tidak Lulus', 'value' => (string) $failed, 'description' => 'Training dengan hasil tidak lulus'],
        ];
    }
}
