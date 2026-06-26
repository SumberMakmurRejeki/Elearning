<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreTrainingMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|\Closure|string>>
     */
    public function rules(): array
    {
        return [
            'training_id' => ['required', 'integer', 'exists:trainings,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'material_type' => ['required', 'string', Rule::in(['file', 'link'])],
            'file' => $this->fileRules($this->requiresFileUpload()),
            'url' => $this->urlRules($this->requiresUrl()),
            'order_number' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
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
            'title.required' => 'Judul materi wajib diisi.',
            'title.max' => 'Judul materi maksimal 150 karakter.',
            'description.string' => 'Deskripsi materi harus berupa teks.',
            'material_type.required' => 'Tipe materi wajib dipilih.',
            'material_type.in' => 'Tipe materi tidak valid.',
            'file.required' => 'File materi wajib diupload.',
            'file.file' => 'File materi tidak valid.',
            'file.mimes' => 'Format file tidak didukung.',
            'url.required' => 'URL materi wajib diisi.',
            'url.url' => 'URL materi tidak valid.',
            'order_number.integer' => 'Urutan materi harus berupa angka.',
            'order_number.min' => 'Urutan materi minimal 1.',
            'is_active.required' => 'Status materi wajib dipilih.',
            'is_active.boolean' => 'Status materi tidak valid.',
        ];
    }

    protected function requiresFileUpload(): bool
    {
        return $this->input('material_type') === 'file';
    }

    protected function requiresUrl(): bool
    {
        return $this->input('material_type') === 'link';
    }

    /**
     * @return array<int, \Illuminate\Contracts\Validation\ValidationRule|\Closure|string>
     */
    protected function fileRules(bool $required): array
    {
        return [
            Rule::requiredIf($required),
            'nullable',
            'file',
            'mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,csv,mp4,jpg,jpeg,png,webp',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $value instanceof UploadedFile) {
                    return;
                }

                $extension = strtolower($value->getClientOriginalExtension());
                $size = $value->getSize();

                if ($size === false) {
                    return;
                }

                $maxBytes = match (true) {
                    in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) => 5 * 1024 * 1024,
                    $extension === 'mp4' => 100 * 1024 * 1024,
                    default => 20 * 1024 * 1024,
                };

                if ($size > $maxBytes) {
                    $fail('Ukuran file terlalu besar.');
                }
            },
        ];
    }

    /**
     * @return array<int, \Illuminate\Contracts\Validation\ValidationRule|string>
     */
    protected function urlRules(bool $required): array
    {
        return [
            Rule::requiredIf($required),
            'nullable',
            'url',
        ];
    }
}
