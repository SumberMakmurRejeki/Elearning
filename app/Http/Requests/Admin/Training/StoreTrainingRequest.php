<?php

namespace App\Http\Requests\Admin\Training;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainingRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'has_pre_test' => ['required', 'boolean'],
            'has_post_test' => ['required', 'boolean'],
            'passing_grade' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                Rule::requiredIf(fn (): bool => $this->boolean('has_post_test')),
            ],
            'allow_post_test_retake' => ['required', 'boolean'],
            'max_post_test_attempt' => [
                'nullable',
                'integer',
                'min:1',
                Rule::requiredIf(fn (): bool => $this->boolean('allow_post_test_retake')),
            ],
            'show_score_to_employee' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul training wajib diisi.',
            'title.max' => 'Judul training maksimal 150 karakter.',
            'description.string' => 'Deskripsi training harus berupa teks.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date' => 'Tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'has_pre_test.required' => 'Pengaturan pre-test wajib dipilih.',
            'has_pre_test.boolean' => 'Pengaturan pre-test tidak valid.',
            'has_post_test.required' => 'Pengaturan post-test wajib dipilih.',
            'has_post_test.boolean' => 'Pengaturan post-test tidak valid.',
            'passing_grade.required' => 'Passing grade wajib diisi jika post-test aktif.',
            'passing_grade.numeric' => 'Passing grade harus berupa angka.',
            'passing_grade.min' => 'Passing grade harus bernilai 0 sampai 100.',
            'passing_grade.max' => 'Passing grade harus bernilai 0 sampai 100.',
            'allow_post_test_retake.required' => 'Pengaturan pengulangan post-test wajib dipilih.',
            'allow_post_test_retake.boolean' => 'Pengaturan pengulangan post-test tidak valid.',
            'max_post_test_attempt.required' => 'Jumlah maksimal percobaan wajib diisi jika pengulangan post-test diizinkan.',
            'max_post_test_attempt.integer' => 'Jumlah maksimal percobaan harus berupa angka bulat.',
            'max_post_test_attempt.min' => 'Jumlah maksimal percobaan minimal 1 kali.',
            'show_score_to_employee.required' => 'Pengaturan tampilkan nilai wajib dipilih.',
            'show_score_to_employee.boolean' => 'Pengaturan tampilkan nilai tidak valid.',
        ];
    }
}
