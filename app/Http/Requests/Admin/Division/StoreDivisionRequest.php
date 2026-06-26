<?php

namespace App\Http\Requests\Admin\Division;

use Illuminate\Foundation\Http\FormRequest;

class StoreDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:divisions,name'],
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
