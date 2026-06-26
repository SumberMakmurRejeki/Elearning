<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Assignment\StoreTrainingAssignmentRequest;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeTrainingProgress;
use App\Models\Position;
use App\Models\Training;
use App\Models\TrainingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class TrainingAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $trainingId = (string) $request->query('training_id', '');
        $divisionId = (string) $request->query('division_id', '');
        $positionId = (string) $request->query('position_id', '');
        $status = (string) $request->query('status', '');

        $progressRecords = EmployeeTrainingProgress::query()
            ->select('employee_training_progress.*')
            ->join('employees', 'employees.id', '=', 'employee_training_progress.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->with(['training', 'assignment', 'employee.user', 'employee.division', 'employee.position'])
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
            ->orderByDesc('employee_training_progress.created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.penugasan.index', [
            'progressRecords' => $progressRecords,
            'query' => $query,
            'trainingId' => $trainingId,
            'divisionId' => $divisionId,
            'positionId' => $positionId,
            'status' => $status,
            'trainingOptions' => $this->trainingOptions(),
            'divisionOptions' => $this->divisionOptions(),
            'positionOptions' => $this->positionOptions(),
            'statusOptions' => $this->statusOptions(),
            'hasFilters' => $query !== '' || $trainingId !== '' || $divisionId !== '' || $positionId !== '' || $status !== '',
        ]);
    }

    public function create(): View
    {
        return view('admin.penugasan.create', [
            'trainingOptions' => $this->trainingOptions(),
            'divisionOptions' => $this->divisionOptions(),
            'positionOptions' => $this->positionOptions(),
            'employeeOptions' => $this->employeeOptions(),
            'targetOptions' => $this->targetOptions(),
            'backRoute' => route('admin.penugasan.index'),
        ]);
    }

    public function store(StoreTrainingAssignmentRequest $request): RedirectResponse
    {
        $targets = $this->resolveTargets($request);

        if ($targets === []) {
            return redirect()
                ->route('admin.penugasan.index')
                ->with('error', 'Semua karyawan terpilih sudah memiliki penugasan training ini.');
        }

        $created = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use ($request, $targets, &$created, &$skipped): void {
                foreach ($targets as $target) {
                    if ($this->progressExists($target['employee_id'], (int) $request->input('training_id'))) {
                        $skipped++;
                        continue;
                    }

                    $assignment = TrainingAssignment::create([
                        'training_id' => (int) $request->input('training_id'),
                        'target_type' => $request->input('target_type'),
                        'target_id' => $target['target_id'],
                        'assigned_at' => now()->toDateString(),
                        'deadline' => null,
                        'is_active' => true,
                        'created_by' => $request->user()?->id,
                    ]);

                    EmployeeTrainingProgress::create([
                        'employee_id' => $target['employee_id'],
                        'training_id' => (int) $request->input('training_id'),
                        'assignment_id' => $assignment->id,
                        'status' => 'not_started',
                    ]);

                    $created++;
                }
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withInput()->with('error', 'Data penugasan gagal disimpan. Silakan coba lagi.');
        }

        if ($created === 0) {
            return redirect()
                ->route('admin.penugasan.index')
                ->with('error', 'Semua karyawan terpilih sudah memiliki penugasan training ini.');
        }

        $message = 'Penugasan berhasil untuk '.$created.' karyawan.';

        if ($skipped > 0) {
            $message .= ' '.$skipped.' karyawan dilewati karena sudah pernah ditugaskan.';
        }

        return redirect()
            ->route('admin.penugasan.index')
            ->with('success', $message);
    }

    public function show(EmployeeTrainingProgress $trainingProgress): View
    {
        $trainingProgress->load(['training', 'assignment', 'employee.user', 'employee.division', 'employee.position']);

        return view('admin.penugasan.show', [
            'progress' => $trainingProgress,
            'canCancel' => $this->canCancel($trainingProgress),
            'statusLabel' => $this->statusLabel($trainingProgress->status),
        ]);
    }

    public function destroy(EmployeeTrainingProgress $trainingProgress): RedirectResponse
    {
        if (! $this->canCancel($trainingProgress)) {
            return redirect()
                ->back()
                ->with('error', 'Penugasan tidak dapat dibatalkan karena karyawan sudah memulai training.');
        }

        try {
            DB::transaction(function () use ($trainingProgress): void {
                $assignmentId = $trainingProgress->assignment_id;
                $trainingProgress->delete();

                if ($assignmentId !== null) {
                    $remaining = EmployeeTrainingProgress::query()->where('assignment_id', $assignmentId)->exists();

                    if (! $remaining) {
                        TrainingAssignment::query()->whereKey($assignmentId)->delete();
                    }
                }
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()
                ->back()
                ->with('error', 'Penugasan gagal dibatalkan. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.penugasan.index')
            ->with('success', 'Penugasan training berhasil dibatalkan.');
    }

    /**
     * @return array<int, array{employee_id:int,target_id:int}>
     */
    private function resolveTargets(StoreTrainingAssignmentRequest $request): array
    {
        $targetType = $request->input('target_type');

        if ($targetType === 'employee') {
            return Employee::query()
                ->whereIn('id', (array) $request->input('employee_ids', []))
                ->where('is_active', true)
                ->get(['id'])
                ->map(fn ($employee): array => ['employee_id' => (int) $employee->id, 'target_id' => (int) $employee->id])
                ->all();
        }

        if ($targetType === 'division') {
            $divisionId = (int) $request->input('division_id');

            return Employee::query()
                ->where('division_id', $divisionId)
                ->where('is_active', true)
                ->get(['id'])
                ->map(fn ($employee): array => ['employee_id' => (int) $employee->id, 'target_id' => $divisionId])
                ->all();
        }

        $positionId = (int) $request->input('position_id');

        return Employee::query()
            ->where('position_id', $positionId)
            ->where('is_active', true)
            ->get(['id'])
            ->map(fn ($employee): array => ['employee_id' => (int) $employee->id, 'target_id' => $positionId])
            ->all();
    }

    private function progressExists(int $employeeId, int $trainingId): bool
    {
        return EmployeeTrainingProgress::query()
            ->where('employee_id', $employeeId)
            ->where('training_id', $trainingId)
            ->exists();
    }

    private function canCancel(EmployeeTrainingProgress $progress): bool
    {
        if ($progress->status !== 'not_started') {
            return false;
        }

        return ! DB::table('material_access_logs')
            ->where('employee_id', $progress->employee_id)
            ->where('training_id', $progress->training_id)
            ->exists()
            && ! DB::table('test_attempts')
                ->where('employee_id', $progress->employee_id)
                ->where('training_id', $progress->training_id)
                ->exists();
    }

    /**
     * @return array<string, string>
     */
    private function trainingOptions(): array
    {
        return Training::query()->where('status', 'published')->orderBy('title')->pluck('title', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private function divisionOptions(): array
    {
        return Division::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private function positionOptions(): array
    {
        return Position::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private function employeeOptions(): array
    {
        return Employee::query()
            ->select('employees.id', 'users.name')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('employees.is_active', true)
            ->orderBy('users.name')
            ->pluck('users.name', 'employees.id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function targetOptions(): array
    {
        return [
            'employee' => 'Karyawan tertentu',
            'division' => 'Divisi',
            'position' => 'Jabatan',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return [
            '' => 'Semua',
            'not_started' => 'Belum Mulai',
            'in_material' => 'Sedang Berjalan',
            'material_completed' => 'Materi Selesai',
            'post_test_completed' => 'Post-Test Selesai',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
            'completed' => 'Selesai',
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'not_started' => 'Belum Mulai',
            'in_material' => 'Sedang Berjalan',
            'material_completed' => 'Materi Selesai',
            'post_test_completed' => 'Post-Test Selesai',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
            'completed' => 'Selesai',
            default => $status,
        };
    }
}
