<x-layouts.admin title="Detail Karyawan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Detail Karyawan"
            description="Lihat ringkasan akun, profil, dan status penggunaan data karyawan."
        >
            <x-button.link href="{{ route('admin.karyawan.index') }}" variant="text">Kembali</x-button.link>
            <x-button.link href="{{ route('admin.karyawan.edit', $employee) }}" variant="primary">Edit Karyawan</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)]">
            <x-card.base>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-graphite">Nama Karyawan</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $employee->user->name }}</h2>
                        <p class="mt-1 text-sm text-charcoal">{{ $employee->user->username }}</p>
                    </div>

                    <x-badge :variant="$employee->is_active ? 'success' : 'neutral'">{{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                </div>

                <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">NIP/ID Karyawan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $employee->employee_number ?: '-' }}</dd>
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
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Login Terakhir</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($employee->user->last_login_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Dibuat</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($employee->created_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Diperbarui</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($employee->updated_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>
                </dl>
            </x-card.base>

            <div class="space-y-6">
                @if ($hasDependencies)
                    <x-alert variant="warning" title="Karyawan sedang digunakan">
                        Karyawan ini sudah punya riwayat training atau test. Delete permanen akan ditolak oleh sistem.
                    </x-alert>
                @endif

                <x-card.base>
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ink">Aksi Cepat</h3>
                        <p class="text-sm text-charcoal">Nonaktifkan, reset password, atau hapus permanen dari halaman detail ini.</p>

                        <div class="flex flex-col gap-3">
                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#reset-password-{{ $employee->id }}">
                                Reset Password
                            </button>

                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-employee-{{ $employee->id }}">
                                {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>

                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-danger bg-white px-4 py-2.5 text-sm font-semibold text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-employee-{{ $employee->id }}">
                                Delete Permanen
                            </button>
                        </div>
                    </div>
                </x-card.base>
            </div>
        </div>

        <x-modal.confirm
            id="reset-password-{{ $employee->id }}"
            title="Reset Password Karyawan"
            description="Password baru akan langsung menggantikan password lama."
            confirm-label="Reset Password"
            :action="route('admin.karyawan.reset-password', $employee)"
            method="POST"
            :open="$errors->resetPassword->any()"
        >
            <div class="grid gap-4">
                <x-form.input
                    label="Password Baru"
                    name="password"
                    type="password"
                    placeholder="Minimal 8 karakter"
                />

                <x-form.input
                    label="Konfirmasi Password Baru"
                    name="password_confirmation"
                    type="password"
                    placeholder="Ulangi password baru"
                />
            </div>
        </x-modal.confirm>

        <x-modal.confirm
            id="toggle-employee-{{ $employee->id }}"
            :title="$employee->is_active ? 'Nonaktifkan Karyawan' : 'Aktifkan Karyawan'"
            :description="$employee->is_active ? 'Akun karyawan ini akan dinonaktifkan.' : 'Akun karyawan ini akan diaktifkan kembali.'"
            :confirm-label="$employee->is_active ? 'Nonaktifkan' : 'Aktifkan'"
            :danger="$employee->is_active"
            :action="route('admin.karyawan.status', $employee)"
            method="PATCH"
        >
            <input type="hidden" name="is_active" value="{{ $employee->is_active ? 0 : 1 }}">
        </x-modal.confirm>

        <x-modal.confirm
            id="delete-employee-{{ $employee->id }}"
            title="Delete Permanen Karyawan"
            description="Karyawan yang dihapus permanen tidak dapat dikembalikan. Jika sudah punya riwayat training atau test, sistem akan menolak proses ini."
            confirm-label="Delete Permanen"
            :danger="true"
            :action="route('admin.karyawan.destroy', $employee)"
            method="DELETE"
        />
    </div>
</x-layouts.admin>
