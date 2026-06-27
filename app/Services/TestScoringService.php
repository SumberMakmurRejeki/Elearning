<?php

namespace App\Services;

use App\Models\Question;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use Illuminate\Support\Collection;

class TestScoringService
{
    /**
     * @param Collection<int, Question> $questions
     * @param array<int, array{question_id:int,selected_option_id:int|null,essay_answer:string|null}> $answers
     * @return array{hasEssay:bool,mcqScore:float,finalScore:?float,gradingStatus:string,passStatus:?string}
     */
    public function scoreAttempt(TestAttempt $attempt, Collection $questions, array $answers): array
    {
        $answerMap = collect($answers)->keyBy('question_id');

        $mcqTotalWeight = 0.0;
        $mcqEarnedWeight = 0.0;
        $hasEssay = false;

        foreach ($questions as $question) {
            $submitted = $answerMap->get($question->id, []);
            $selectedOptionId = $submitted['selected_option_id'] ?? null;
            $essayAnswer = $submitted['essay_answer'] ?? null;

            if ($question->question_type === 'multiple_choice') {
                $mcqTotalWeight += (float) $question->weight;

                $selectedOption = $selectedOptionId === null
                    ? null
                    : $question->options()->whereKey($selectedOptionId)->first();

                $isCorrect = $selectedOption?->is_correct === true;
                $score = $isCorrect ? (float) $question->weight : 0.0;

                if ($isCorrect) {
                    $mcqEarnedWeight += (float) $question->weight;
                }

                TestAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'selected_option_id' => $selectedOption?->id,
                        'essay_answer' => null,
                        'is_correct' => $isCorrect,
                        'score' => $score,
                    ]
                );

                continue;
            }

            $hasEssay = true;

            TestAnswer::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ],
                [
                    'selected_option_id' => null,
                    'essay_answer' => $essayAnswer,
                    'is_correct' => null,
                    'score' => 0,
                ]
            );
        }

        $mcqScore = $mcqTotalWeight > 0
            ? round(($mcqEarnedWeight / $mcqTotalWeight) * 100, 2)
            : 0.0;

        $gradingStatus = $hasEssay ? 'waiting_manual_review' : 'auto_graded';
        $finalScore = $hasEssay ? null : $mcqScore;

        $attempt->update([
            'mcq_score' => $mcqScore,
            'essay_score' => $attempt->essay_score ?? 0,
            'final_score' => $finalScore,
            'grading_status' => $gradingStatus,
        ]);

        return [
            'hasEssay' => $hasEssay,
            'mcqScore' => $mcqScore,
            'finalScore' => $finalScore,
            'gradingStatus' => $gradingStatus,
            'passStatus' => null,
        ];
    }
}
