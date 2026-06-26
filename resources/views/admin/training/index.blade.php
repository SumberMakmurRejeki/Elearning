<x-layouts.admin title="Daftar Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Daftar Training"
            description="Kelola data training, pengaturan test, dan status publish training karyawan."
        >
            <x-button.link href="{{ route('admin.training.create') }}" variant="primary">Tambah Training</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            <form method="GET" action="{{ route('admin.training.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_220px_220px_180px_auto]">
                <x-form.input
                    label="Search training"
                    name="q"
                    type="search"
                    placeholder="Cari judul atau deskripsi training"
                    :value="$query"
                />

                <x-form.select
                    label="Status"
                    name="status"
                    :options="$statusOptions"
                    :selected="$status"
                />

                <x-form.select
                    label="Bulan"
                    name="month"
                    :options="$monthOptions"
                    :selected="$month"
                    placeholder="Semua"
                />

                <x-form.select
                    label="Tahun"
                    name="year"
                    :options="$yearOptions"
                    :selected="$year"
                    placeholder="Semua"
                />

                <div class="flex items-end gap-2">
                    <x-button.primary type="submit">Cari / Filter</x-button.primary>
                    <x-button.link href="{{ route('admin.training.index') }}" variant="text">Reset</x-button.link>
                </div>
            </form>
        </x-card.base>

        <x-card.base class="p-0">
            <x-table.table>
                <x-table.header>
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Judul Training</th>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Pengaturan Test</th>
                        <th class="px-6 py-4">Dibuat</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </x-table.header>

                <tbody>
                    @forelse ($trainings as $training)
                        <x-table.row>
                            <td class="px-6 py-4 text-sm text-graphite">{{ $trainings->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <p class="font-medium text-ink">{{ $training->title }}</p>
                                    <p class="text-sm text-charcoal">{{ \Illuminate\Support\Str::limit($training->description ?: 'Tidak ada deskripsi training.', 80) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ optional($training->start_date)->translatedFormat('d M Y') }} - {{ optional($training->end_date)->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="match ($training->status) { 'published' => 'success', 'archived' => 'neutral', default => 'warning' }">{{ ucfirst($training->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">
                                Pre-Test: {{ $training->has_pre_test ? 'Ya' : 'Tidak' }}<br>
                                Post-Test: {{ $training->has_post_test ? 'Ya' : 'Tidak' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ optional($training->created_at)->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <x-button.link href="{{ route('admin.training.show', $training) }}" variant="icon" aria-label="Detail {{ $training->title }}" title="Detail">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/>
                                            <circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/>
                                        </svg>
                                    </x-button.link>

                                    <x-button.link href="{{ route('admin.training.edit', $training) }}" variant="icon" aria-label="Edit {{ $training->title }}" title="Edit">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M4 13.8V16h2.2l8.1-8.1-2.2-2.2L4 13.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M11.8 5.7l2.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    </x-button.link>

                                    @if ($training->status === 'draft')
                                        <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-fog bg-white px-4 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#publish-{{ $training->id }}">Publish</button>
                                    @elseif ($training->status === 'published')
                                        <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-fog bg-white px-4 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#archive-{{ $training->id }}">Archive</button>
                                    @endif

                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-danger bg-white text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-{{ $training->id }}" aria-label="Delete permanen {{ $training->title }}" title="Delete Permanen">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5">
                                            <path d="M3.5 5.5h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7 5.5V4.25A1.25 1.25 0 0 1 8.25 3h3.5A1.25 1.25 0 0 1 13 4.25V5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M7.5 8.5v6.5M10 8.5V15M12.5 8.5v6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            <path d="M5.5 5.5l.6 10.3A1.5 1.5 0 0 0 7.6 17h4.8a1.5 1.5 0 0 0 1.5-1.2l.6-10.3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>

                                @if ($training->status === 'draft')
                                    <x-modal.confirm
                                        id="publish-{{ $training->id }}"
                                        title="Publish Training"
                                        description="Training draft ini akan dipublish dan siap dipakai untuk penugasan."
                                        confirm-label="Publish"
                                        :action="route('admin.training.status', $training)"
                                        method="PATCH"
                                    >
                                        <input type="hidden" name="status" value="published">
                                    </x-modal.confirm>
                                @elseif ($training->status === 'published')
                                    <x-modal.confirm
                                        id="archive-{{ $training->id }}"
                                        title="Archive Training"
                                        description="Training published ini akan diarsipkan dan tidak tersedia untuk penugasan baru."
                                        confirm-label="Archive"
                                        :action="route('admin.training.status', $training)"
                                        method="PATCH"
                                    >
                                        <input type="hidden" name="status" value="archived">
                                    </x-modal.confirm>
                                @endif

                                <x-modal.confirm
                                    id="delete-{{ $training->id }}"
                                    title="Delete Permanen Training"
                                    description="Training yang dihapus permanen tidak dapat dikembalikan. Lanjutkan?"
                                    confirm-label="Delete Permanen"
                                    :danger="true"
                                    :action="route('admin.training.destroy', $training)"
                                    method="DELETE"
                                />
                            </td>
                        </x-table.row>
                    @empty
                        <x-table.empty
                            colspan="7"
                            :title="$hasFilters ? 'Tidak ada training sesuai filter.' : 'Belum ada data training.'"
                            :description="$hasFilters ? 'Coba ubah search, status, bulan, atau tahun filter.' : 'Tambah training pertama untuk memulai pengelolaan data training.'"
                            :action-label="$hasFilters ? 'Reset Filter' : 'Tambah Training'"
                            :action-href="$hasFilters ? route('admin.training.index') : route('admin.training.create')"
                        />
                    @endforelse
                </tbody>
            </x-table.table>

            @if ($trainings->hasPages())
                <div class="border-t border-fog px-6 py-4">
                    {{ $trainings->links() }}
                </div>
            @endif
        </x-card.base>
    </div>
</x-layouts.admin>
