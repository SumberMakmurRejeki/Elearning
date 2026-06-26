<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Position\StorePositionRequest;
use App\Http\Requests\Admin\Position\UpdatePositionRequest;
use App\Http\Requests\Admin\Position\UpdatePositionStatusRequest;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $positions = Position::query()
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

        return view('admin.jabatan.index', [
            'positions' => $positions,
            'query' => $query,
            'status' => $status,
            'statusOptions' => $this->statusOptions(),
            'hasFilters' => $query !== '' || $status !== '',
        ]);
    }

    public function create(): View
    {
        return view('admin.jabatan.create', [
            'position' => new Position(['is_active' => true]),
            'statusOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.jabatan.index'),
        ]);
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        Position::create($request->validated());

        return redirect()
            ->route('admin.jabatan.index')
            ->with('success', 'Data jabatan berhasil disimpan.');
    }

    public function show(Position $position): View
    {
        return view('admin.jabatan.show', [
            'position' => $position,
            'employeeCount' => (int) DB::table('employees')->where('position_id', $position->id)->count(),
        ]);
    }

    public function edit(Position $position): View
    {
        return view('admin.jabatan.edit', [
            'position' => $position,
            'statusOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.jabatan.index'),
        ]);
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return redirect()
            ->route('admin.jabatan.index')
            ->with('success', 'Perubahan data jabatan berhasil disimpan.');
    }

    public function updateStatus(UpdatePositionStatusRequest $request, Position $position): RedirectResponse
    {
        $position->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->back()
            ->with('success', $request->boolean('is_active') ? 'Jabatan berhasil diaktifkan kembali.' : 'Jabatan berhasil dinonaktifkan.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $usedByEmployees = DB::table('employees')->where('position_id', $position->id)->exists();

        if ($usedByEmployees) {
            return redirect()
                ->back()
                ->with('error', 'Jabatan tidak dapat dihapus karena masih digunakan oleh data karyawan. Sarankan admin untuk menonaktifkan jabatan saja.');
        }

        $position->delete();

        return redirect()
            ->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil dihapus permanen.');
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
