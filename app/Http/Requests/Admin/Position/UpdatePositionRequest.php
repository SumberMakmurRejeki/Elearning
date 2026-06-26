<?php

namespace App\Http\Requests\Admin\Position;

use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
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
        /** @var Position|null $position */
        $position = $this->route('position');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('positions', 'name')->ignore($position),
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
            'name.required' => 'Nama jabatan wajib diisi.',
            'name.unique' => 'Nama jabatan sudah digunakan.',
            'name.max' => 'Nama jabatan maksimal 100 karakter.',
            'description.string' => 'Deskripsi jabatan harus berupa teks.',
            'is_active.required' => 'Status jabatan wajib dipilih.',
            'is_active.boolean' => 'Status jabatan tidak valid.',
        ];
    }
}
