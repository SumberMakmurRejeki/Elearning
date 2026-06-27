<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;
use App\Services\AdminReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TestResultController extends Controller
{
    public function __construct(
        private readonly AdminReportService $reportService,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->query('q'),
            'test_type' => $request->query('test_type'),
            'training_id' => $request->query('training_id'),
            'division_id' => $request->query('division_id'),
            'position_id' => $request->query('position_id'),
            'grading_status' => $request->query('grading_status'),
            'pass_status' => $request->query('pass_status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        try {
            $attempts = $this->reportService->testResultIndex($filters);
            $options = $this->reportService->testResultFilterOptions();

            return view('admin.test-results.index', [
                'attempts' => $attempts,
                'query' => $filters['q'] ?? '',
                'testType' => $filters['test_type'] ?? '',
                'trainingId' => $filters['training_id'] ?? '',
                'divisionId' => $filters['division_id'] ?? '',
                'positionId' => $filters['position_id'] ?? '',
                'gradingStatus' => $filters['grading_status'] ?? '',
                'passStatus' => $filters['pass_status'] ?? '',
                'dateFrom' => $filters['date_from'] ?? '',
                'dateTo' => $filters['date_to'] ?? '',
                ...$options,
                'hasFilters' => collect($filters)->filter(static fn ($v) => $v !== '' && $v !== null)->isNotEmpty(),
                'hasError' => false,
            ]);
        } catch (Throwable $e) {
            report($e);

            return view('admin.test-results.index', [
                'attempts' => collect(),
                'query' => '',
                'testType' => '',
                'trainingId' => '',
                'divisionId' => '',
                'positionId' => '',
                'gradingStatus' => '',
                'passStatus' => '',
                'dateFrom' => '',
                'dateTo' => '',
                'trainingOptions' => [],
                'divisionOptions' => [],
                'positionOptions' => [],
                'testTypeOptions' => ['pre_test' => 'Pre-Test', 'post_test' => 'Post-Test'],
                'gradingStatusOptions' => ['auto_graded' => 'Auto-Graded', 'waiting_manual_review' => 'Menunggu Penilaian', 'manual_reviewed' => 'Selesai Dinilai'],
                'passStatusOptions' => ['passed' => 'Lulus', 'failed' => 'Tidak Lulus'],
                'hasFilters' => false,
                'hasError' => true,
            ]);
        }
    }

    public function show(TestAttempt $attempt): View
    {
        $data = $this->reportService->testResultDetail($attempt);

        return view('admin.test-results.show', $data);
    }
}
