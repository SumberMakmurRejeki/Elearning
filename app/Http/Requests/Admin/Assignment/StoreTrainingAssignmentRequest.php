<?php

namespace App\Http\Requests\Admin\Assignment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainingAssignmentRequest extends FormRequest
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
            'target_type' => ['required', 'string', Rule::in(['employee', 'division', 'position'])],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
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
            'target_type.required' => 'Target penugasan wajib dipilih.',
            'target_type.in' => 'Target penugasan tidak valid.',
            'employee_ids.*.exists' => 'Karyawan yang dipilih tidak valid.',
            'division_id.exists' => 'Divisi yang dipilih tidak valid.',
            'position_id.exists' => 'Jabatan yang dipilih tidak valid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validatePublishedTraining($validator);
            $this->validateTargetSpecificSelection($validator);
            $this->validateSelectedEmployeesAreActive($validator);
        });
    }

    private function validatePublishedTraining($validator): void
    {
        $training = \Illuminate\Support\Facades\DB::table('trainings')
            ->select('status')
            ->where('id', (int) $this->input('training_id'))
            ->first();

        if ($training === null || $training->status !== 'published') {
            $validator->errors()->add('training_id', 'Training belum dapat ditugaskan.');
        }
    }

    private function validateTargetSpecificSelection($validator): void
    {
        $targetType = $this->input('target_type');

        if ($targetType === 'employee' && count((array) $this->input('employee_ids', [])) === 0) {
            $validator->errors()->add('employee_ids', 'Karyawan wajib dipilih.');
        }

        if ($targetType === 'division' && ! $this->filled('division_id')) {
            $validator->errors()->add('division_id', 'Divisi wajib dipilih.');
        }

        if ($targetType === 'position' && ! $this->filled('position_id')) {
            $validator->errors()->add('position_id', 'Jabatan wajib dipilih.');
        }
    }

    private function validateSelectedEmployeesAreActive($validator): void
    {
        if ($this->input('target_type') !== 'employee') {
            return;
        }

        $inactiveExists = \Illuminate\Support\Facades\DB::table('employees')
            ->whereIn('id', (array) $this->input('employee_ids', []))
            ->where('is_active', false)
            ->exists();

        if ($inactiveExists) {
            $validator->errors()->add('employee_ids', 'Karyawan nonaktif tidak dapat menerima penugasan baru.');
        }
    }
}
