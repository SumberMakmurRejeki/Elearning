<x-layouts.admin title="Master Data Jabatan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Master Data Jabatan"
            description="Kelola data jabatan perusahaan."
        >
            <x-button.link href="{{ route('admin.jabatan.create') }}" variant="primary">Tambah Jabatan</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            <form method="GET" action="{{ route('admin.jabatan.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(240px,0.7fr)_auto]">
                <x-form.input
                    label="Search nama jabatan"
                    name="q"
                    type="search"
                    placeholder="Cari nama atau deskripsi jabatan"
                    :value="$query"
                />

                <x-form.select
                    label="Status"
                    name="status"
                    :options="$statusOptions"
                    :selected="$status"
                />

                <div class="flex items-end gap-2">
                    <x-button.primary type="submit">Cari / Filter</x-button.primary>
                    <x-button.link href="{{ route('admin.jabatan.index') }}" variant="text">Reset</x-button.link>
                </div>
            </form>
        </x-card.base>

        <x-card.base class="p-0">
            <x-table.table>
                <x-table.header>
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Nama Jabatan</th>
                        <th class="px-6 py-4">Deskripsi Singkat</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Tanggal Dibuat</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </x-table.header>

                <tbody>
                    @forelse ($positions as $position)
                        <x-table.row>
                            <td class="px-6 py-4 text-sm text-graphite">{{ $positions->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 font-medium text-ink">{{ $position->name }}</td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ \Illuminate\Support\Str::limit($position->description ?: '-', 80) }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$position->is_active ? 'success' : 'neutral'">{{ $position->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ optional($position->created_at)->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                    <x-button.link href="{{ route('admin.jabatan.show', $position) }}" variant="icon" aria-label="Detail {{ $position->name }}" title="Detail">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                            <path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/>
                            <circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/>
                        </svg>
                    </x-button.link>

                    <x-button.link href="{{ route('admin.jabatan.edit', $position) }}" variant="icon" aria-label="Edit {{ $position->name }}" title="Edit">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                            <path d="M4 13.8V16h2.2l8.1-8.1-2.2-2.2L4 13.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            <path d="M11.8 5.7l2.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                    </x-button.link>

                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-{{ $position->id }}" aria-label="{{ $position->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $position->name }}" title="{{ $position->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M10 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7.2 5.2a6 6 0 1 0 5.6 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-danger shadow-sm transition hover:border-danger hover:text-danger focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-{{ $position->id }}" aria-label="Delete permanen {{ $position->name }}" title="Delete Permanen">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M3.5 5.5h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7 5.5V4.25A1.25 1.25 0 0 1 8.25 3h3.5A1.25 1.25 0 0 1 13 4.25V5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7.5 8.5v6.5M10 8.5V15M12.5 8.5v6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M5.5 5.5l.6 10.3A1.5 1.5 0 0 0 7.6 17h4.8a1.5 1.5 0 0 0 1.5-1.2l.6-10.3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>

                                <x-modal.confirm
                                    id="toggle-{{ $position->id }}"
                                    :title="$position->is_active ? 'Nonaktifkan Jabatan' : 'Aktifkan Jabatan'"
                                    :description="$position->is_active ? 'Jabatan aktif ini akan dinonaktifkan.' : 'Jabatan nonaktif ini akan diaktifkan kembali.'"
                                    :confirm-label="$position->is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                    :danger="$position->is_active"
                                    :action="route('admin.jabatan.status', $position)"
                                    method="PATCH"
                                >
                                    <input type="hidden" name="is_active" value="{{ $position->is_active ? 0 : 1 }}">
                                </x-modal.confirm>

                                <x-modal.confirm
                                    id="delete-{{ $position->id }}"
                                    title="Delete Permanen Jabatan"
                                    description="Jabatan yang dihapus permanen tidak dapat dikembalikan. Lanjutkan?"
                                    confirm-label="Delete Permanen"
                                    :danger="true"
                                    :action="route('admin.jabatan.destroy', $position)"
                                    method="DELETE"
                                />
                            </td>
                        </x-table.row>
                    @empty
                        <x-table.empty
                            colspan="6"
                            :title="$hasFilters ? 'Tidak ada jabatan sesuai filter.' : 'Belum ada data jabatan.'"
                            :description="$hasFilters ? 'Coba ubah kata kunci atau status filter.' : 'Tambah jabatan pertama untuk memulai pengelolaan data.'"
                            :action-label="$hasFilters ? 'Reset Filter' : 'Tambah Jabatan'"
                            :action-href="$hasFilters ? route('admin.jabatan.index') : route('admin.jabatan.create')"
                        />
                    @endforelse
                </tbody>
            </x-table.table>

            @if ($positions->hasPages())
                <div class="border-t border-fog px-6 py-4">
                    {{ $positions->links() }}
                </div>
            @endif
        </x-card.base>
    </div>
</x-layouts.admin>
