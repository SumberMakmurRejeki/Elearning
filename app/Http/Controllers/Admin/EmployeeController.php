<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Employee\ResetEmployeePasswordRequest;
use App\Http\Requests\Admin\Employee\StoreEmployeeRequest;
use App\Http\Requests\Admin\Employee\UpdateEmployeeRequest;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $divisionId = (string) $request->query('division_id', '');
        $positionId = (string) $request->query('position_id', '');
        $status = (string) $request->query('status', '');

        $employees = Employee::query()
            ->select('employees.*')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->with(['user', 'division', 'position'])
            ->when($query !== '', static function ($builder) use ($query): void {
                $builder->where(static function ($search) use ($query): void {
                    $search->where('users.name', 'like', '%'.$query.'%')
                        ->orWhere('users.username', 'like', '%'.$query.'%')
                        ->orWhere('employees.employee_number', 'like', '%'.$query.'%');
                });
            })
            ->when($divisionId !== '', static fn ($builder) => $builder->where('employees.division_id', (int) $divisionId))
            ->when($positionId !== '', static fn ($builder) => $builder->where('employees.position_id', (int) $positionId))
            ->when($status === 'active', static fn ($builder) => $builder->where('users.is_active', true))
            ->when($status === 'inactive', static fn ($builder) => $builder->where('users.is_active', false))
            ->orderBy('users.name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.karyawan.index', [
            'employees' => $employees,
            'query' => $query,
            'divisionId' => $divisionId,
            'positionId' => $positionId,
            'status' => $status,
            'divisionOptions' => $this->divisionOptions(),
            'positionOptions' => $this->positionOptions(),
            'statusOptions' => $this->statusOptions(),
            'hasFilters' => $query !== '' || $divisionId !== '' || $positionId !== '' || $status !== '',
        ]);
    }

    public function create(): View
    {
        return view('admin.karyawan.create', [
            'employee' => new Employee(['is_active' => true]),
            'user' => new User(['is_active' => true, 'role' => 'karyawan']),
            'divisionOptions' => $this->divisionOptions(),
            'positionOptions' => $this->positionOptions(),
            'statusOptions' => $this->formStatusOptions(),
            'backRoute' => route('admin.karyawan.index'),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request): void {
                $validated = $request->validated();

                $user = User::create([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'password' => $validated['password'],
                    'role' => $validated['role'] ?? 'karyawan',
                    'is_active' => $request->boolean('is_active'),
                ]);

                Employee::create([
                    'user_id' => $user->id,
                    'employee_number' => $validated['employee_number'] ?: null,
                    'division_id' => (int) $validated['division_id'],
                    'position_id' => (int) $validated['position_id'],
                    'is_active' => $request->boolean('is_active'),
                ]);
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withInput()->with('error', 'Data karyawan gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil disimpan.');
    }

    public function show(Employee $employee): View
    {
        $employee->load(['user', 'division', 'position']);

        return view('admin.karyawan.show', [
            'employee' => $employee,
            'hasDependencies' => $this->hasTrainingDependencies($employee),
        ]);
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['user', 'division', 'position']);

        return view('admin.karyawan.edit', [
            'employee' => $employee,
            'user' => $employee->user,
            'divisionOptions' => $this->divisionOptions(),
            'positionOptions' => $this->positionOptions(),
            'statusOptions' => $this->formStatusOptions(),
            'backRoute' => route('admin.karyawan.index'),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $employee): void {
                $validated = $request->validated();
                $user = $employee->user()->firstOrFail();

                $user->fill([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'is_active' => $request->boolean('is_active'),
                    'role' => $validated['role'] ?? 'karyawan',
                ]);

                if ($request->filled('password')) {
                    $user->password = $validated['password'];
                }

                $user->save();

                $employee->update([
                    'employee_number' => $validated['employee_number'] ?: null,
                    'division_id' => (int) $validated['division_id'],
                    'position_id' => (int) $validated['position_id'],
                    'is_active' => $request->boolean('is_active'),
                ]);
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withInput()->with('error', 'Perubahan data karyawan gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Perubahan data karyawan berhasil disimpan.');
    }

    public function resetPassword(ResetEmployeePasswordRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $user = $employee->user()->firstOrFail();

            $user->update([
                'password' => $request->validated()['password'],
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->with('error', 'Password karyawan gagal direset. Silakan coba lagi.');
        }

        return back()->with('success', 'Password karyawan berhasil direset.');
    }

    public function updateStatus(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($employee, $validated): void {
                $employee->update(['is_active' => (bool) $validated['is_active']]);

                $employee->user()->update(['is_active' => (bool) $validated['is_active']]);
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->with('error', 'Status akun karyawan gagal diubah. Silakan coba lagi.');
        }

        return back()->with('success', $validated['is_active'] ? 'Karyawan berhasil diaktifkan kembali.' : 'Karyawan berhasil dinonaktifkan.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        if ($this->hasTrainingDependencies($employee)) {
            return back()->with('error', 'Karyawan tidak dapat dihapus karena sudah memiliki data training atau hasil test. Sarankan admin untuk menonaktifkan akun karyawan saja.');
        }

        try {
            DB::transaction(function () use ($employee): void {
                $user = $employee->user()->firstOrFail();

                $employee->delete();
                $user->delete();
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->with('error', 'Data karyawan gagal dihapus. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil dihapus permanen.');
    }

    public function previewIndex(): View
    {
        return $this->index(request());
    }

    /**
     * @return array<string, string>
     */
    private function divisionOptions(): array
    {
        return Division::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private function positionOptions(): array
    {
        return Position::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return [
            '' => 'Semua',
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formStatusOptions(): array
    {
        return [
            '1' => 'Aktif',
            '0' => 'Nonaktif',
        ];
    }

    private function hasTrainingDependencies(Employee $employee): bool
    {
        return DB::table('employee_training_progress')->where('employee_id', $employee->id)->exists()
            || DB::table('material_access_logs')->where('employee_id', $employee->id)->exists()
            || DB::table('test_attempts')->where('employee_id', $employee->id)->exists();
    }
}
