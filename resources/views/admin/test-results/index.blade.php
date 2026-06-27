<x-layouts.admin title="Hasil Test - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Penilaian"
            title="Hasil Test"
            description="Lihat daftar hasil test karyawan, filter berdasarkan training, divisi, jabatan, jenis test, status penilaian, dan kelulusan."
        />

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        @if ($hasError)
            <x-error-state title="Gagal memuat hasil test" description="Data hasil test gagal dimuat." action-label="Muat Ulang" />
        @else
            <x-card.base>
                <form method="GET" action="{{ route('admin.hasil-test.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px_220px_220px_auto]">
                    <x-form.input label="Search hasil test" name="q" type="search" placeholder="Cari nama karyawan, NIP, atau nama training" :value="$query" />
                    <x-form.select label="Training" name="training_id" :options="$trainingOptions" :selected="$trainingId" placeholder="Semua" />
                    <x-form.select label="Divisi" name="division_id" :options="$divisionOptions" :selected="$divisionId" placeholder="Semua" />
                    <x-form.select label="Jabatan" name="position_id" :options="$positionOptions" :selected="$positionId" placeholder="Semua" />

                    <div class="flex items-end gap-2">
                        <x-button.primary type="submit">Cari / Filter</x-button.primary>
                        <x-button.link href="{{ route('admin.hasil-test.index') }}" variant="text">Reset</x-button.link>
                    </div>
                </form>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <x-form.select label="Jenis Test" name="test_type" :options="$testTypeOptions" :selected="$testType" placeholder="Semua" />
                    <x-form.select label="Status Penilaian" name="grading_status" :options="$gradingStatusOptions" :selected="$gradingStatus" placeholder="Semua" />
                    <x-form.select label="Status Kelulusan" name="pass_status" :options="$passStatusOptions" :selected="$passStatus" placeholder="Semua" />
                    <x-form.input label="Tanggal Dari" name="date_from" type="date" :value="$dateFrom" />
                    <x-form.input label="Tanggal Sampai" name="date_to" type="date" :value="$dateTo" />
                </div>
            </x-card.base>

            <x-card.base class="p-0">
                <x-table.table>
                    <x-table.header>
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Karyawan</th>
                            <th class="px-6 py-4">NIP</th>
                            <th class="px-6 py-4">Divisi</th>
                            <th class="px-6 py-4">Jabatan</th>
                            <th class="px-6 py-4">Training</th>
                            <th class="px-6 py-4">Jenis Test</th>
                            <th class="px-6 py-4">Attempt</th>
                            <th class="px-6 py-4">Nilai MCQ</th>
                            <th class="px-6 py-4">Nilai Essay</th>
                            <th class="px-6 py-4">Nilai Akhir</th>
                            <th class="px-6 py-4">Status Penilaian</th>
                            <th class="px-6 py-4">Kelulusan</th>
                            <th class="px-6 py-4">Tanggal Submit</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </x-table.header>

                    <tbody>
                        @forelse ($attempts as $attempt)
                            <x-table.row>
                                <td class="px-6 py-4 text-sm text-graphite">{{ method_exists($attempts, 'firstItem') ? $attempts->firstItem() + $loop->index : $loop->iteration }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->employee?->user?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->employee?->employee_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->employee?->division?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->employee?->position?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->training?->title ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->test_type === 'pre_test' ? 'Pre-Test' : 'Post-Test' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->attempt_number }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->mcq_score !== null ? number_format((float) $attempt->mcq_score, 2) : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->essay_score !== null ? number_format((float) $attempt->essay_score, 2) : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $attempt->final_score !== null ? number_format((float) $attempt->final_score, 2) : '-' }}</td>
                                <td class="px-6 py-4">
                                    <x-badge :variant="match ($attempt->grading_status) { 'auto_graded' => 'info', 'waiting_manual_review' => 'warning', 'manual_reviewed' => 'success', default => 'neutral' }">
                                        {{ match ($attempt->grading_status) { 'auto_graded' => 'Auto-Graded', 'waiting_manual_review' => 'Menunggu Penilaian', 'manual_reviewed' => 'Selesai Dinilai', default => $attempt->grading_status ?? '-' } }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($attempt->pass_status === 'passed')
                                        <x-badge variant="success">Lulus</x-badge>
                                    @elseif ($attempt->pass_status === 'failed')
                                        <x-badge variant="danger">Tidak Lulus</x-badge>
                                    @else
                                        <span class="text-xs text-graphite">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ optional($attempt->submitted_at)->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <x-button.link href="{{ route('admin.hasil-test.show', $attempt) }}" variant="icon" aria-label="Detail hasil test" title="Detail">
                                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
                                        </x-button.link>
                                    </div>
                                </td>
                            </x-table.row>
                        @empty
                            <x-table.empty
                                colspan="15"
                                :title="$hasFilters ? 'Tidak ada hasil test sesuai filter.' : 'Belum ada hasil test.'"
                                :description="$hasFilters ? 'Coba ubah filter training, divisi, jabatan, jenis test, atau status.' : 'Hasil test akan tampil di sini setelah karyawan mengerjakan test.'"
                                :action-label="$hasFilters ? 'Reset Filter' : null"
                                :action-href="$hasFilters ? route('admin.hasil-test.index') : null"
                            />
                        @endforelse
                    </tbody>
                </x-table.table>

                @if (method_exists($attempts, 'hasPages') && $attempts->hasPages())
                    <div class="border-t border-fog px-6 py-4">
                        {{ $attempts->links() }}
                    </div>
                @endif
            </x-card.base>
        @endif
    </div>
</x-layouts.admin>
