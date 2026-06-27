<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTrainingProgress;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class PreTestService
{
    public function __construct(
        private readonly TestScoringService $scoringService,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function buildPreTestForUser(User $user, Training $training): ?array
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return null;
        }

        if (! $this->employeeHasTraining($employee, $training)) {
            return null;
        }

        if (! $training->has_pre_test) {
            return null;
        }

        if ($this->preTestAlreadyCompleted($employee, $training)) {
            return null;
        }

        $questions = Question::query()
            ->with(['options' => fn ($q) => $q->orderBy('option_label')])
            ->where('training_id', $training->id)
            ->where('test_type', 'pre_test')
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN order_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('order_number')
            ->orderByDesc('created_at')
            ->get();

        return [
            'employee' => $employee->loadMissing('user'),
            'training' => $training,
            'questions' => $questions,
            'hasEmptyState' => $questions->isEmpty(),
        ];
    }

    /**
     * @param array<int, array{question_id:int,selected_option_id:int|null,essay_answer:string|null}> $answers
     */
    public function submitPreTest(User $user, Training $training, array $answers): bool
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return false;
        }

        if (! $this->employeeHasTraining($employee, $training)) {
            return false;
        }

        if (! $training->has_pre_test) {
            return false;
        }

        if ($this->preTestAlreadyCompleted($employee, $training)) {
            return false;
        }

        $activeQuestions = Question::query()
            ->where('training_id', $training->id)
            ->where('test_type', 'pre_test')
            ->where('is_active', true)
            ->get();

        if ($activeQuestions->isEmpty()) {
            return false;
        }

        $submittedAnswerMap = collect($answers)->pluck('question_id')->flip();

        foreach ($activeQuestions as $question) {
            if (! $submittedAnswerMap->has($question->id)) {
                return false;
            }
        }

        try {
            DB::transaction(function () use ($employee, $training, $activeQuestions, $answers): void {
                $progress = EmployeeTrainingProgress::query()
                    ->where('employee_id', $employee->id)
                    ->where('training_id', $training->id)
                    ->lockForUpdate()
                    ->first();

                if ($progress === null || $progress->pre_test_completed_at !== null) {
                    throw new \RuntimeException('pre-test already completed');
                }

                $attemptNumber = TestAttempt::query()
                    ->where('employee_id', $employee->id)
                    ->where('training_id', $training->id)
                    ->where('test_type', 'pre_test')
                    ->count() + 1;

                $now = Carbon::now();

                $attempt = TestAttempt::create([
                    'employee_id' => $employee->id,
                    'training_id' => $training->id,
                    'test_type' => 'pre_test',
                    'attempt_number' => $attemptNumber,
                    'status' => 'submitted',
                    'started_at' => $now,
                    'submitted_at' => $now,
                ]);

                $this->scoringService->scoreAttempt($attempt, $activeQuestions, $answers);

                EmployeeTrainingProgress::query()
                    ->where('employee_id', $employee->id)
                    ->where('training_id', $training->id)
                    ->whereNull('pre_test_completed_at')
                    ->update([
                        'pre_test_completed_at' => $now,
                        'status' => DB::raw("CASE WHEN status = 'not_started' THEN 'pre_test_completed' WHEN status = 'in_progress' THEN 'pre_test_completed' ELSE status END"),
                    ]);
            });

            return true;
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    private function employeeHasTraining(Employee $employee, Training $training): bool
    {
        return EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->exists();
    }

    private function preTestAlreadyCompleted(Employee $employee, Training $training): bool
    {
        return EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->whereNotNull('pre_test_completed_at')
            ->exists();
    }
}
