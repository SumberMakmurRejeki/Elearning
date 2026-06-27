@props([
    'employee',
    'user',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Simpan',
    'cancelHref',
    'divisionOptions',
    'positionOptions',
    'statusOptions',
    'passwordHelp' => 'Kosongkan bila password tidak diubah.',
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf

    @if (strtoupper($method) !== 'POST')
        @method(strtoupper($method))
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.input
            label="Nama Karyawan"
            name="name"
            placeholder="Contoh: Budi Santoso"
            :value="old('name', $user->name)"
        />

        <x-form.input
            label="Username"
            name="username"
            placeholder="Contoh: budi.santoso"
            :value="old('username', $user->username)"
        />

        <x-form.input
            label="NIP/ID Karyawan"
            name="employee_number"
            placeholder="Opsional: EMP-001"
            :value="old('employee_number', $employee->employee_number)"
        />

        <x-form.select
            label="Divisi"
            name="division_id"
            :options="$divisionOptions"
            placeholder="Pilih divisi"
            :selected="old('division_id', $employee->division_id)"
        />

        <x-form.select
            label="Jabatan"
            name="position_id"
            :options="$positionOptions"
            placeholder="Pilih jabatan"
            :selected="old('position_id', $employee->position_id)"
        />

        <x-form.select
            label="Role"
            name="role"
            :options="['karyawan' => 'Karyawan', 'admin' => 'Admin']"
            placeholder="Pilih role"
            :selected="old('role', $user->role ?? 'karyawan')"
        />

        <x-form.select
            label="Status Akun"
            name="is_active"
            :options="$statusOptions"
            :selected="old('is_active', (string) ($employee->is_active ? 1 : 0))"
        />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-form.input
            label="Password Baru"
            name="password"
            type="password"
            placeholder="Minimal 8 karakter"
            :help="$passwordHelp"
        />

        <x-form.input
            label="Konfirmasi Password Baru"
            name="password_confirmation"
            type="password"
            placeholder="Ulangi password baru"
        />
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $cancelHref }}" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Batal</a>
        <x-button.primary type="submit">{{ $submitLabel }}</x-button.primary>
    </div>
</form>
