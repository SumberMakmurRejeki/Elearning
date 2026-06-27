<x-layouts.admin title="Jawaban Essay - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Penilaian"
            title="Jawaban Essay"
            description="Lihat jawaban essay yang menunggu penilaian, filter berdasarkan training, karyawan, jenis test, dan status penilaian."
        />

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        @if ($hasError)
            <x-error-state title="Gagal memuat jawaban essay" description="Data jawaban essay gagal dimuat." action-label="Muat Ulang" />
        @else
            <x-card.base>
                <form method="GET" action="{{ route('admin.essay-answers.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px_220px_220px_220px_auto]">
                    <x-form.input label="Search jawaban essay" name="q" type="search" placeholder="Cari nama karyawan atau nama training" :value="$query" />
                    <x-form.select label="Training" name="training_id" :options="$trainingOptions" :selected="$trainingId" placeholder="Semua" />
                    <x-form.select label="Karyawan" name="employee_id" :options="$employeeOptions" :selected="$employeeId" placeholder="Semua" />
                    <x-form.select label="Jenis Test" name="test_type" :options="$testTypeOptions" :selected="$testType" placeholder="Semua" />
                    <x-form.select label="Status Penilaian" name="status" :options="$statusOptions" :selected="$status" placeholder="Semua" />

                    <div class="flex items-end gap-2">
                        <x-button.primary type="submit">Cari / Filter</x-button.primary>
                        <x-button.link href="{{ route('admin.essay-answers.index') }}" variant="text">Reset</x-button.link>
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
                            <th class="px-6 py-4">Jenis Test</th>
                            <th class="px-6 py-4">Pertanyaan</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Skor</th>
                            <th class="px-6 py-4">Tanggal Submit</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </x-table.header>

                    <tbody>
                        @forelse ($answers as $answer)
                            <x-table.row>
                                <td class="px-6 py-4 text-sm text-graphite">{{ method_exists($answers, 'firstItem') ? $answers->firstItem() + $loop->index : $loop->iteration }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $answer->attempt?->training?->title ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $answer->attempt?->employee?->user?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ $answer->attempt?->test_type === 'pre_test' ? 'Pre-Test' : 'Post-Test' }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ \Illuminate\Support\Str::limit($answer->question?->question_text ?? '-', 60) }}</td>
                                <td class="px-6 py-4">
                                    <x-badge :variant="$answer->graded_at ? 'success' : 'warning'">{{ $answer->graded_at ? 'Sudah Dinilai' : 'Menunggu Penilaian' }}</x-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ number_format((float) $answer->score, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-charcoal">{{ optional($answer->attempt?->submitted_at)->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <x-button.link href="{{ route('admin.essay-answers.show', $answer) }}" variant="primary">Nilai</x-button.link>
                                    </div>
                                </td>
                            </x-table.row>
                        @empty
                            <x-table.empty
                                colspan="9"
                                :title="$hasFilters ? 'Tidak ada jawaban essay sesuai filter.' : 'Belum ada jawaban essay.'"
                                :description="$hasFilters ? 'Coba ubah filter training, karyawan, jenis test, atau status penilaian.' : 'Jawaban essay akan tampil di sini setelah karyawan mengerjakan test essay.'"
                                :action-label="$hasFilters ? 'Reset Filter' : null"
                                :action-href="$hasFilters ? route('admin.essay-answers.index') : null"
                            />
                        @endforelse
                    </tbody>
                </x-table.table>

                @if (method_exists($answers, 'hasPages') && $answers->hasPages())
                    <div class="border-t border-fog px-6 py-4">
                        {{ $answers->links() }}
                    </div>
                @endif
            </x-card.base>
        @endif
    </div>
</x-layouts.admin>
