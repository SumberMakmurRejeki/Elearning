<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanTrainingExport;
use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Services\AdminReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ReportController extends Controller
{
    public function __construct(
        private readonly AdminReportService $reportService,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'month' => $request->query('month'),
            'year' => $request->query('year'),
            'training_id' => $request->query('training_id'),
            'division_id' => $request->query('division_id'),
            'position_id' => $request->query('position_id'),
            'progress_status' => $request->query('progress_status'),
            'final_status' => $request->query('final_status'),
        ];

        try {
            $data = $this->reportService->trainingReportIndex($filters);
            $options = $this->reportService->trainingReportFilterOptions();

            return view('admin.reports.index', [
                ...$data,
                'month' => $filters['month'] ?? '',
                'year' => $filters['year'] ?? '',
                'trainingId' => $filters['training_id'] ?? '',
                'divisionId' => $filters['division_id'] ?? '',
                'positionId' => $filters['position_id'] ?? '',
                'progressStatus' => $filters['progress_status'] ?? '',
                'finalStatus' => $filters['final_status'] ?? '',
                ...$options,
                'hasFilters' => collect($filters)->filter(static fn ($v) => $v !== '' && $v !== null)->isNotEmpty(),
                'hasError' => false,
            ]);
        } catch (Throwable $e) {
            report($e);

            $options = $this->reportService->trainingReportFilterOptions();

            return view('admin.reports.index', [
                'reportRows' => collect(),
                'summary' => [
                    'total_assignments' => 0,
                    'total_completed' => 0,
                    'total_passed' => 0,
                    'total_failed' => 0,
                    'total_waiting_review' => 0,
                    'avg_post_test' => 0,
                ],
                'hasEmptyState' => true,
                'month' => '',
                'year' => '',
                'trainingId' => '',
                'divisionId' => '',
                'positionId' => '',
                'progressStatus' => '',
                'finalStatus' => '',
                ...$options,
                'hasFilters' => false,
                'hasError' => true,
            ]);
        }
    }

    public function show(Training $training, Request $request): View
    {
        $filters = [
            'division_id' => $request->query('division_id'),
            'position_id' => $request->query('position_id'),
            'progress_status' => $request->query('progress_status'),
            'final_status' => $request->query('final_status'),
        ];

        try {
            $data = $this->reportService->trainingReportDetail($training, $filters);
            $options = $this->reportService->trainingReportFilterOptions();

            return view('admin.reports.show', [
                ...$data,
                'divisionId' => $filters['division_id'] ?? '',
                'positionId' => $filters['position_id'] ?? '',
                'progressStatus' => $filters['progress_status'] ?? '',
                'finalStatus' => $filters['final_status'] ?? '',
                'divisionOptions' => $options['divisionOptions'],
                'positionOptions' => $options['positionOptions'],
                'progressStatusOptions' => $options['progressStatusOptions'],
                'finalStatusOptions' => $options['finalStatusOptions'],
                'hasFilters' => collect($filters)->filter(static fn ($v) => $v !== '' && $v !== null)->isNotEmpty(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return view('admin.reports.show', [
                'training' => $training,
                'employeeRows' => collect(),
                'divisionId' => '',
                'positionId' => '',
                'progressStatus' => '',
                'finalStatus' => '',
                'divisionOptions' => [],
                'positionOptions' => [],
                'progressStatusOptions' => [],
                'finalStatusOptions' => [],
                'hasFilters' => false,
                'hasError' => true,
            ]);
        }
    }

    // ============================================================
    // Export
    // ============================================================

    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);
        $data = $this->reportService->trainingReportIndex($filters);

        if ($data['reportRows']->isEmpty() && !$data['hasEmptyState']) {
            return back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        $pdf = Pdf::loadView('admin.reports.pdf', [
            ...$data,
            'exportDate' => now()->translatedFormat('d F Y, H:i'),
            'filters' => $filters,
        ]);

        $filename = 'laporan-training-' . now()->format('Y-m') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->extractFilters($request);
        $export = new LaporanTrainingExport($filters);

        $rows = $export->collection();

        if ($rows->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        $filename = 'laporan-training-' . now()->format('Y-m') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * @return array<string,mixed>
     */
    private function extractFilters(Request $request): array
    {
        return [
            'month' => $request->query('month'),
            'year' => $request->query('year'),
            'training_id' => $request->query('training_id'),
            'division_id' => $request->query('division_id'),
            'position_id' => $request->query('position_id'),
            'progress_status' => $request->query('progress_status'),
            'final_status' => $request->query('final_status'),
        ];
    }
}
