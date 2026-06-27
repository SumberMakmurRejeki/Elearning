<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'employee_number' => ['nullable', 'string', 'max:50', 'unique:employees,employee_number'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'is_active' => ['required', 'boolean'],
            'role' => ['required', 'string', 'in:karyawan,admin'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama karyawan wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'employee_number.unique' => 'NIP/ID karyawan sudah digunakan.',
            'division_id.required' => 'Divisi wajib dipilih.',
            'division_id.exists' => 'Divisi tidak valid.',
            'position_id.required' => 'Jabatan wajib dipilih.',
            'position_id.exists' => 'Jabatan tidak valid.',
            'is_active.required' => 'Status akun wajib dipilih.',
            'is_active.boolean' => 'Status akun tidak valid.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role harus karyawan atau admin.',
        ];
    }
}
