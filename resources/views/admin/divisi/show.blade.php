<x-layouts.admin title="Detail Divisi - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Detail Divisi"
            description="Lihat ringkasan data divisi, status, dan jumlah karyawan yang menggunakan divisi ini."
        >
            <x-button.link href="{{ route('admin.divisi.index') }}" variant="text">Kembali</x-button.link>
            <x-button.link href="{{ route('admin.divisi.edit', $division) }}" variant="primary">Edit Divisi</x-button.link>
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
                        <p class="text-sm font-medium text-graphite">Nama Divisi</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $division->name }}</h2>
                    </div>

                    <x-badge :variant="$division->is_active ? 'success' : 'neutral'">{{ $division->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                </div>

                <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $division->is_active ? 'Divisi aktif dan dapat dipilih pada data karyawan.' : 'Divisi nonaktif dan tidak disarankan dipakai pada data baru.' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Karyawan Terkait</dt>
                        <dd class="mt-2 text-2xl font-semibold text-ink">{{ $employeeCount }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Dibuat</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($division->created_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Diperbarui</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($division->updated_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 rounded-2xl border border-fog bg-white p-4">
                    <p class="text-sm font-semibold text-ink">Deskripsi</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-charcoal">{{ $division->description ?: 'Tidak ada deskripsi untuk divisi ini.' }}</p>
                </div>
            </x-card.base>

            <div class="space-y-6">
                @if ($employeeCount > 0)
                    <x-alert variant="warning" title="Divisi sedang digunakan">
                        Divisi ini masih dipakai oleh {{ $employeeCount }} data karyawan. Hapus permanen akan ditolak oleh sistem.
                    </x-alert>
                @endif

                <x-card.base>
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ink">Aksi Cepat</h3>
                        <p class="text-sm text-charcoal">Nonaktifkan divisi jika masih dipakai, atau hapus permanen jika sudah tidak digunakan.</p>

                        <div class="flex flex-col gap-3">
                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-division-{{ $division->id }}">
                                {{ $division->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>

                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-danger bg-white px-4 py-2.5 text-sm font-semibold text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-division-{{ $division->id }}">
                                Delete Permanen
                            </button>
                        </div>
                    </div>
                </x-card.base>
            </div>
        </div>

        <x-modal.confirm
            id="toggle-division-{{ $division->id }}"
            :title="$division->is_active ? 'Nonaktifkan Divisi' : 'Aktifkan Divisi'"
            :description="$division->is_active ? 'Divisi aktif ini akan dinonaktifkan.' : 'Divisi nonaktif ini akan diaktifkan kembali.'"
            :confirm-label="$division->is_active ? 'Nonaktifkan' : 'Aktifkan'"
            :danger="$division->is_active"
            :action="route('admin.divisi.status', $division)"
            method="PATCH"
        >
            <input type="hidden" name="is_active" value="{{ $division->is_active ? 0 : 1 }}">
        </x-modal.confirm>

        <x-modal.confirm
            id="delete-division-{{ $division->id }}"
            title="Delete Permanen Divisi"
            description="Divisi yang dihapus permanen tidak dapat dikembalikan."
            confirm-label="Delete Permanen"
            :danger="true"
            :action="route('admin.divisi.destroy', $division)"
            method="DELETE"
        />
    </div>
</x-layouts.admin>
