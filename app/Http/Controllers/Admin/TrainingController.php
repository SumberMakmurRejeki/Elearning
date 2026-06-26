<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Training\StoreTrainingRequest;
use App\Http\Requests\Admin\Training\UpdateTrainingRequest;
use App\Http\Requests\Admin\Training\UpdateTrainingStatusRequest;
use App\Models\Training;
use App\Services\AdminDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $month = (string) $request->query('month', '');
        $year = (string) $request->query('year', '');

        $trainings = Training::query()
            ->when($query !== '', static function ($builder) use ($query): void {
                $builder->where(static function ($search) use ($query): void {
                    $search->where('title', 'like', '%'.$query.'%')
                        ->orWhere('description', 'like', '%'.$query.'%');
                });
            })
            ->when($status !== '', static fn ($builder) => $builder->where('status', $status))
            ->when($month !== '', static fn ($builder) => $builder->whereMonth('start_date', (int) $month))
            ->when($year !== '', static fn ($builder) => $builder->whereYear('start_date', (int) $year))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.training.index', [
            'trainings' => $trainings,
            'query' => $query,
            'status' => $status,
            'month' => $month,
            'year' => $year,
            'statusOptions' => $this->statusOptions(),
            'monthOptions' => $this->monthOptions(),
            'yearOptions' => $this->yearOptions(),
            'hasFilters' => $query !== '' || $status !== '' || $month !== '' || $year !== '',
        ]);
    }

    public function create(): View
    {
        return view('admin.training.create', [
            'training' => new Training([
                'status' => 'draft',
                'has_pre_test' => false,
                'has_post_test' => false,
                'allow_post_test_retake' => false,
                'show_score_to_employee' => false,
            ]),
            'booleanOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.training.index'),
        ]);
    }

    public function store(StoreTrainingRequest $request): RedirectResponse
    {
        Training::create($this->payload($request) + [
            'status' => 'draft',
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.training.index')
            ->with('success', 'Data training berhasil disimpan sebagai draft.');
    }

    public function show(Training $training): View
    {
        return view('admin.training.show', [
            'training' => $training,
            'materialCount' => (int) DB::table('training_materials')->where('training_id', $training->id)->count(),
            'questionCount' => (int) DB::table('questions')->where('training_id', $training->id)->count(),
            'assignmentCount' => (int) DB::table('training_assignments')->where('training_id', $training->id)->count(),
            'progressCount' => (int) DB::table('employee_training_progress')->where('training_id', $training->id)->count(),
            'attemptCount' => (int) DB::table('test_attempts')->where('training_id', $training->id)->count(),
            'hasDependencies' => $this->hasDependencies($training),
        ]);
    }

    public function edit(Training $training): View
    {
        return view('admin.training.edit', [
            'training' => $training,
            'booleanOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.training.index'),
        ]);
    }

    public function update(UpdateTrainingRequest $request, Training $training): RedirectResponse
    {
        $training->update($this->payload($request));

        return redirect()
            ->route('admin.training.index')
            ->with('success', 'Perubahan data training berhasil disimpan.');
    }

    public function updateStatus(UpdateTrainingStatusRequest $request, Training $training): RedirectResponse
    {
        $status = $request->validated()['status'];

        if (! $this->canTransitionTo($training, $status)) {
            return redirect()
                ->back()
                ->with('error', 'Status training tidak valid untuk kondisi training saat ini.');
        }

        if ($status === 'published') {
            if ($error = $this->publishReadinessError($training)) {
                return redirect()
                    ->back()
                    ->with('error', $error);
            }
        }

        $training->update(['status' => $status]);

        return redirect()
            ->back()
            ->with('success', $status === 'published'
                ? 'Training berhasil dipublish.'
                : 'Training berhasil diarsipkan.');
    }

    public function destroy(Training $training): RedirectResponse
    {
        if ($this->hasDependencies($training)) {
            return redirect()
                ->back()
                ->with('error', 'Training tidak dapat dihapus karena sudah memiliki materi, soal, penugasan, progress, atau hasil test terkait.');
        }

        $training->delete();

        return redirect()
            ->route('admin.training.index')
            ->with('success', 'Training berhasil dihapus permanen.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreTrainingRequest|UpdateTrainingRequest $request): array
    {
        $validated = $request->validated();

        $payload = [
            'title' => $validated['title'],
            'description' => ($validated['description'] ?? null) ?: null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'has_pre_test' => $request->boolean('has_pre_test'),
            'has_post_test' => $request->boolean('has_post_test'),
            'passing_grade' => $validated['passing_grade'] ?? null,
            'allow_post_test_retake' => $request->boolean('allow_post_test_retake'),
            'max_post_test_attempt' => $validated['max_post_test_attempt'] ?? null,
            'show_score_to_employee' => $request->boolean('show_score_to_employee'),
        ];

        if (! $payload['has_post_test']) {
            $payload['passing_grade'] = null;
            $payload['allow_post_test_retake'] = false;
            $payload['max_post_test_attempt'] = null;
        }

        if (! $payload['allow_post_test_retake']) {
            $payload['max_post_test_attempt'] = null;
        }

        return $payload;
    }

    private function canTransitionTo(Training $training, string $targetStatus): bool
    {
        return match ($training->status) {
            'draft' => $targetStatus === 'published',
            'published' => $targetStatus === 'archived',
            default => false,
        };
    }

    private function publishReadinessError(Training $training): ?string
    {
        $hasActiveMaterials = DB::table('training_materials')
            ->where('training_id', $training->id)
            ->where('is_active', true)
            ->exists();

        if (! $hasActiveMaterials) {
            return 'Training belum dapat dipublish karena materi aktif belum tersedia.';
        }

        if ($training->has_pre_test && ! $this->hasActiveQuestions($training->id, 'pre_test')) {
            return 'Training belum dapat dipublish karena soal pre-test aktif belum tersedia.';
        }

        if ($training->has_post_test && ! $this->hasActiveQuestions($training->id, 'post_test')) {
            return 'Training belum dapat dipublish karena soal post-test aktif belum tersedia.';
        }

        return null;
    }

    private function hasActiveQuestions(int $trainingId, string $testType): bool
    {
        return DB::table('questions')
            ->where('training_id', $trainingId)
            ->where('test_type', $testType)
            ->where('is_active', true)
            ->exists();
    }

    private function hasDependencies(Training $training): bool
    {
        return DB::table('training_materials')->where('training_id', $training->id)->exists()
            || DB::table('training_assignments')->where('training_id', $training->id)->exists()
            || DB::table('employee_training_progress')->where('training_id', $training->id)->exists()
            || DB::table('material_access_logs')->where('training_id', $training->id)->exists()
            || DB::table('questions')->where('training_id', $training->id)->exists()
            || DB::table('question_options')->whereIn('question_id', function ($query) use ($training): void {
                $query->select('id')->from('questions')->where('training_id', $training->id);
            })->exists()
            || DB::table('test_attempts')->where('training_id', $training->id)->exists()
            || DB::table('test_answers')->whereIn('attempt_id', function ($query) use ($training): void {
                $query->select('id')->from('test_attempts')->where('training_id', $training->id);
            })->exists();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return [
            '' => 'Semua',
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function booleanOptions(): array
    {
        return [
            '1' => 'Ya',
            '0' => 'Tidak',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function monthOptions(): array
    {
        return AdminDashboardService::monthOptions();
    }

    /**
     * @return array<string, string>
     */
    private function yearOptions(): array
    {
        $years = Training::query()
            ->pluck('start_date')
            ->filter()
            ->map(static fn ($startDate): string => (string) date('Y', strtotime((string) $startDate)))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if ($years === []) {
            $currentYear = (string) now()->year;

            return [$currentYear => $currentYear];
        }

        return array_combine($years, $years) ?: [];
    }
}
