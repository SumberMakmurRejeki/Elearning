<x-layouts.admin title="Detail Laporan Training - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Monitoring & Laporan"
            title="Detail Laporan Training"
            description="Daftar karyawan yang ditugaskan dan progress masing-masing pada training ini."
        >
            <x-button.link href="{{ route('admin.laporan.index') }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-graphite">Training</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $training->title }}</h2>
                    <p class="mt-1 text-sm text-charcoal">{{ $training->description ?? '-' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge :variant="$training->status === 'published' ? 'success' : ($training->status === 'archived' ? 'neutral' : 'warning')">
                        {{ ucfirst($training->status) }}
                    </x-badge>
                    @if ($training->passing_grade !== null)
                        <x-badge variant="info">Passing Grade: {{ number_format((float) $training->passing_grade, 0) }}</x-badge>
                    @endif
                </div>
            </div>
        </x-card.base>

        <x-card.base>
            <form method="GET" action="{{ route('admin.laporan.show', $training) }}" class="grid gap-4 xl:grid-cols-[repeat(4,minmax(0,1fr))_auto]">
                <x-form.select label="Divisi" name="division_id" :options="$divisionOptions" :selected="$divisionId" placeholder="Semua" />
                <x-form.select label="Jabatan" name="position_id" :options="$positionOptions" :selected="$positionId" placeholder="Semua" />
                <x-form.select label="Status Progress" name="progress_status" :options="$progressStatusOptions" :selected="$progressStatus" placeholder="Semua" />
                <x-form.select label="Status Kelulusan" name="final_status" :options="$finalStatusOptions" :selected="$finalStatus" placeholder="Semua" />

                <div class="flex items-end gap-2">
                    <x-button.primary type="submit">Filter</x-button.primary>
                    <x-button.link href="{{ route('admin.laporan.show', $training) }}" variant="text">Reset</x-button.link>
                </div>
            </form>
        </x-card.base>

        @if ($employeeRows->isEmpty())
            <x-empty-state
                title="Belum ada karyawan pada training ini"
                description="Tidak ada progress training untuk karyawan yang ditugaskan."
                :action-label="$hasFilters ? 'Reset Filter' : null"
                :action-href="$hasFilters ? route('admin.laporan.show', $training) : null"
            />
        @else
            <x-card.base class="p-0">
                <x-table.table>
                    <x-table.header>
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Karyawan</th>
                            <th class="px-6 py-4">NIP</th>
                            <th class="px-6 py-4">Divisi</th>
                            <th class="px-6 py-4">Jabatan</th>
                            <th class="px-6 py-4">Status Progress</th>
                            <th class="px-6 py-4">Nilai Pre-Test</th>
                            <th class="px-6 py-4">Nilai Post-Test</th>
                            <th class="px-6 py-4">Attempt Post-Test</th>
                            <th class="px-6 py-4">Kelulusan</th>
                        </tr>
                    </x-table.header>

                    <tbody>
                        @foreach ($employeeRows as $row)
                            <x-table.row>
                                <td class="px-6 py-4 text-sm text-graphite">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $row['employee']?->user?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $row['employee']?->employee_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $row['employee']?->division?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $row['employee']?->position?->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <x-badge :variant="match ($row['progress']->status) { 'not_started' => 'neutral', 'passed' => 'success', 'failed' => 'danger', 'waiting_essay_review' => 'warning', default => 'info' }">
                                        {{ $row['statusLabel'] }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-charcoal">
                                    @if ($row['latestPreTest']?->final_score !== null)
                                        {{ number_format((float) $row['latestPreTest']->final_score, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-charcoal">
                                    @if ($row['latestPostTest']?->final_score !== null)
                                        {{ number_format((float) $row['latestPostTest']->final_score, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-charcoal">
                                    {{ $row['latestPostTest']?->attempt_number ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($row['progress']->final_status === 'passed')
                                        <x-badge variant="success">Lulus</x-badge>
                                    @elseif ($row['progress']->final_status === 'failed')
                                        <x-badge variant="danger">Tidak Lulus</x-badge>
                                    @else
                                        <span class="text-xs text-graphite">-</span>
                                    @endif
                                </td>
                            </x-table.row>
                        @endforeach
                    </tbody>
                </x-table.table>
            </x-card.base>
        @endif
    </div>
</x-layouts.admin>
