<x-layouts.admin title="Soal Test - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Master Training"
            title="Soal Test"
            description="Kelola soal pre-test dan post-test, termasuk pilihan ganda, essay, bobot nilai, dan status aktif soal."
        >
            <x-button.link href="{{ route('admin.soal.create') }}" variant="primary">Tambah Soal</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <x-card.base>
            <form method="GET" action="{{ route('admin.soal.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px_180px_220px_180px_auto]">
                <x-form.input
                    label="Search soal"
                    name="q"
                    type="search"
                    placeholder="Cari pertanyaan soal"
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
                    label="Jenis Test"
                    name="test_type"
                    :options="$testTypeOptions"
                    :selected="$testType"
                    placeholder="Semua"
                />

                <x-form.select
                    label="Jenis Soal"
                    name="question_type"
                    :options="$questionTypeOptions"
                    :selected="$questionType"
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
                    <x-button.link href="{{ route('admin.soal.index') }}" variant="text">Reset</x-button.link>
                </div>
            </form>
        </x-card.base>

        <x-card.base class="p-0">
            <x-table.table>
                <x-table.header>
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Training</th>
                        <th class="px-6 py-4">Jenis Test</th>
                        <th class="px-6 py-4">Jenis Soal</th>
                        <th class="px-6 py-4">Pertanyaan Singkat</th>
                        <th class="px-6 py-4">Bobot Nilai</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Tanggal Dibuat</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </x-table.header>

                <tbody>
                    @forelse ($questions as $question)
                        <x-table.row>
                            <td class="px-6 py-4 text-sm text-graphite">{{ $questions->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ $question->training?->title ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$question->test_type === 'pre_test' ? 'info' : 'ink'">{{ $question->test_type === 'pre_test' ? 'Pre-Test' : 'Post-Test' }}</x-badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$question->question_type === 'multiple_choice' ? 'info' : 'warning'">{{ $question->question_type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ \Illuminate\Support\Str::limit($question->question_text, 90) }}</td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ number_format((float) $question->weight, 0) }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$question->is_active ? 'success' : 'neutral'">{{ $question->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-charcoal">{{ optional($question->created_at)->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <x-button.link href="{{ route('admin.soal.show', $question) }}" variant="icon" aria-label="Detail soal" title="Detail">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M2.5 10s2.8-4.5 7.5-4.5S17.5 10 17.5 10s-2.8 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
                                    </x-button.link>
                                    <x-button.link href="{{ route('admin.soal.edit', $question) }}" variant="icon" aria-label="Edit soal" title="Edit">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M4 13.8V16h2.2l8.1-8.1-2.2-2.2L4 13.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M11.8 5.7l2.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                    </x-button.link>
                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fog bg-white text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-{{ $question->id }}" aria-label="Toggle soal" title="{{ $question->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M10 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M7.2 5.2a6 6 0 1 0 5.6 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                    </button>
                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-danger bg-white text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-{{ $question->id }}" aria-label="Delete soal" title="Delete Permanen">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-4.5 w-4.5"><path d="M3.5 5.5h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M7 5.5V4.25A1.25 1.25 0 0 1 8.25 3h3.5A1.25 1.25 0 0 1 13 4.25V5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M7.5 8.5v6.5M10 8.5V15M12.5 8.5v6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M5.5 5.5l.6 10.3A1.5 1.5 0 0 0 7.6 17h4.8a1.5 1.5 0 0 0 1.5-1.2l.6-10.3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>

                                <x-modal.confirm
                                    id="toggle-{{ $question->id }}"
                                    :title="$question->is_active ? 'Nonaktifkan Soal' : 'Aktifkan Soal'"
                                    :description="$question->is_active ? 'Soal aktif ini akan dinonaktifkan.' : 'Soal nonaktif ini akan diaktifkan kembali.'"
                                    :confirm-label="$question->is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                    :danger="$question->is_active"
                                    :action="route('admin.soal.status', $question)"
                                    method="PATCH"
                                >
                                    <input type="hidden" name="is_active" value="{{ $question->is_active ? 0 : 1 }}">
                                </x-modal.confirm>

                                <x-modal.confirm
                                    id="delete-{{ $question->id }}"
                                    title="Delete Permanen Soal"
                                    description="Soal yang dihapus permanen tidak dapat dikembalikan. Lanjutkan?"
                                    confirm-label="Delete Permanen"
                                    :danger="true"
                                    :action="route('admin.soal.destroy', $question)"
                                    method="DELETE"
                                />
                            </td>
                        </x-table.row>
                    @empty
                        <x-table.empty
                            colspan="9"
                            :title="$hasFilters ? 'Tidak ada soal sesuai filter.' : 'Belum ada data soal test.'"
                            :description="$hasFilters ? 'Coba ubah filter training, jenis test, jenis soal, status, atau kata kunci pencarian.' : 'Tambah soal pertama untuk mulai melengkapi bank soal training.'"
                            :action-label="$hasFilters ? 'Reset Filter' : 'Tambah Soal'"
                            :action-href="$hasFilters ? route('admin.soal.index') : route('admin.soal.create')"
                        />
                    @endforelse
                </tbody>
            </x-table.table>

            @if ($questions->hasPages())
                <div class="border-t border-fog px-6 py-4">
                    {{ $questions->links() }}
                </div>
            @endif
        </x-card.base>
    </div>
</x-layouts.admin>
