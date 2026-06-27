<?php

namespace App\Services;

use App\Models\TestAnswer;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class EssayScoringService
{
    public function scoreAnswer(TestAnswer $answer, float $score, User $grader): bool
    {
        try {
            DB::transaction(function () use ($answer, $score, $grader): void {
                $lockedAnswer = TestAnswer::query()
                    ->with(['question', 'attempt.training'])
                    ->whereKey($answer->id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedAnswer === null || $lockedAnswer->question?->question_type !== 'essay') {
                    throw new RuntimeException('essay answer not found');
                }

                $attempt = $lockedAnswer->attempt;

                if ($attempt === null || $attempt->grading_status !== 'waiting_manual_review') {
                    throw new RuntimeException('attempt does not require manual review');
                }

                $maxScore = (float) $lockedAnswer->question->weight;

                if ($score < 0 || $score > $maxScore) {
                    throw new RuntimeException('score out of range');
                }

                $lockedAnswer->update([
                    'score' => $score,
                    'graded_by' => $grader->id,
                    'graded_at' => Carbon::now(),
                ]);

                $attempt->refresh();
                $attempt->loadMissing(['training', 'answers.question']);

                $essayAnswers = $attempt->answers->filter(
                    fn (TestAnswer $testAnswer): bool => $testAnswer->question?->question_type === 'essay'
                );

                $essayScore = round((float) $essayAnswers->sum('score'), 2);
                $hasPendingEssay = $essayAnswers->contains(fn (TestAnswer $testAnswer): bool => $testAnswer->graded_at === null);

                if ($hasPendingEssay) {
                    $attempt->update([
                        'essay_score' => $essayScore,
                        'grading_status' => 'waiting_manual_review',
                        'status' => 'submitted',
                    ]);

                    return;
                }

                $finalScore = round((float) $attempt->mcq_score + $essayScore, 2);

                $attempt->update([
                    'essay_score' => $essayScore,
                    'final_score' => $finalScore,
                    'grading_status' => 'manual_reviewed',
                    'status' => 'graded',
                ]);

                $progress = DB::table('employee_training_progress')
                    ->where('employee_id', $attempt->employee_id)
                    ->where('training_id', $attempt->training_id)
                    ->lockForUpdate()
                    ->first();

                if ($progress === null) {
                    return;
                }

                if ($attempt->test_type === 'pre_test') {
                    DB::table('employee_training_progress')
                        ->where('employee_id', $attempt->employee_id)
                        ->where('training_id', $attempt->training_id)
                        ->update([
                            'pre_test_completed_at' => $progress->pre_test_completed_at ?? Carbon::now(),
                        ]);

                    return;
                }

                $training = $attempt->training;
                $passStatus = $finalScore >= (float) ($training?->passing_grade ?? 0) ? 'passed' : 'failed';

                $attempt->update([
                    'pass_status' => $passStatus,
                ]);

                DB::table('employee_training_progress')
                    ->where('employee_id', $attempt->employee_id)
                    ->where('training_id', $attempt->training_id)
                    ->update([
                        'status' => $passStatus,
                        'final_score' => $finalScore,
                        'final_status' => $passStatus,
                        'completed_at' => Carbon::now(),
                    ]);
            });

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
