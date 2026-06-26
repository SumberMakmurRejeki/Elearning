<x-layouts.admin title="Penugasan Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Penugasan Training"
            description="Kelola penugasan training ke karyawan tertentu, divisi, atau jabatan, beserta status progress awalnya."
        >
            <x-button.link href="{{ route('admin.penugasan.create') }}" variant="primary">Tambah Penugasan</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            <form method="GET" action="{{ route('admin.penugasan.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px_220px_220px_220px_auto]">
                <x-form.input
                    label="Search penugasan"
                    name="q"
                    type="search"
                    placeholder="Cari nama karyawan atau nama training"
                    :value="$query"
                />

                <x-form.select label="Training" name="training_id" :options="$trainingOptions" :selected="$trainingId" placeholder="Semua" />
                <x-form.select label="Divisi" name="division_id" :options="$divisionOptions" :selected="$divisionId" placeholder="Semua" />
                <x-form.select label="Jabatan" name="position_id" :options="$positionOptions" :selected="$positionId" placeholder="Semua" />
                <x-form.select label="Status Progress" name="status" :options="$statusOptions" :selected="$status" />

                <div class="flex items-end gap-2">
                    <x-button.primary type="submit">Cari / Filter</x-button.primary>
                    <x-button.link href="{{ route('admin.penugasan.index') }}" variant="text">Reset</x-button.link>
                </div>
            </form>
        </x-card.base>

        <x-card.base class="p-0">
            <x-table.table>
                <x-table.header>
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Training</th>
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Divisi</th>
                        <th class="px-6 py-4">Jabatan</th>
                        <th class="px-6 py-4">Status Progress</th>
                        <th class="px-6 py-4">Tanggal Ditugaskan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </x-table.header>

                <tbody>
                    @forelse ($progressRecords as $progress)
                        <x-table.row>
                            <td class="px-6 py-4 text-sm text-graphite">{{ $progressRecords->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ $progress->training?->title ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ $progress->employee?->user?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ $progress->employee?->division?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ $progress->employee?->position?->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="match ($progress->status) { 'not_started' => 'neutral', 'passed' => 'success', 'failed' => 'danger', default => 'info' }">
                                    {{ match ($progress->status) { 'not_started' => 'Belum Mulai', 'in_material' => 'Sedang Berjalan', 'material_completed' => 'Materi Selesai', 'post_test_completed' => 'Post-Test Selesai', 'passed' => 'Lulus', 'failed' => 'Tidak Lulus', 'completed' => 'Selesai', default => $progress->status } }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ optional($progress->assignment?->assigned_at)->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <x-button.link href="{{ route('admin.penugasan.show', $progress) }}" variant="icon" aria-label="Detail penugasan" title="Detail">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
                                    </x-button.link>

                                    @if ($progress->status === 'not_started')
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-danger bg-white text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#cancel-{{ $progress->id }}" aria-label="Batalkan penugasan" title="Batalkan Penugasan">
                                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M3.5 5.5h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M7 5.5V4.25A1.25 1.25 0 0 1 8.25 3h3.5A1.25 1.25 0 0 1 13 4.25V5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M7.5 8.5v6.5M10 8.5V15M12.5 8.5v6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M5.5 5.5l.6 10.3A1.5 1.5 0 0 0 7.6 17h4.8a1.5 1.5 0 0 0 1.5-1.2l.6-10.3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                        </button>

                                        <x-modal.confirm id="cancel-{{ $progress->id }}" title="Batalkan Penugasan Training" description="Penugasan yang dibatalkan akan menghapus progress awal karyawan untuk training ini." confirm-label="Batalkan Penugasan" :danger="true" :action="route('admin.penugasan.destroy', $progress)" method="DELETE" />
                                    @endif
                                </div>
                            </td>
                        </x-table.row>
                    @empty
                        <x-table.empty
                            colspan="8"
                            :title="$hasFilters ? 'Tidak ada penugasan sesuai filter.' : 'Belum ada data penugasan training.'"
                            :description="$hasFilters ? 'Coba ubah filter training, divisi, jabatan, status progress, atau kata kunci pencarian.' : 'Tambah penugasan pertama untuk mulai mendistribusikan training ke karyawan.'"
                            :action-label="$hasFilters ? 'Reset Filter' : 'Tambah Penugasan'"
                            :action-href="$hasFilters ? route('admin.penugasan.index') : route('admin.penugasan.create')"
                        />
                    @endforelse
                </tbody>
            </x-table.table>

            @if ($progressRecords->hasPages())
                <div class="border-t border-fog px-6 py-4">
                    {{ $progressRecords->links() }}
                </div>
            @endif
        </x-card.base>
    </div>
</x-layouts.admin>
