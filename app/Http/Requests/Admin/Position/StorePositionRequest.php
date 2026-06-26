<?php

namespace App\Http\Requests\Admin\Position;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', 'unique:positions,name'],
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
            'name.required' => 'Nama jabatan wajib diisi.',
            'name.unique' => 'Nama jabatan sudah digunakan.',
            'name.max' => 'Nama jabatan maksimal 100 karakter.',
            'description.string' => 'Deskripsi jabatan harus berupa teks.',
            'is_active.required' => 'Status jabatan wajib dipilih.',
            'is_active.boolean' => 'Status jabatan tidak valid.',
        ];
    }
}
