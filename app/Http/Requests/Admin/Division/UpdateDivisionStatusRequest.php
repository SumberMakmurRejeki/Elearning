<?php

namespace App\Http\Requests\Admin\Division;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDivisionStatusRequest extends FormRequest
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
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'is_active.required' => 'Status divisi wajib dipilih.',
            'is_active.boolean' => 'Status divisi tidak valid.',
        ];
    }
}
