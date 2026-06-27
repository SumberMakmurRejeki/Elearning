<x-layouts.admin title="Laporan Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Monitoring & Laporan"
            title="Laporan Training"
            description="Ringkasan kondisi training per training, filter berdasarkan bulan, tahun, training, divisi, jabatan, dan status."
        >
            <div class="flex items-center gap-2">
                <x-button.link href="{{ route('admin.laporan.export-pdf', request()->query()) }}" variant="outline" aria-label="Export PDF">
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4 w-4"><path d="M10 2v11m0 0l-3.5-3.5m3.5 3.5l3.5-3.5M4 16.5V18h12v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Export PDF
                </x-button.link>
                <x-button.link href="{{ route('admin.laporan.export-excel', request()->query()) }}" variant="outline" aria-label="Export Excel">
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4 w-4"><path d="M10 2v11m0 0l-3.5-3.5m3.5 3.5l3.5-3.5M4 16.5V18h12v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Export Excel
                </x-button.link>
            </div>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        @if ($hasError)
            <x-error-state title="Gagal memuat laporan" description="Data laporan gagal dimuat." action-label="Muat Ulang" />
        @else
            <x-card.base>
                <form method="GET" action="{{ route('admin.laporan.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px_220px_220px_auto]">
                    <x-form.input label="Search laporan" name="q" type="search" placeholder="Cari nama training" :value="$query ?? ''" />
                    <x-form.select label="Bulan" name="month" :options="$monthOptions" :selected="$month" placeholder="Semua" />
                    <x-form.select label="Tahun" name="year" :options="$yearOptions" :selected="$year" placeholder="Semua" />
                    <x-form.select label="Training" name="training_id" :options="$trainingOptions" :selected="$trainingId" placeholder="Semua" />

                    <div class="flex items-end gap-2">
                        <x-button.primary type="submit">Terapkan Filter</x-button.primary>
                        <x-button.link href="{{ route('admin.laporan.index') }}" variant="text">Reset</x-button.link>
                    </div>
                </form>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <x-form.select label="Divisi" name="division_id" :options="$divisionOptions" :selected="$divisionId" placeholder="Semua" />
                    <x-form.select label="Jabatan" name="position_id" :options="$positionOptions" :selected="$positionId" placeholder="Semua" />
                    <x-form.select label="Status Progress" name="progress_status" :options="$progressStatusOptions" :selected="$progressStatus" placeholder="Semua" />
                    <x-form.select label="Status Kelulusan" name="final_status" :options="$finalStatusOptions" :selected="$finalStatus" placeholder="Semua" />
                </div>
            </x-card.base>

            @if ($hasEmptyState)
                <x-empty-state
                    title="Belum ada data laporan"
                    description="Tidak ada data training atau progress yang sesuai filter."
                    :action-label="$hasFilters ? 'Reset Filter' : null"
                    :action-href="$hasFilters ? route('admin.laporan.index') : null"
                />
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <x-card.stat label="Total Assignment" value="{{ $summary['total_assignments'] }}" data-summary="total_assignments">
                        Jumlah karyawan yang ditugaskan
                    </x-card.stat>
                    <x-card.stat label="Total Selesai" value="{{ $summary['total_completed'] }}" data-summary="total_completed">
                        Karyawan selesai training
                    </x-card.stat>
                    <x-card.stat label="Total Lulus" value="{{ $summary['total_passed'] }}" data-summary="total_passed">
                        Karyawan lulus training
                    </x-card.stat>
                    <x-card.stat label="Total Tidak Lulus" value="{{ $summary['total_failed'] }}" data-summary="total_failed">
                        Karyawan tidak lulus
                    </x-card.stat>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-card.stat label="Menunggu Penilaian" value="{{ $summary['total_waiting_review'] }}" data-summary="waiting_review">
                        Essay belum dinilai
                    </x-card.stat>
                    <x-card.stat label="Rata-rata Post-Test" value="{{ number_format($summary['avg_post_test'], 1) }}" data-summary="avg_post_test">
                        Rata-rata nilai post-test
                    </x-card.stat>
                </div>

                <x-card.base class="p-0">
                    <x-table.table>
                        <x-table.header>
                            <tr>
                                <th class="px-6 py-4">No</th>
                                <th class="px-6 py-4">Training</th>
                                <th class="px-6 py-4">Ditugaskan</th>
                                <th class="px-6 py-4">Belum Mulai</th>
                                <th class="px-6 py-4">Sedang Berjalan</th>
                                <th class="px-6 py-4">Selesai</th>
                                <th class="px-6 py-4">Lulus</th>
                                <th class="px-6 py-4">Tidak Lulus</th>
                                <th class="px-6 py-4">Menunggu</th>
                                <th class="px-6 py-4">Avg Pre</th>
                                <th class="px-6 py-4">Avg Post</th>
                                <th class="px-6 py-4">% Selesai</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </x-table.header>

                        <tbody>
                            @foreach ($reportRows as $row)
                                <x-table.row>
                                    <td class="px-6 py-4 text-sm text-graphite">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['training']->title }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['total_employees'] }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['not_started'] }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['in_progress'] }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['completed'] }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['passed'] }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['failed'] }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['waiting_review'] }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['avg_pre_test'] > 0 ? number_format((float) $row['avg_pre_test'], 1) : '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['avg_post_test'] > 0 ? number_format((float) $row['avg_post_test'], 1) : '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-charcoal">{{ $row['completion_pct'] }}%</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <x-button.link href="{{ route('admin.laporan.show', $row['training']) }}" variant="icon" aria-label="Detail laporan" title="Detail">
                                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
                                            </x-button.link>
                                        </div>
                                    </td>
                                </x-table.row>
                            @endforeach
                        </tbody>
                    </x-table.table>
                </x-card.base>
            @endif
        @endif
    </div>
</x-layouts.admin>
