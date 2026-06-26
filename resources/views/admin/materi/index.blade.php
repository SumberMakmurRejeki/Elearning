<x-layouts.admin title="Materi Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Materi Training"
            description="Kelola materi file dan link untuk setiap training, termasuk status aktif, urutan, dan akses file private."
        >
            <x-button.link href="{{ route('admin.materi.create') }}" variant="primary">Tambah Materi</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            <form method="GET" action="{{ route('admin.materi.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_220px_220px_180px_auto]">
                <x-form.input
                    label="Search materi"
                    name="q"
                    type="search"
                    placeholder="Cari judul atau deskripsi materi"
                    :value="$query"
                />

                <x-form.select
                    label="Training"
                    name="training_id"
                    :options="$trainingOptions"
                    :selected="$trainingId"
                    placeholder="Semua"
                />

                <x-form.select
                    label="Tipe Materi"
                    name="material_type"
                    :options="$materialTypeOptions"
                    :selected="$materialType"
                    placeholder="Semua"
                />

                <x-form.select
                    label="Status"
                    name="status"
                    :options="$statusOptions"
                    :selected="$status"
                />

                <div class="flex items-end gap-2">
                    <x-button.primary type="submit">Cari / Filter</x-button.primary>
                    <x-button.link href="{{ route('admin.materi.index') }}" variant="text">Reset</x-button.link>
                </div>
            </form>
        </x-card.base>

        <x-card.base class="p-0">
            <x-table.table>
                <x-table.header>
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Judul Materi</th>
                        <th class="px-6 py-4">Training</th>
                        <th class="px-6 py-4">Tipe Materi</th>
                        <th class="px-6 py-4">Sumber Materi</th>
                        <th class="px-6 py-4">Urutan</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Tanggal Dibuat</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </x-table.header>

                <tbody>
                    @forelse ($materials as $material)
                        <x-table.row>
                            <td class="px-6 py-4 text-sm text-graphite">{{ $materials->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <p class="font-medium text-ink">{{ $material->title }}</p>
                                    <p class="text-sm text-charcoal">{{ \Illuminate\Support\Str::limit($material->description ?: 'Tidak ada deskripsi materi.', 80) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ $material->training?->title ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$material->material_type === 'file' ? 'info' : 'ink'">{{ $material->material_type === 'file' ? 'File Upload' : 'Link Eksternal' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">
                                @if ($material->material_type === 'file')
                                    {{ basename((string) $material->file_path) }}
                                @else
                                    {{ \Illuminate\Support\Str::limit((string) $material->url, 40) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ $material->order_number ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$material->is_active ? 'success' : 'neutral'">{{ $material->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ optional($material->created_at)->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <x-button.link href="{{ route('admin.materi.show', $material) }}" variant="icon" aria-label="Detail {{ $material->title }}" title="Detail">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/>
                                            <circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/>
                                        </svg>
                                    </x-button.link>

                                    <x-button.link href="{{ route('admin.materi.edit', $material) }}" variant="icon" aria-label="Edit {{ $material->title }}" title="Edit">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M4 13.8V16h2.2l8.1-8.1-2.2-2.2L4 13.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M11.8 5.7l2.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    </x-button.link>

                                    @if ($material->material_type === 'file' && $material->file_path)
                                        <x-button.link href="{{ route('admin.materi.preview-file', $material) }}" variant="icon" aria-label="Preview {{ $material->title }}" title="Preview File">
                                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                                <path d="M10 3.5v8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                <path d="m6.8 8.7 3.2 3.2 3.2-3.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M4 14.5h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            </svg>
                                        </x-button.link>
                                    @endif

                                    @if ($material->is_active)
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-{{ $material->id }}" aria-label="Nonaktifkan {{ $material->title }}" title="Nonaktifkan">
                                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                                <path d="M10 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                <path d="M7.2 5.2a6 6 0 1 0 5.6 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-{{ $material->id }}" aria-label="Aktifkan {{ $material->title }}" title="Aktifkan">
                                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                                <path d="M10 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                <path d="M7.2 5.2a6 6 0 1 0 5.6 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-danger bg-white text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-{{ $material->id }}" aria-label="Delete permanen {{ $material->title }}" title="Delete Permanen">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M3.5 5.5h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7 5.5V4.25A1.25 1.25 0 0 1 8.25 3h3.5A1.25 1.25 0 0 1 13 4.25V5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7.5 8.5v6.5M10 8.5V15M12.5 8.5v6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M5.5 5.5l.6 10.3A1.5 1.5 0 0 0 7.6 17h4.8a1.5 1.5 0 0 0 1.5-1.2l.6-10.3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>

                                <x-modal.confirm
                                    id="toggle-{{ $material->id }}"
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
                                    id="delete-{{ $material->id }}"
                                    title="Delete Permanen Materi"
                                    description="Materi yang dihapus permanen tidak dapat dikembalikan. Lanjutkan?"
                                    confirm-label="Delete Permanen"
                                    :danger="true"
                                    :action="route('admin.materi.destroy', $material)"
                                    method="DELETE"
                                />
                            </td>
                        </x-table.row>
                    @empty
                        <x-table.empty
                            colspan="9"
                            :title="$hasFilters ? 'Tidak ada materi sesuai filter.' : 'Belum ada data materi training.'"
                            :description="$hasFilters ? 'Coba ubah filter training, tipe, status, atau kata kunci pencarian.' : 'Tambah materi pertama untuk mulai melengkapi training.'"
                            :action-label="$hasFilters ? 'Reset Filter' : 'Tambah Materi'"
                            :action-href="$hasFilters ? route('admin.materi.index') : route('admin.materi.create')"
                        />
                    @endforelse
                </tbody>
            </x-table.table>

            @if ($materials->hasPages())
                <div class="border-t border-fog px-6 py-4">
                    {{ $materials->links() }}
                </div>
            @endif
        </x-card.base>
    </div>
</x-layouts.admin>
