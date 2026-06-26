<?php

namespace App\Http\Requests\Admin\Division;

use App\Models\Division;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDivisionRequest extends FormRequest
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
        /** @var Division|null $division */
        $division = $this->route('division');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('divisions', 'name')->ignore($division),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama divisi wajib diisi.',
            'name.unique' => 'Nama divisi sudah digunakan.',
            'name.max' => 'Nama divisi maksimal 100 karakter.',
            'description.string' => 'Deskripsi divisi harus berupa teks.',
            'is_active.required' => 'Status divisi wajib dipilih.',
            'is_active.boolean' => 'Status divisi tidak valid.',
        ];
    }
}
