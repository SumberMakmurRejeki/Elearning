<x-layouts.admin title="Monitoring Progress Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Monitoring & Laporan"
            title="Monitoring Progress Training"
            description="Lihat progress training seluruh karyawan, filter berdasarkan training, divisi, jabatan, dan status."
        />

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        @if ($hasError)
            <x-error-state title="Gagal memuat data progress" description="Data monitoring progress gagal dimuat." action-label="Muat Ulang" />
        @else
            <x-card.base>
                <form method="GET" action="{{ route('admin.progress.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px_220px_220px_220px_auto]">
                    <x-form.input label="Search monitoring" name="q" type="search" placeholder="Cari nama karyawan atau nama training" :value="$query" />
                    <x-form.select label="Training" name="training_id" :options="$trainingOptions" :selected="$trainingId" placeholder="Semua" />
                    <x-form.select label="Divisi" name="division_id" :options="$divisionOptions" :selected="$divisionId" placeholder="Semua" />
                    <x-form.select label="Jabatan" name="position_id" :options="$positionOptions" :selected="$positionId" placeholder="Semua" />
                    <x-form.select label="Status Progress" name="status" :options="$statusOptions" :selected="$status" />

                    <div class="flex items-end gap-2">
                        <x-button.primary type="submit">Cari / Filter</x-button.primary>
                        <x-button.link href="{{ route('admin.progress.index') }}" variant="text">Reset</x-button.link>
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
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Kelulusan</th>
                            <th class="px-6 py-4">Nilai</th>
                            <th class="px-6 py-4">Ditugaskan</th>
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
                                    <x-badge :variant="match ($progress->status) { 'not_started' => 'neutral', 'passed' => 'success', 'failed' => 'danger', 'waiting_essay_review' => 'warning', default => 'info' }">
                                        {{ match ($progress->status) { 'not_started' => 'Belum Mulai', 'pre_test_completed' => 'Pre-Test Selesai', 'in_material' => 'Sedang Berjalan', 'material_completed' => 'Materi Selesai', 'post_test_completed' => 'Post-Test Selesai', 'waiting_essay_review' => 'Menunggu Penilaian', 'passed' => 'Lulus', 'failed' => 'Tidak Lulus', default => $progress->status } }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($progress->final_status === 'passed')
                                        <x-badge variant="success">Lulus</x-badge>
                                    @elseif ($progress->final_status === 'failed')
                                        <x-badge variant="danger">Tidak Lulus</x-badge>
                                    @else
                                        <span class="text-xs text-graphite">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $progress->final_score !== null ? number_format((float) $progress->final_score, 2) : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ optional($progress->assignment?->assigned_at)->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <x-button.link href="{{ route('admin.progress.show', $progress) }}" variant="icon" aria-label="Detail progress" title="Detail">
                                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
                                        </x-button.link>
                                    </div>
                                </td>
                            </x-table.row>
                        @empty
                            <x-table.empty
                                colspan="10"
                                :title="$hasFilters ? 'Tidak ada data progress sesuai filter.' : 'Belum ada data progress training.'"
                                :description="$hasFilters ? 'Coba ubah filter training, divisi, jabatan, atau status progress.' : 'Data progress akan tampil di sini setelah karyawan ditugaskan ke training.'"
                                :action-label="$hasFilters ? 'Reset Filter' : null"
                                :action-href="$hasFilters ? route('admin.progress.index') : null"
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
        @endif
    </div>
</x-layouts.admin>
