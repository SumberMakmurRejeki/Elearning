<?php

namespace App\Exports;

use App\Models\EmployeeTrainingProgress;
use App\Models\TestAttempt;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanTrainingExport implements FromCollection, WithHeadings, WithStyles
{
    /** @var array<string,mixed> */
    private array $filters;

    /** @var Collection<int,array<string,mixed>> */
    private Collection $rows;

    /**
     * @param array<string,mixed> $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->rows = $this->buildRows();
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Karyawan',
            'NIP',
            'Divisi',
            'Jabatan',
            'Nama Training',
            'Status Progress',
            'Nilai Pre-Test',
            'Nilai Post-Test',
            'Status Kelulusan',
            'Tanggal Mulai',
            'Tanggal Selesai',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C9E0FC'],
                ],
            ],
        ];
    }

    /**
     * @return Collection<int,array<string,mixed>>
     */
    private function buildRows(): Collection
    {
        $divisionId = (string) ($this->filters['division_id'] ?? '');
        $positionId = (string) ($this->filters['position_id'] ?? '');
        $progressStatus = (string) ($this->filters['progress_status'] ?? '');
        $finalStatus = (string) ($this->filters['final_status'] ?? '');
        $trainingId = (string) ($this->filters['training_id'] ?? '');
        $month = (string) ($this->filters['month'] ?? '');
        $year = (string) ($this->filters['year'] ?? '');

        $query = EmployeeTrainingProgress::query()
            ->join('employees', 'employees.id', '=', 'employee_training_progress.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('trainings', 'trainings.id', '=', 'employee_training_progress.training_id')
            ->leftJoin('divisions', 'divisions.id', '=', 'employees.division_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->select(
                'employee_training_progress.*',
                'users.name as employee_name',
                'employees.employee_number',
                'divisions.name as division_name',
                'positions.name as position_name',
                'trainings.title as training_title',
                'trainings.passing_grade',
            )
            ->when($trainingId !== '', static fn ($q) => $q->where('employee_training_progress.training_id', (int) $trainingId))
            ->when($divisionId !== '', static fn ($q) => $q->where('employees.division_id', (int) $divisionId))
            ->when($positionId !== '', static fn ($q) => $q->where('employees.position_id', (int) $positionId))
            ->when($progressStatus !== '', static fn ($q) => $q->where('employee_training_progress.status', $progressStatus))
            ->when($finalStatus !== '', static fn ($q) => $q->where('employee_training_progress.final_status', $finalStatus))
            ->when($year !== '', static function ($q) use ($year, $month): void {
                if ($month !== '') {
                    $q->whereMonth('employee_training_progress.created_at', (int) $month)
                        ->whereYear('employee_training_progress.created_at', (int) $year);
                } else {
                    $q->whereYear('employee_training_progress.created_at', (int) $year);
                }
            })
            ->orderBy('users.name')
            ->get();

        $statusLabels = [
            'not_started' => 'Belum Mulai',
            'pre_test_completed' => 'Pre-Test Selesai',
            'in_material' => 'Sedang Berjalan',
            'material_completed' => 'Materi Selesai',
            'post_test_completed' => 'Post-Test Selesai',
            'waiting_essay_review' => 'Menunggu Penilaian',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
        ];

        return $query->map(function ($row) use ($statusLabels, &$i): array {
            $i = ($i ?? 0) + 1;

            $latestPreTest = TestAttempt::query()
                ->where('employee_id', $row->employee_id)
                ->where('training_id', $row->training_id)
                ->where('test_type', 'pre_test')
                ->orderByDesc('submitted_at')
                ->value('final_score');

            $latestPostTest = TestAttempt::query()
                ->where('employee_id', $row->employee_id)
                ->where('training_id', $row->training_id)
                ->where('test_type', 'post_test')
                ->orderByDesc('submitted_at')
                ->value('final_score');

            $finalStatusLabel = match ($row->final_status) {
                'passed' => 'Lulus',
                'failed' => 'Tidak Lulus',
                default => '-',
            };

            return [
                'no' => $i,
                'employee_name' => $row->employee_name,
                'employee_number' => $row->employee_number ?? '-',
                'division_name' => $row->division_name ?? '-',
                'position_name' => $row->position_name ?? '-',
                'training_title' => $row->training_title,
                'status' => $statusLabels[$row->status] ?? $row->status,
                'pre_test_score' => $latestPreTest !== null ? number_format((float) $latestPreTest, 2) : '-',
                'post_test_score' => $latestPostTest !== null ? number_format((float) $latestPostTest, 2) : '-',
                'final_status' => $finalStatusLabel,
                'started_at' => $row->pre_test_completed_at !== null
                    ? \Carbon\Carbon::parse($row->pre_test_completed_at)->translatedFormat('d M Y')
                    : '-',
                'completed_at' => $row->completed_at !== null
                    ? \Carbon\Carbon::parse($row->completed_at)->translatedFormat('d M Y')
                    : '-',
            ];
        })->map(static fn ($row) => array_values($row));
    }
}
