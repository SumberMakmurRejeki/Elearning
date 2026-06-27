<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Services\PreTestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePreTestController extends Controller
{
    public function show(Training $training, PreTestService $service): View|RedirectResponse
    {
        $user = auth()->user();
        $data = $service->buildPreTestForUser($user, $training);

        if ($data === null) {
            return redirect()
                ->route('employee.material.index', $training)
                ->with('info', 'Pre-test tidak tersedia atau sudah selesai.');
        }

        if ($data['hasEmptyState']) {
            return view('employee.training.pre-test-empty', $data);
        }

        return view('employee.training.pre-test', $data);
    }

    public function submit(Request $request, Training $training, PreTestService $service): RedirectResponse
    {
        $user = auth()->user();

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

        $success = $service->submitPreTest($user, $training, $answers);

        if (! $success) {
            return redirect()
                ->back()
                ->with('error', 'Pre-test gagal disubmit. Pastikan semua soal terjawab dan Anda belum pernah submit sebelumnya.');
        }

        return redirect()
            ->route('employee.material.index', $training)
            ->with('success', 'Pre-test berhasil disubmit. Materi training kini dapat Anda akses.');
    }
}
