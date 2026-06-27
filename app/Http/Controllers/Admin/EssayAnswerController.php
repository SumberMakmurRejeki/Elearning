<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TestAnswer;
use App\Models\Training;
use App\Services\EssayScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EssayAnswerController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $trainingId = (string) $request->query('training_id', '');
        $employeeId = (string) $request->query('employee_id', '');
        $testType = (string) $request->query('test_type', '');
        $status = (string) $request->query('status', '');

        try {
            $answers = TestAnswer::query()
                ->select('test_answers.*')
                ->join('questions', 'questions.id', '=', 'test_answers.question_id')
                ->join('test_attempts', 'test_attempts.id', '=', 'test_answers.attempt_id')
                ->join('employees', 'employees.id', '=', 'test_attempts.employee_id')
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->with(['attempt.training', 'attempt.employee.user', 'question'])
                ->where('questions.question_type', 'essay')
                ->when($query !== '', static function ($builder) use ($query): void {
                    $builder->where(static function ($search) use ($query): void {
                        $search->where('users.name', 'like', '%'.$query.'%')
                            ->orWhereIn('test_attempts.training_id', Training::query()->select('id')->where('title', 'like', '%'.$query.'%'));
                    });
                })
                ->when($trainingId !== '', static fn ($builder) => $builder->where('test_attempts.training_id', (int) $trainingId))
                ->when($employeeId !== '', static fn ($builder) => $builder->where('test_attempts.employee_id', (int) $employeeId))
                ->when($testType !== '', static fn ($builder) => $builder->where('test_attempts.test_type', $testType))
                ->when($status === 'waiting_manual_review', static fn ($builder) => $builder->whereNull('test_answers.graded_at'))
                ->when($status === 'manual_reviewed', static fn ($builder) => $builder->whereNotNull('test_answers.graded_at'))
                ->orderByDesc('test_attempts.submitted_at')
                ->paginate(10)
                ->withQueryString();

            return view('admin.essay-answers.index', [
                'answers' => $answers,
                'query' => $query,
                'trainingId' => $trainingId,
                'employeeId' => $employeeId,
                'testType' => $testType,
                'status' => $status,
                'trainingOptions' => Training::query()->orderBy('title')->pluck('title', 'id')->all(),
                'employeeOptions' => Employee::query()->join('users', 'users.id', '=', 'employees.user_id')->orderBy('users.name')->pluck('users.name', 'employees.id')->all(),
                'testTypeOptions' => ['pre_test' => 'Pre-Test', 'post_test' => 'Post-Test'],
                'statusOptions' => ['waiting_manual_review' => 'Menunggu Penilaian', 'manual_reviewed' => 'Sudah Dinilai'],
                'hasFilters' => $query !== '' || $trainingId !== '' || $employeeId !== '' || $testType !== '' || $status !== '',
                'hasError' => false,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return view('admin.essay-answers.index', [
                'answers' => collect(),
                'query' => $query,
                'trainingId' => $trainingId,
                'employeeId' => $employeeId,
                'testType' => $testType,
                'status' => $status,
                'trainingOptions' => [],
                'employeeOptions' => [],
                'testTypeOptions' => ['pre_test' => 'Pre-Test', 'post_test' => 'Post-Test'],
                'statusOptions' => ['waiting_manual_review' => 'Menunggu Penilaian', 'manual_reviewed' => 'Sudah Dinilai'],
                'hasFilters' => false,
                'hasError' => true,
            ]);
        }
    }

    public function show(TestAnswer $answer): View
    {
        abort_if($answer->question?->question_type !== 'essay', 404);

        $answer->load(['attempt.training', 'attempt.employee.user', 'question']);

        return view('admin.essay-answers.show', [
            'answer' => $answer,
            'maxScore' => (float) $answer->question?->weight,
        ]);
    }

    public function score(Request $request, TestAnswer $answer, EssayScoringService $service): RedirectResponse
    {
        abort_if($answer->question?->question_type !== 'essay', 404);

        $answer->loadMissing(['attempt', 'question']);

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$answer->question->weight],
        ]);

        $success = $service->scoreAnswer($answer, (float) $validated['score'], $request->user());

        if (! $success) {
            return back()->withInput()->with('error', 'Nilai essay gagal disimpan. Pastikan attempt masih menunggu penilaian dan skor sesuai bobot soal.');
        }

        return redirect()
            ->route('admin.essay-answers.show', $answer)
            ->with('success', 'Nilai essay berhasil disimpan.');
    }
}
