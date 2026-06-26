<x-layouts.admin title="Detail Materi - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Detail Materi"
            description="Lihat detail materi training, status akses, dan sumber file atau link sebelum melakukan aksi lanjutan."
        >
            <x-button.link href="{{ route('admin.materi.index') }}" variant="text">Kembali</x-button.link>
            <x-button.link href="{{ route('admin.materi.edit', $material) }}" variant="primary">Edit Materi</x-button.link>
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
                        <p class="text-sm font-medium text-graphite">Judul Materi</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $material->title }}</h2>
                    </div>

                    <x-badge :variant="$material->is_active ? 'success' : 'neutral'">{{ $material->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                </div>

                <dl class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Training</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $material->training?->title ?? '-' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tipe Sumber Materi</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $material->material_type === 'file' ? 'File Upload' : 'Link Eksternal' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Urutan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $material->order_number ?? '-' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Nama File / URL</dt>
                        <dd class="mt-2 break-all text-sm font-medium text-ink">{{ $sourceLabel }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tipe File</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $material->file_type ? strtoupper($material->file_type) : '-' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Ukuran File</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $fileSizeLabel }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Dibuat</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($material->created_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Diperbarui</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($material->updated_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>

                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jumlah Akses Karyawan</dt>
                        <dd class="mt-2 text-2xl font-semibold text-ink">{{ $accessCount }}</dd>
                    </div>
                </dl>

                <div class="mt-6 rounded-2xl border border-fog bg-white p-4">
                    <p class="text-sm font-semibold text-ink">Deskripsi Materi</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-charcoal">{{ $material->description ?: 'Tidak ada deskripsi materi.' }}</p>
                </div>
            </x-card.base>

            <div class="space-y-6">
                @if ($accessCount > 0)
                    <x-alert variant="warning" title="Materi sudah pernah diakses">
                        Materi ini sudah pernah diakses oleh karyawan. Delete permanen akan ditolak oleh sistem.
                    </x-alert>
                @endif

                <x-card.base>
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ink">Aksi Cepat</h3>
                        <p class="text-sm text-charcoal">Kelola status materi, akses file private untuk admin, atau hapus permanen jika belum pernah diakses.</p>

                        <div class="flex flex-col gap-3">
                            @if ($material->material_type === 'file' && $material->file_path)
                                <x-button.link href="{{ route('admin.materi.preview-file', $material) }}" variant="text">Preview File</x-button.link>
                                <x-button.link href="{{ route('admin.materi.download-file', $material) }}" variant="text">Download File</x-button.link>
                            @elseif ($material->url)
                                <x-button.link href="{{ $material->url }}" variant="text" target="_blank" rel="noreferrer noopener">Buka Link Materi</x-button.link>
                            @endif

                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-material-{{ $material->id }}">
                                {{ $material->is_active ? 'Nonaktifkan Materi' : 'Aktifkan Materi' }}
                            </button>

                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-danger bg-white px-4 py-2.5 text-sm font-semibold text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-material-{{ $material->id }}">Delete Permanen</button>
                        </div>
                    </div>
                </x-card.base>
            </div>
        </div>

        <x-modal.confirm
            id="toggle-material-{{ $material->id }}"
            :title="$material->is_active ? 'Nonaktifkan Materi' : 'Aktifkan Materi'"
            :description="$material->is_active ? 'Materi aktif ini akan dinonaktifkan.' : 'Materi nonaktif ini akan diaktifkan kembali.'"
            :confirm-label="$material->is_active ? 'Nonaktifkan' : 'Aktifkan'"
            :danger="$material->is_active"
            :action="route('admin.materi.status', $material)"
            method="PATCH"
        >
            <input type="hidden" name="is_active" value="{{ $material->is_active ? 0 : 1 }}">
        </x-modal.confirm>

        <x-modal.confirm
            id="delete-material-{{ $material->id }}"
            title="Delete Permanen Materi"
            description="Materi yang dihapus permanen tidak dapat dikembalikan."
            confirm-label="Delete Permanen"
            :danger="true"
            :action="route('admin.materi.destroy', $material)"
            method="DELETE"
        />
    </div>
</x-layouts.admin>
