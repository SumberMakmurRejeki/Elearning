<x-layouts.employee title="Profil Karyawan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Akun"
            title="Profil Karyawan"
            description="Kelola informasi profil dan password akun Anda."
        />

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        {{-- Informasi Karyawan --}}
        @if ($employee)
            <x-card.base>
                <h3 class="mb-4 text-lg font-semibold text-ink">Informasi Karyawan</h3>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">NIP/ID Karyawan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $employee->employee_number ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Divisi</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $employee->division?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jabatan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $employee->position?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status Akun</dt>
                        <dd class="mt-2">
                            <x-badge :variant="$employee->is_active ? 'success' : 'danger'">
                                {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </dd>
                    </div>
                </dl>
            </x-card.base>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Profil --}}
            <x-card.base class="space-y-6">
                <h3 class="text-lg font-semibold text-ink">Edit Profil</h3>

                <form method="POST" action="{{ route('employee.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <x-form.input label="Nama" name="name" type="text" :value="old('name', $user->name)" required />
                    <x-form.input label="Username" name="username" type="text" :value="old('username', $user->username)" required />

                    <div class="pt-2">
                        <x-button.primary type="submit">Simpan Profil</x-button.primary>
                    </div>
                </form>
            </x-card.base>

            {{-- Ubah Password --}}
            <x-card.base class="space-y-6">
                <h3 class="text-lg font-semibold text-ink">Ubah Password</h3>

                <form method="POST" action="{{ route('employee.profile.update-password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <x-form.input label="Password Lama" name="current_password" type="password" autocomplete="current-password" />
                    <x-form.input label="Password Baru" name="new_password" type="password" autocomplete="new-password" help="Minimal 8 karakter." />
                    <x-form.input label="Konfirmasi Password Baru" name="new_password_confirmation" type="password" autocomplete="new-password" />

                    <div class="pt-2">
                        <x-button.primary type="submit">Ubah Password</x-button.primary>
                    </div>
                </form>
            </x-card.base>
        </div>
    </div>
</x-layouts.employee>
