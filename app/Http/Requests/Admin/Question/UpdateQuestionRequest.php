<?php

namespace App\Http\Requests\Admin\Question;

use App\Models\Question;

class UpdateQuestionRequest extends StoreQuestionRequest
{
    protected function validateUniqueOrderNumber($validator): void
    {
        /** @var Question|null $question */
        $question = $this->route('question');

        $query = \Illuminate\Support\Facades\DB::table('questions')
            ->where('training_id', (int) $this->input('training_id'))
            ->where('test_type', (string) $this->input('test_type'))
            ->where('order_number', (int) $this->input('order_number'));

        if ($question !== null) {
            $query->where('id', '!=', $question->id);
        }

        if ($query->exists()) {
            $validator->errors()->add('order_number', 'Nomor soal sudah digunakan.');
        }
    }
}
