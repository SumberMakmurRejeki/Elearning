<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Division\StoreDivisionRequest;
use App\Http\Requests\Admin\Division\UpdateDivisionRequest;
use App\Http\Requests\Admin\Division\UpdateDivisionStatusRequest;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $divisions = Division::query()
            ->when($query !== '', static function ($builder) use ($query): void {
                $builder->where(static function ($search) use ($query): void {
                    $search->where('name', 'like', '%'.$query.'%')
                        ->orWhere('description', 'like', '%'.$query.'%');
                });
            })
            ->when($status === 'active', static fn ($builder) => $builder->where('is_active', true))
            ->when($status === 'inactive', static fn ($builder) => $builder->where('is_active', false))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.divisi.index', [
            'divisions' => $divisions,
            'query' => $query,
            'status' => $status,
            'statusOptions' => $this->statusOptions(),
            'hasFilters' => $query !== '' || $status !== '',
        ]);
    }

    public function create(): View
    {
        return view('admin.divisi.create', [
            'division' => new Division(['is_active' => true]),
            'statusOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.divisi.index'),
        ]);
    }

    public function store(StoreDivisionRequest $request): RedirectResponse
    {
        Division::create($request->validated());

        return redirect()
            ->route('admin.divisi.index')
            ->with('success', 'Data divisi berhasil disimpan.');
    }

    public function show(Division $division): View
    {
        return view('admin.divisi.show', [
            'division' => $division,
            'employeeCount' => (int) DB::table('employees')->where('division_id', $division->id)->count(),
        ]);
    }

    public function edit(Division $division): View
    {
        return view('admin.divisi.edit', [
            'division' => $division,
            'statusOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.divisi.index'),
        ]);
    }

    public function update(UpdateDivisionRequest $request, Division $division): RedirectResponse
    {
        $division->update($request->validated());

        return redirect()
            ->route('admin.divisi.index')
            ->with('success', 'Perubahan data divisi berhasil disimpan.');
    }

    public function updateStatus(UpdateDivisionStatusRequest $request, Division $division): RedirectResponse
    {
        $division->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->back()
            ->with('success', $request->boolean('is_active') ? 'Divisi berhasil diaktifkan kembali.' : 'Divisi berhasil dinonaktifkan.');
    }

    public function destroy(Division $division): RedirectResponse
    {
        $usedByEmployees = DB::table('employees')->where('division_id', $division->id)->exists();

        if ($usedByEmployees) {
            return redirect()
                ->back()
                ->with('error', 'Divisi tidak dapat dihapus karena masih digunakan oleh data karyawan. Sarankan admin untuk menonaktifkan divisi saja.');
        }

        $division->delete();

        return redirect()
            ->route('admin.divisi.index')
            ->with('success', 'Divisi berhasil dihapus permanen.');
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
    private function booleanOptions(): array
    {
        return [
            '1' => 'Aktif',
            '0' => 'Nonaktif',
        ];
    }
}
