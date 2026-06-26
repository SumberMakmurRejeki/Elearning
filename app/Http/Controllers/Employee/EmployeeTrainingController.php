<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Services\EmployeeTrainingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EmployeeTrainingController extends Controller
{
    public function index(Request $request, EmployeeTrainingService $service): View
    {
        $user = $request->user();

        try {
            return view('employee.training.index', $service->buildIndexForUser(
                $user,
                trim((string) $request->query('q', '')),
                (string) $request->query('status', '')
            ) + [
                'hasError' => false,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return view('employee.training.index', [
                'employee' => $user?->employee,
                'trainings' => collect(),
                'search' => (string) $request->query('q', ''),
                'statusFilter' => (string) $request->query('status', ''),
                'statusOptions' => $service->statusOptions(),
                'hasEmptyState' => false,
                'hasError' => true,
            ]);
        }
    }

    public function show(Training $training, EmployeeTrainingService $service): View
    {
        $user = auth()->user();

        try {
            $data = $service->buildShowForUser($user, $training);
        } catch (Throwable $throwable) {
            report($throwable);

            return view('employee.training.show', [
                'hasError' => true,
            ]);
        }

        abort_if($data === null, 404);

        return view('employee.training.show', $data + [
            'hasError' => false,
        ]);
    }

    public function action(Training $training, string $action, EmployeeTrainingService $service): View
    {
        $user = auth()->user();
        $data = $service->buildShowForUser($user, $training);

        abort_if($data === null, 404);

        $title = match ($action) {
            'pre-test' => 'Mulai Pre-Test',
            'materi' => 'Lihat Materi',
            'post-test' => 'Mulai Post-Test',
            'hasil' => 'Lihat Hasil',
            default => 'Aksi Training',
        };

        return view('placeholders.section', [
            'role' => 'employee',
            'title' => $title,
            'description' => 'Placeholder untuk langkah training berikutnya. Fitur detailnya akan dikerjakan pada task selanjutnya.',
        ]);
    }
}
