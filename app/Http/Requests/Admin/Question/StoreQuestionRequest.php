<?php

namespace App\Http\Requests\Admin\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'training_id' => ['required', 'integer', 'exists:trainings,id'],
            'test_type' => ['required', 'string', Rule::in(['pre_test', 'post_test'])],
            'question_type' => ['required', 'string', Rule::in(['multiple_choice', 'essay'])],
            'order_number' => ['required', 'integer', 'min:1'],
            'question_text' => ['required', 'string'],
            'weight' => ['required', 'numeric', 'gt:0'],
            'is_active' => ['required', 'boolean'],
            'options' => ['nullable', 'array'],
            'options.*.option_text' => ['nullable', 'string'],
            'options.*.is_correct' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'training_id.required' => 'Training wajib dipilih.',
            'training_id.exists' => 'Training yang dipilih tidak valid.',
            'test_type.required' => 'Jenis test wajib dipilih.',
            'test_type.in' => 'Jenis test tidak valid.',
            'question_type.required' => 'Jenis soal wajib dipilih.',
            'question_type.in' => 'Jenis soal tidak valid.',
            'order_number.required' => 'Nomor soal wajib diisi.',
            'order_number.integer' => 'Nomor soal harus berupa angka.',
            'order_number.min' => 'Nomor soal minimal 1.',
            'question_text.required' => 'Pertanyaan wajib diisi.',
            'weight.required' => 'Bobot nilai wajib diisi.',
            'weight.numeric' => 'Bobot nilai harus berupa angka.',
            'weight.gt' => 'Bobot nilai harus lebih dari 0.',
            'is_active.required' => 'Status soal wajib dipilih.',
            'is_active.boolean' => 'Status soal tidak valid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateUniqueOrderNumber($validator);
            $this->validateMultipleChoiceOptions($validator);
        });
    }

    protected function validateUniqueOrderNumber($validator): void
    {
        $exists = \Illuminate\Support\Facades\DB::table('questions')
            ->where('training_id', (int) $this->input('training_id'))
            ->where('test_type', (string) $this->input('test_type'))
            ->where('order_number', (int) $this->input('order_number'))
            ->exists();

        if ($exists) {
            $validator->errors()->add('order_number', 'Nomor soal sudah digunakan.');
        }
    }

    protected function validateMultipleChoiceOptions($validator): void
    {
        if ($this->input('question_type') !== 'multiple_choice') {
            return;
        }

        $options = collect($this->input('options', []))
            ->map(static fn ($option): array => [
                'option_text' => trim((string) ($option['option_text'] ?? '')),
                'is_correct' => (bool) ($option['is_correct'] ?? false),
            ])
            ->filter(static fn (array $option): bool => $option['option_text'] !== '')
            ->values();

        if ($options->count() < 2) {
            $validator->errors()->add('options', 'Pilihan jawaban minimal 2 opsi untuk soal pilihan ganda.');

            return;
        }

        if ($options->contains(static fn (array $option): bool => $option['option_text'] === '')) {
            $validator->errors()->add('options', 'Pilihan jawaban wajib diisi untuk soal pilihan ganda.');

            return;
        }

        $correctAnswers = $options->filter(static fn (array $option): bool => $option['is_correct'])->count();

        if ($correctAnswers !== 1) {
            $validator->errors()->add('options', 'Soal pilihan ganda harus memiliki tepat 1 jawaban benar.');
        }
    }
}
