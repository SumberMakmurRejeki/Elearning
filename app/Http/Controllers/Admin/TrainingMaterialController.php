<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Material\StoreTrainingMaterialRequest;
use App\Http\Requests\Admin\Material\UpdateTrainingMaterialRequest;
use App\Http\Requests\Admin\Material\UpdateTrainingMaterialStatusRequest;
use App\Models\Training;
use App\Models\TrainingMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrainingMaterialController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $trainingId = (string) $request->query('training_id', '');
        $materialType = (string) $request->query('material_type', '');
        $status = (string) $request->query('status', '');

        $materials = TrainingMaterial::query()
            ->with('training')
            ->when($query !== '', static function ($builder) use ($query): void {
                $builder->where(static function ($search) use ($query): void {
                    $search->where('title', 'like', '%'.$query.'%')
                        ->orWhere('description', 'like', '%'.$query.'%');
                });
            })
            ->when($trainingId !== '', static fn ($builder) => $builder->where('training_id', (int) $trainingId))
            ->when($materialType !== '', static fn ($builder) => $builder->where('material_type', $materialType))
            ->when($status === 'active', static fn ($builder) => $builder->where('is_active', true))
            ->when($status === 'inactive', static fn ($builder) => $builder->where('is_active', false))
            ->orderByRaw('CASE WHEN order_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('order_number')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.materi.index', [
            'materials' => $materials,
            'query' => $query,
            'trainingId' => $trainingId,
            'materialType' => $materialType,
            'status' => $status,
            'trainingOptions' => $this->trainingOptions(),
            'materialTypeOptions' => $this->materialTypeOptions(),
            'statusOptions' => $this->statusOptions(),
            'hasFilters' => $query !== '' || $trainingId !== '' || $materialType !== '' || $status !== '',
        ]);
    }

    public function create(): View
    {
        return view('admin.materi.create', [
            'material' => new TrainingMaterial([
                'material_type' => 'file',
                'is_active' => true,
            ]),
            'trainingOptions' => $this->trainingOptions(),
            'materialTypeOptions' => $this->materialTypeOptions(),
            'booleanOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.materi.index'),
        ]);
    }

    public function store(StoreTrainingMaterialRequest $request): RedirectResponse
    {
        $storedPath = null;

        try {
            $payload = $this->payload($request);

            if ($request->input('material_type') === 'file' && $request->file('file') !== null) {
                $storedPath = $this->storeUploadedFile((int) $payload['training_id'], $request->file('file'));
                $payload['file_path'] = $storedPath;
                $payload['file_type'] = strtolower((string) $request->file('file')->getClientOriginalExtension());
                $payload['file_size'] = $request->file('file')->getSize();
                $payload['url'] = null;
            }

            TrainingMaterial::create($payload);
        } catch (Throwable $throwable) {
            if ($storedPath !== null) {
                Storage::disk('local')->delete($storedPath);
            }

            report($throwable);

            return back()->withInput()->with('error', 'Data materi gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.materi.index')
            ->with('success', 'Data materi berhasil disimpan.');
    }

    public function show(TrainingMaterial $trainingMaterial): View
    {
        $trainingMaterial->load('training');

        return view('admin.materi.show', [
            'material' => $trainingMaterial,
            'accessCount' => (int) DB::table('material_access_logs')->where('material_id', $trainingMaterial->id)->count(),
            'sourceLabel' => $this->sourceLabel($trainingMaterial),
            'fileSizeLabel' => $this->fileSizeLabel($trainingMaterial->file_size),
        ]);
    }

    public function edit(TrainingMaterial $trainingMaterial): View
    {
        $trainingMaterial->load('training');

        return view('admin.materi.edit', [
            'material' => $trainingMaterial,
            'trainingOptions' => $this->trainingOptions(),
            'materialTypeOptions' => $this->materialTypeOptions(),
            'booleanOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.materi.index'),
        ]);
    }

    public function update(UpdateTrainingMaterialRequest $request, TrainingMaterial $trainingMaterial): RedirectResponse
    {
        $oldFilePath = $trainingMaterial->file_path;
        $newFilePath = null;

        try {
            $payload = $this->payload($request, $trainingMaterial);

            if ($request->input('material_type') === 'file' && $request->file('file') !== null) {
                $newFilePath = $this->storeUploadedFile((int) $payload['training_id'], $request->file('file'));
                $payload['file_path'] = $newFilePath;
                $payload['file_type'] = strtolower((string) $request->file('file')->getClientOriginalExtension());
                $payload['file_size'] = $request->file('file')->getSize();
                $payload['url'] = null;
            }

            $trainingMaterial->update($payload);

            if ($oldFilePath !== null && $oldFilePath !== $trainingMaterial->file_path) {
                Storage::disk('local')->delete($oldFilePath);
            }
        } catch (Throwable $throwable) {
            if ($newFilePath !== null) {
                Storage::disk('local')->delete($newFilePath);
            }

            report($throwable);

            return back()->withInput()->with('error', 'Perubahan data materi gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.materi.index')
            ->with('success', 'Perubahan data materi berhasil disimpan.');
    }

    public function updateStatus(UpdateTrainingMaterialStatusRequest $request, TrainingMaterial $trainingMaterial): RedirectResponse
    {
        $isActive = $request->boolean('is_active');

        $trainingMaterial->update(['is_active' => $isActive]);

        return redirect()
            ->back()
            ->with('success', $isActive ? 'Materi berhasil diaktifkan kembali.' : 'Materi berhasil dinonaktifkan.');
    }

    public function destroy(TrainingMaterial $trainingMaterial): RedirectResponse
    {
        if ($this->hasAccessLogs($trainingMaterial)) {
            return redirect()
                ->back()
                ->with('error', 'Materi tidak dapat dihapus karena sudah pernah diakses oleh karyawan. Silakan nonaktifkan materi saja.');
        }

        try {
            $filePath = $trainingMaterial->file_path;

            $trainingMaterial->delete();

            if ($filePath !== null) {
                Storage::disk('local')->delete($filePath);
            }
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()
                ->back()
                ->with('error', 'Data materi gagal dihapus. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.materi.index')
            ->with('success', 'Data materi berhasil dihapus permanen.');
    }

    public function previewFile(TrainingMaterial $trainingMaterial): Response|RedirectResponse
    {
        if ($redirect = $this->guardFileAccess($trainingMaterial)) {
            return $redirect;
        }

        return Storage::disk('local')->response(
            $trainingMaterial->file_path,
            basename((string) $trainingMaterial->file_path),
            ['Content-Type' => Storage::disk('local')->mimeType((string) $trainingMaterial->file_path) ?: 'application/octet-stream']
        );
    }

    public function downloadFile(TrainingMaterial $trainingMaterial): Response|RedirectResponse
    {
        if ($redirect = $this->guardFileAccess($trainingMaterial)) {
            return $redirect;
        }

        return Storage::disk('local')->download(
            $trainingMaterial->file_path,
            basename((string) $trainingMaterial->file_path)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreTrainingMaterialRequest|UpdateTrainingMaterialRequest $request, ?TrainingMaterial $trainingMaterial = null): array
    {
        $validated = $request->validated();
        $materialType = $validated['material_type'];

        $payload = [
            'training_id' => (int) $validated['training_id'],
            'title' => $validated['title'],
            'description' => ($validated['description'] ?? null) ?: null,
            'material_type' => $materialType,
            'order_number' => $validated['order_number'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($materialType === 'link') {
            $payload['url'] = ($validated['url'] ?? $trainingMaterial?->url) ?: null;
            $payload['file_path'] = null;
            $payload['file_type'] = null;
            $payload['file_size'] = null;

            return $payload;
        }

        $payload['url'] = null;
        $payload['file_path'] = $trainingMaterial?->file_path;
        $payload['file_type'] = $trainingMaterial?->file_type;
        $payload['file_size'] = $trainingMaterial?->file_size;

        return $payload;
    }

    private function storeUploadedFile(int $trainingId, \Illuminate\Http\UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $fileName = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs('training-materials/'.$trainingId, $fileName, 'local');
    }

    private function hasAccessLogs(TrainingMaterial $trainingMaterial): bool
    {
        return DB::table('material_access_logs')->where('material_id', $trainingMaterial->id)->exists();
    }

    private function guardFileAccess(TrainingMaterial $trainingMaterial): ?RedirectResponse
    {
        if ($trainingMaterial->material_type !== 'file' || $trainingMaterial->file_path === null) {
            return redirect()
                ->route('admin.materi.show', $trainingMaterial)
                ->with('error', 'File materi tidak tersedia untuk diakses.');
        }

        if (! Storage::disk('local')->exists($trainingMaterial->file_path)) {
            return redirect()
                ->route('admin.materi.show', $trainingMaterial)
                ->with('error', 'File materi tidak ditemukan atau sudah tidak tersedia.');
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function trainingOptions(): array
    {
        return Training::query()->orderBy('title')->pluck('title', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private function materialTypeOptions(): array
    {
        return [
            'file' => 'File Upload',
            'link' => 'Link Eksternal',
        ];
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

    private function sourceLabel(TrainingMaterial $trainingMaterial): string
    {
        if ($trainingMaterial->material_type === 'link') {
            return $trainingMaterial->url ?: '-';
        }

        return basename((string) $trainingMaterial->file_path);
    }

    private function fileSizeLabel(?int $fileSize): string
    {
        if ($fileSize === null) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $fileSize;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, $unitIndex === 0 ? 0 : 2).' '.$units[$unitIndex];
    }
}
