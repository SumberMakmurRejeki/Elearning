<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Question\StoreQuestionRequest;
use App\Http\Requests\Admin\Question\UpdateQuestionRequest;
use App\Http\Requests\Admin\Question\UpdateQuestionStatusRequest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Training;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $trainingId = (string) $request->query('training_id', '');
        $testType = (string) $request->query('test_type', '');
        $questionType = (string) $request->query('question_type', '');
        $status = (string) $request->query('status', '');

        $questions = Question::query()
            ->with(['training', 'options'])
            ->when($query !== '', static fn ($builder) => $builder->where('question_text', 'like', '%'.$query.'%'))
            ->when($trainingId !== '', static fn ($builder) => $builder->where('training_id', (int) $trainingId))
            ->when($testType !== '', static fn ($builder) => $builder->where('test_type', $testType))
            ->when($questionType !== '', static fn ($builder) => $builder->where('question_type', $questionType))
            ->when($status === 'active', static fn ($builder) => $builder->where('is_active', true))
            ->when($status === 'inactive', static fn ($builder) => $builder->where('is_active', false))
            ->orderBy('training_id')
            ->orderBy('test_type')
            ->orderBy('order_number')
            ->paginate(10)
            ->withQueryString();

        return view('admin.soal.index', [
            'questions' => $questions,
            'query' => $query,
            'trainingId' => $trainingId,
            'testType' => $testType,
            'questionType' => $questionType,
            'status' => $status,
            'trainingOptions' => $this->trainingOptions(),
            'testTypeOptions' => $this->testTypeOptions(),
            'questionTypeOptions' => $this->questionTypeOptions(),
            'statusOptions' => $this->statusOptions(),
            'hasFilters' => $query !== '' || $trainingId !== '' || $testType !== '' || $questionType !== '' || $status !== '',
        ]);
    }

    public function create(): View
    {
        return view('admin.soal.create', [
            'question' => new Question([
                'test_type' => 'pre_test',
                'question_type' => 'multiple_choice',
                'is_active' => true,
            ]),
            'trainingOptions' => $this->trainingOptions(),
            'testTypeOptions' => $this->testTypeOptions(),
            'questionTypeOptions' => $this->questionTypeOptions(),
            'booleanOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.soal.index'),
        ]);
    }

    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request): void {
                $question = Question::create($this->payload($request));
                $this->syncOptions($question, $request->input('options', []));
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withInput()->with('error', 'Data soal gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.soal.index')
            ->with('success', 'Data soal berhasil disimpan.');
    }

    public function show(Question $question): View
    {
        $question->load(['training', 'options']);

        return view('admin.soal.show', [
            'question' => $question,
            'usedInTest' => $this->hasAnswers($question),
        ]);
    }

    public function edit(Question $question): View
    {
        $question->load(['training', 'options']);

        return view('admin.soal.edit', [
            'question' => $question,
            'trainingOptions' => $this->trainingOptions(),
            'testTypeOptions' => $this->testTypeOptions(),
            'questionTypeOptions' => $this->questionTypeOptions(),
            'booleanOptions' => $this->booleanOptions(),
            'backRoute' => route('admin.soal.index'),
            'usedInTest' => $this->hasAnswers($question),
        ]);
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        if ($this->hasAnswers($question)) {
            return redirect()
                ->route('admin.soal.show', $question)
                ->with('error', 'Soal tidak dapat diubah karena sudah digunakan dalam test.');
        }

        try {
            DB::transaction(function () use ($request, $question): void {
                $question->update($this->payload($request));
                $this->syncOptions($question, $request->input('options', []));
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withInput()->with('error', 'Perubahan data soal gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.soal.index')
            ->with('success', 'Perubahan data soal berhasil disimpan.');
    }

    public function updateStatus(UpdateQuestionStatusRequest $request, Question $question): RedirectResponse
    {
        $question->update(['is_active' => $request->boolean('is_active')]);

        return redirect()
            ->back()
            ->with('success', $request->boolean('is_active') ? 'Soal berhasil diaktifkan kembali.' : 'Soal berhasil dinonaktifkan.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        if ($this->hasAnswers($question)) {
            return redirect()
                ->back()
                ->with('error', 'Soal tidak dapat dihapus karena sudah digunakan dalam test. Silakan nonaktifkan soal saja.');
        }

        try {
            DB::transaction(function () use ($question): void {
                $question->options()->delete();
                $question->delete();
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()
                ->back()
                ->with('error', 'Data soal gagal dihapus. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.soal.index')
            ->with('success', 'Data soal berhasil dihapus permanen.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreQuestionRequest|UpdateQuestionRequest $request): array
    {
        $validated = $request->validated();

        return [
            'training_id' => (int) $validated['training_id'],
            'test_type' => $validated['test_type'],
            'question_type' => $validated['question_type'],
            'order_number' => (int) $validated['order_number'],
            'question_text' => $validated['question_text'],
            'weight' => $validated['weight'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rawOptions
     */
    private function syncOptions(Question $question, array $rawOptions): void
    {
        $question->options()->delete();

        if ($question->question_type !== 'multiple_choice') {
            return;
        }

        $options = collect($rawOptions)
            ->map(static fn ($option): array => [
                'option_text' => trim((string) ($option['option_text'] ?? '')),
                'is_correct' => (bool) ($option['is_correct'] ?? false),
            ])
            ->filter(static fn (array $option): bool => $option['option_text'] !== '')
            ->values();

        foreach ($options as $index => $option) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => chr(65 + $index),
                'option_text' => $option['option_text'],
                'is_correct' => $option['is_correct'],
            ]);
        }
    }

    private function hasAnswers(Question $question): bool
    {
        return DB::table('test_answers')->where('question_id', $question->id)->exists();
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
    private function testTypeOptions(): array
    {
        return [
            'pre_test' => 'Pre-Test',
            'post_test' => 'Post-Test',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function questionTypeOptions(): array
    {
        return [
            'multiple_choice' => 'Pilihan Ganda',
            'essay' => 'Essay',
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
}
