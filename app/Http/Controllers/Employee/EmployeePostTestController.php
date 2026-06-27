<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTrainingProgress;
use App\Models\TestAttempt;
use App\Models\Training;
use App\Services\PostTestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeePostTestController extends Controller
{
    public function show(Training $training, PostTestService $service, Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $isRetake = $request->boolean('retake');
        $result = $service->buildPostTestForUser($user, $training, $isRetake);

        if ($result['error'] !== null) {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', $result['error']);
        }

        if ($result['data'] === null) {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', 'Post-test tidak tersedia.');
        }

        if ($result['data']['hasEmptyState']) {
            return view('employee.training.post-test-empty', $result['data']);
        }

        return view('employee.training.post-test', $result['data']);
    }

    public function submit(Request $request, Training $training, PostTestService $service): RedirectResponse
    {
        $user = auth()->user();
        $isRetake = $request->boolean('retake');

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.selected_option_id' => 'nullable|integer',
            'answers.*.essay_answer' => 'nullable|string|max:5000',
        ]);

        $answers = collect($validated['answers'])
            ->map(fn (array $a): array => [
                'question_id' => (int) $a['question_id'],
                'selected_option_id' => isset($a['selected_option_id']) && $a['selected_option_id'] !== '' ? (int) $a['selected_option_id'] : null,
                'essay_answer' => $a['essay_answer'] ?? null,
            ])
            ->all();

        $success = $service->submitPostTest($user, $training, $answers, $isRetake);

        if (! $success) {
            return redirect()
                ->back()
                ->with('error', 'Post-test gagal disubmit. Pastikan semua soal terjawab dan syarat akses terpenuhi.');
        }

        return redirect()
            ->route('employee.training.show', $training)
            ->with('success', $isRetake ? 'Post-test berhasil diulang. Hasil akan tersedia setelah proses penilaian selesai.' : 'Post-test berhasil disubmit. Hasil akan tersedia setelah proses penilaian selesai.');
    }

    public function retake(Training $training): RedirectResponse
    {
        $user = auth()->user();
        $employee = $user?->employee()->first();

        if ($employee === null) {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', 'Anda tidak memiliki akses ke training ini.');
        }

        if (! $training->has_post_test) {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', 'Training ini tidak memiliki post-test.');
        }

        if (! $training->allow_post_test_retake) {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', 'Pengulangan post-test tidak diizinkan untuk training ini.');
        }

        $progress = EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->first();

        if ($progress === null) {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', 'Anda tidak ditugaskan ke training ini.');
        }

        if ($progress->final_status === 'passed') {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', 'Anda sudah lulus training ini dan tidak perlu mengulang post-test.');
        }

        $attemptCount = TestAttempt::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->where('test_type', 'post_test')
            ->count();

        if ($training->max_post_test_attempt !== null && $attemptCount >= $training->max_post_test_attempt) {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', 'Kesempatan mengulang post-test sudah habis.');
        }

        $hasPendingEssay = TestAttempt::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->where('test_type', 'post_test')
            ->where('grading_status', 'waiting_manual_review')
            ->exists();

        if ($hasPendingEssay) {
            return redirect()
                ->route('employee.training.show', $training)
                ->with('error', 'Masih ada attempt post-test yang menunggu penilaian essay. Silakan tunggu sebelum mengulang.');
        }

        return redirect()
            ->route('employee.post-test.show', $training);
    }
}
