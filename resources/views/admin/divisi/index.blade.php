<x-layouts.admin title="Master Data Divisi - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Data"
            title="Master Data Divisi"
            description="Kelola data divisi perusahaan."
        >
            <x-button.link href="{{ route('admin.divisi.create') }}" variant="primary">Tambah Divisi</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            <form method="GET" action="{{ route('admin.divisi.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(240px,0.7fr)_auto]">
                <x-form.input
                    label="Search nama divisi"
                    name="q"
                    type="search"
                    placeholder="Cari nama atau deskripsi divisi"
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
                    <x-button.link href="{{ route('admin.divisi.index') }}" variant="text">Reset</x-button.link>
                </div>
            </form>
        </x-card.base>

        <x-card.base class="p-0">
            <x-table.table>
                <x-table.header>
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Nama Divisi</th>
                        <th class="px-6 py-4">Deskripsi Singkat</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Tanggal Dibuat</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </x-table.header>

                <tbody>
                    @forelse ($divisions as $division)
                        <x-table.row>
                            <td class="px-6 py-4 text-sm text-graphite">{{ $divisions->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 font-medium text-ink">{{ $division->name }}</td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ \Illuminate\Support\Str::limit($division->description ?: '-', 80) }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$division->is_active ? 'success' : 'neutral'">{{ $division->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ optional($division->created_at)->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                    <x-button.link href="{{ route('admin.divisi.show', $division) }}" variant="icon" aria-label="Detail {{ $division->name }}" title="Detail">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                            <path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/>
                            <circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/>
                        </svg>
                    </x-button.link>

                    <x-button.link href="{{ route('admin.divisi.edit', $division) }}" variant="icon" aria-label="Edit {{ $division->name }}" title="Edit">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                            <path d="M4 13.8V16h2.2l8.1-8.1-2.2-2.2L4 13.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            <path d="M11.8 5.7l2.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                    </x-button.link>

                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-{{ $division->id }}" aria-label="{{ $division->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $division->name }}" title="{{ $division->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M10 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7.2 5.2a6 6 0 1 0 5.6 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-danger shadow-sm transition hover:border-danger hover:text-danger focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-{{ $division->id }}" aria-label="Delete permanen {{ $division->name }}" title="Delete Permanen">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M3.5 5.5h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7 5.5V4.25A1.25 1.25 0 0 1 8.25 3h3.5A1.25 1.25 0 0 1 13 4.25V5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7.5 8.5v6.5M10 8.5V15M12.5 8.5v6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M5.5 5.5l.6 10.3A1.5 1.5 0 0 0 7.6 17h4.8a1.5 1.5 0 0 0 1.5-1.2l.6-10.3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>

                                <x-modal.confirm
                                    id="toggle-{{ $division->id }}"
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
                                    id="delete-{{ $division->id }}"
                                    title="Delete Permanen Divisi"
                                    description="Divisi yang dihapus permanen tidak dapat dikembalikan. Lanjutkan?"
                                    confirm-label="Delete Permanen"
                                    :danger="true"
                                    :action="route('admin.divisi.destroy', $division)"
                                    method="DELETE"
                                />
                            </td>
                        </x-table.row>
                    @empty
                        <x-table.empty
                            colspan="6"
                            :title="$hasFilters ? 'Tidak ada divisi sesuai filter.' : 'Belum ada data divisi.'"
                            :description="$hasFilters ? 'Coba ubah kata kunci atau status filter.' : 'Tambah divisi pertama untuk memulai pengelolaan data.'"
                            :action-label="$hasFilters ? 'Reset Filter' : 'Tambah Divisi'"
                            :action-href="$hasFilters ? route('admin.divisi.index') : route('admin.divisi.create')"
                        />
                    @endforelse
                </tbody>
            </x-table.table>

            @if ($divisions->hasPages())
                <div class="border-t border-fog px-6 py-4">
                    {{ $divisions->links() }}
                </div>
            @endif
        </x-card.base>
    </div>
</x-layouts.admin>
