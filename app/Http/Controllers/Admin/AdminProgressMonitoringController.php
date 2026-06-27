<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTrainingProgress;
use App\Models\Training;
use App\Models\TrainingMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class AdminProgressMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $trainingId = (string) $request->query('training_id', '');
        $divisionId = (string) $request->query('division_id', '');
        $positionId = (string) $request->query('position_id', '');
        $status = (string) $request->query('status', '');
        $finalStatus = (string) $request->query('final_status', '');

        try {
            $progressRecords = EmployeeTrainingProgress::query()
                ->select('employee_training_progress.*')
                ->join('employees', 'employees.id', '=', 'employee_training_progress.employee_id')
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->with(['training', 'employee.user', 'employee.division', 'employee.position'])
                ->when($query !== '', static function ($builder) use ($query): void {
                    $builder->where(static function ($search) use ($query): void {
                        $search->where('users.name', 'like', '%'.$query.'%')
                            ->orWhereIn('training_id', Training::query()->select('id')->where('title', 'like', '%'.$query.'%'));
                    });
                })
                ->when($trainingId !== '', static fn ($builder) => $builder->where('employee_training_progress.training_id', (int) $trainingId))
                ->when($divisionId !== '', static fn ($builder) => $builder->where('employees.division_id', (int) $divisionId))
                ->when($positionId !== '', static fn ($builder) => $builder->where('employees.position_id', (int) $positionId))
                ->when($status !== '', static fn ($builder) => $builder->where('employee_training_progress.status', $status))
                ->when($finalStatus !== '', static fn ($builder) => $builder->where('employee_training_progress.final_status', $finalStatus))
                ->orderByDesc('employee_training_progress.created_at')
                ->paginate(15)
                ->withQueryString();

            return view('admin.progress.index', [
                'progressRecords' => $progressRecords,
                'query' => $query,
                'trainingId' => $trainingId,
                'divisionId' => $divisionId,
                'positionId' => $positionId,
                'status' => $status,
                'finalStatus' => $finalStatus,
                'trainingOptions' => Training::query()->orderBy('title')->pluck('title', 'id')->all(),
                'divisionOptions' => $this->divisionOptions(),
                'positionOptions' => $this->positionOptions(),
                'statusOptions' => $this->statusOptions(),
                'finalStatusOptions' => $this->finalStatusOptions(),
                'hasFilters' => $query !== '' || $trainingId !== '' || $divisionId !== '' || $positionId !== '' || $status !== '' || $finalStatus !== '',
                'hasError' => false,
            ]);
        } catch (Throwable $e) {
            report($e);

            return view('admin.progress.index', [
                'progressRecords' => collect(),
                'query' => $query,
                'trainingId' => $trainingId,
                'divisionId' => $divisionId,
                'positionId' => $positionId,
                'status' => $status,
                'finalStatus' => $finalStatus,
                'trainingOptions' => [],
                'divisionOptions' => [],
                'positionOptions' => [],
                'statusOptions' => $this->statusOptions(),
                'finalStatusOptions' => $this->finalStatusOptions(),
                'hasFilters' => false,
                'hasError' => true,
            ]);
        }
    }

    public function show(EmployeeTrainingProgress $progress): View
    {
        $progress->load(['training', 'employee.user', 'employee.division', 'employee.position', 'assignment']);

        $attempts = DB::table('test_attempts')
            ->where('employee_id', $progress->employee_id)
            ->where('training_id', $progress->training_id)
            ->orderByDesc('created_at')
            ->get();

        $activeMaterials = (int) DB::table('training_materials')
            ->where('training_id', $progress->training_id)
            ->where('is_active', true)
            ->count();

        $openedMaterials = (int) DB::table('material_access_logs')
            ->where('employee_id', $progress->employee_id)
            ->where('training_id', $progress->training_id)
            ->whereIn('material_id', function ($q) use ($progress): void {
                $q->select('id')
                    ->from('training_materials')
                    ->where('training_id', $progress->training_id)
                    ->where('is_active', true);
            })
            ->count();

        return view('admin.progress.show', [
            'progress' => $progress,
            'attempts' => $attempts,
            'activeMaterials' => $activeMaterials,
            'openedMaterials' => $openedMaterials,
            'statusLabel' => $this->statusLabel($progress->status),
            'finalStatusLabel' => $this->finalStatusLabel($progress->final_status),
        ]);
    }

    private function divisionOptions(): array
    {
        return DB::table('divisions')
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private function positionOptions(): array
    {
        return DB::table('positions')
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private function statusOptions(): array
    {
        return [
            '' => 'Semua Status',
            'not_started' => 'Belum Mulai',
            'pre_test_completed' => 'Pre-Test Selesai',
            'in_material' => 'Sedang Berjalan',
            'material_completed' => 'Materi Selesai',
            'post_test_completed' => 'Post-Test Selesai',
            'waiting_essay_review' => 'Menunggu Penilaian',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
        ];
    }

    private function finalStatusOptions(): array
    {
        return [
            '' => 'Semua',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
        ];
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

    private function finalStatusLabel(?string $status): ?string
    {
        return match ($status) {
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
            null => null,
            default => $status,
        };
    }
}
