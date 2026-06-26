<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request, AdminDashboardService $service): View
    {
        $selectedMonth = $this->selectedMonth($request->query('month'));
        $selectedYear = $this->selectedYear($request->query('year'));
        $selectedTrainingId = $this->selectedTrainingId($request->query('training_id'));

        $baseViewData = [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'selectedTrainingId' => $selectedTrainingId,
            'monthOptions' => AdminDashboardService::monthOptions(),
            'yearOptions' => $this->fallbackYearOptions(),
            'trainingOptions' => [],
        ];

        try {
            return view('admin.dashboard', $baseViewData + $service->build($selectedMonth, $selectedYear, $selectedTrainingId) + ['hasError' => false]);
        } catch (Throwable $throwable) {
            report($throwable);

            return view('admin.dashboard', $baseViewData + [
                'hasError' => true,
                'hasEmptyState' => false,
                'summaryCards' => [],
                'progressChart' => [],
                'scoreChart' => [],
                'passChart' => [],
            ]);
        }
    }

    private function selectedMonth(mixed $value): int
    {
        $month = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);

        return $month === false ? (int) now()->month : $month;
    }

    private function selectedYear(mixed $value): int
    {
        $year = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]);

        return $year === false ? (int) now()->year : $year;
    }

    private function selectedTrainingId(mixed $value): ?int
    {
        $trainingId = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $trainingId === false ? null : $trainingId;
    }

    /**
     * @return array<int, string>
     */
    private function fallbackYearOptions(): array
    {
        $currentYear = (int) now()->year;

        return [
            $currentYear => (string) $currentYear,
            $currentYear - 1 => (string) ($currentYear - 1),
            $currentYear - 2 => (string) ($currentYear - 2),
        ];
    }
}
