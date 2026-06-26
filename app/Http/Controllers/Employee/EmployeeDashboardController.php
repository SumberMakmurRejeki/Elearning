<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\EmployeeDashboardService;
use Illuminate\View\View;
use Throwable;

class EmployeeDashboardController extends Controller
{
    public function __invoke(EmployeeDashboardService $service): View
    {
        $user = auth()->user();

        try {
            return view('employee.dashboard', $service->buildForUser($user) + [
                'hasError' => false,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return view('employee.dashboard', [
                'employee' => $user?->employee,
                'summaryCards' => [],
                'recentTrainings' => collect(),
                'hasEmptyState' => false,
                'hasError' => true,
            ]);
        }
    }
}
