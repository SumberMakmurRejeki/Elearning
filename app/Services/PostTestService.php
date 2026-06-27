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

class PostTestService
{
    public function __construct(
        private readonly TestScoringService $scoringService,
    ) {}

    /**
     * @return array{data:array<string,mixed>|null,error:string|null}
     */
    public function buildPostTestForUser(User $user, Training $training, bool $isRetake = false): array
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return ['data' => null, 'error' => 'Anda tidak memiliki akses ke training ini.'];
        }

        if (! $this->employeeHasTraining($employee, $training)) {
            return ['data' => null, 'error' => 'Anda tidak memiliki akses ke training ini.'];
        }

        if (! $training->has_post_test) {
            return ['data' => null, 'error' => 'Training ini tidak memiliki post-test.'];
        }

        if (! $isRetake && $this->postTestAlreadyCompleted($employee, $training)) {
            return ['data' => null, 'error' => 'Post-test sudah selesai dikerjakan.'];
        }

        if ($isRetake && $this->postTestIsPassed($employee, $training)) {
            return ['data' => null, 'error' => 'Anda sudah lulus training ini dan tidak perlu mengulang post-test.'];
        }

        if ($training->has_pre_test && ! $this->preTestIsCompleted($employee, $training)) {
            return ['data' => null, 'error' => 'Selesaikan pre-test terlebih dahulu sebelum mengerjakan post-test.'];
        }

        if (! $this->allMaterialsOpened($employee, $training)) {
            return ['data' => null, 'error' => 'Post-test belum dapat dikerjakan karena materi belum selesai dibuka.'];
        }

        $questions = Question::query()
            ->with(['options' => fn ($q) => $q->orderBy('option_label')])
            ->where('training_id', $training->id)
            ->where('test_type', 'post_test')
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN order_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('order_number')
            ->orderByDesc('created_at')
            ->get();

        return [
            'data' => [
                'employee' => $employee->loadMissing('user'),
                'training' => $training,
                'questions' => $questions,
                'hasEmptyState' => $questions->isEmpty(),
            ],
            'error' => null,
        ];
    }

    /**
     * @param array<int,array{question_id:int,selected_option_id:int|null,essay_answer:string|null}> $answers
     * @return bool
     */
    public function submitPostTest(User $user, Training $training, array $answers, bool $isRetake = false): bool
    {
        $employee = $user->employee()->first();

        if ($employee === null) {
            return false;
        }

        if (! $this->employeeHasTraining($employee, $training)) {
            return false;
        }

        if (! $training->has_post_test) {
            return false;
        }

        if (! $isRetake && $this->postTestAlreadyCompleted($employee, $training)) {
            return false;
        }

        if ($isRetake) {
            if ($this->postTestIsPassed($employee, $training)) {
                return false;
            }

            if (! $training->allow_post_test_retake) {
                return false;
            }

            $attemptCount = TestAttempt::query()
                ->where('employee_id', $employee->id)
                ->where('training_id', $training->id)
                ->where('test_type', 'post_test')
                ->count();

            if ($training->max_post_test_attempt !== null && $attemptCount >= $training->max_post_test_attempt) {
                return false;
            }

            $hasPendingEssay = TestAttempt::query()
                ->where('employee_id', $employee->id)
                ->where('training_id', $training->id)
                ->where('test_type', 'post_test')
                ->where('grading_status', 'waiting_manual_review')
                ->exists();

            if ($hasPendingEssay) {
                return false;
            }
        }

        if ($training->has_pre_test && ! $this->preTestIsCompleted($employee, $training)) {
            return false;
        }

        if (! $this->allMaterialsOpened($employee, $training)) {
            return false;
        }

        $activeQuestions = Question::query()
            ->where('training_id', $training->id)
            ->where('test_type', 'post_test')
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
            DB::transaction(function () use ($employee, $training, $activeQuestions, $answers, $isRetake): void {
                $attemptNumber = TestAttempt::query()
                    ->where('employee_id', $employee->id)
                    ->where('training_id', $training->id)
                    ->where('test_type', 'post_test')
                    ->count() + 1;

                $now = Carbon::now();

                $attempt = TestAttempt::create([
                    'employee_id' => $employee->id,
                    'training_id' => $training->id,
                    'test_type' => 'post_test',
                    'attempt_number' => $attemptNumber,
                    'status' => 'submitted',
                    'started_at' => $now,
                    'submitted_at' => $now,
                ]);

                $scoring = $this->scoringService->scoreAttempt($attempt, $activeQuestions, $answers);

                $hasEssay = $scoring['hasEssay'];
                $finalScore = $scoring['finalScore'];

                $passStatus = null;

                if (! $hasEssay && $finalScore !== null) {
                    $passStatus = $finalScore >= (float) ($training->passing_grade ?? 0) ? 'passed' : 'failed';

                    $attempt->update([
                        'pass_status' => $passStatus,
                        'status' => 'completed',
                    ]);
                }

                if ($isRetake) {
                    $bestScore = TestAttempt::query()
                        ->where('employee_id', $employee->id)
                        ->where('training_id', $training->id)
                        ->where('test_type', 'post_test')
                        ->whereNotNull('final_score')
                        ->max('final_score');

                    $bestIsPassed = $bestScore !== null && $bestScore >= (float) ($training->passing_grade ?? 0);

                    EmployeeTrainingProgress::query()
                        ->where('employee_id', $employee->id)
                        ->where('training_id', $training->id)
                        ->update([
                            'post_test_completed_at' => $now,
                            'status' => $hasEssay ? 'waiting_essay_review' : ($bestIsPassed ? 'passed' : 'failed'),
                            'final_score' => $hasEssay ? null : $bestScore,
                            'final_status' => $hasEssay ? null : ($bestIsPassed ? 'passed' : 'failed'),
                            'completed_at' => $bestIsPassed ? $now : null,
                        ]);
                } else {
                    $progressStatus = $hasEssay ? 'waiting_essay_review' : 'post_test_completed';

                    EmployeeTrainingProgress::query()
                        ->where('employee_id', $employee->id)
                        ->where('training_id', $training->id)
                        ->update([
                            'post_test_completed_at' => $now,
                            'status' => $hasEssay ? 'waiting_essay_review' : $passStatus,
                            'final_score' => $hasEssay ? null : $finalScore,
                            'final_status' => $hasEssay ? null : $passStatus,
                            'completed_at' => $hasEssay ? null : $now,
                        ]);
                }
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

    private function postTestAlreadyCompleted(Employee $employee, Training $training): bool
    {
        return EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->whereNotNull('post_test_completed_at')
            ->exists();
    }

    private function postTestIsPassed(Employee $employee, Training $training): bool
    {
        return EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->where('final_status', 'passed')
            ->exists();
    }

    private function preTestIsCompleted(Employee $employee, Training $training): bool
    {
        return EmployeeTrainingProgress::query()
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->whereNotNull('pre_test_completed_at')
            ->exists();
    }

    private function allMaterialsOpened(Employee $employee, Training $training): bool
    {
        $activeCount = (int) DB::table('training_materials')
            ->where('training_id', $training->id)
            ->where('is_active', true)
            ->count();

        if ($activeCount === 0) {
            return true;
        }

        $openedCount = (int) DB::table('material_access_logs')
            ->where('employee_id', $employee->id)
            ->where('training_id', $training->id)
            ->whereIn('material_id', function ($query) use ($training): void {
                $query->select('id')
                    ->from('training_materials')
                    ->where('training_id', $training->id)
                    ->where('is_active', true);
            })
            ->count();

        return $openedCount >= $activeCount;
    }
}
