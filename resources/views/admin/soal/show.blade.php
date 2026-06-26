<x-layouts.admin title="Detail Soal - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header eyebrow="Master Training" title="Detail Soal" description="Lihat detail soal test, bobot nilai, status, dan opsi jawaban sebelum melakukan aksi lanjutan.">
            <x-button.link href="{{ route('admin.soal.index') }}" variant="text">Kembali</x-button.link>
            <x-button.link href="{{ route('admin.soal.edit', $question) }}" variant="primary">Edit Soal</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)]">
            <x-card.base>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-graphite">Training</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $question->training?->title ?? '-' }}</h2>
                    </div>

                    <x-badge :variant="$question->is_active ? 'success' : 'neutral'">{{ $question->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                </div>

                <dl class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jenis Test</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $question->test_type === 'pre_test' ? 'Pre-Test' : 'Post-Test' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jenis Soal</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $question->question_type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Nomor Soal</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $question->order_number }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Bobot Nilai</dt><dd class="mt-2 text-sm font-medium text-ink">{{ number_format((float) $question->weight, 0) }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Dibuat</dt><dd class="mt-2 text-sm font-medium text-ink">{{ optional($question->created_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Tanggal Diperbarui</dt><dd class="mt-2 text-sm font-medium text-ink">{{ optional($question->updated_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd></div>
                </dl>

                <div class="mt-6 rounded-2xl border border-fog bg-white p-4">
                    <p class="text-sm font-semibold text-ink">Pertanyaan</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-charcoal">{{ $question->question_text }}</p>
                </div>

                @if ($question->question_type === 'multiple_choice')
                    <div class="mt-6 rounded-2xl border border-fog bg-white p-4">
                        <p class="text-sm font-semibold text-ink">Daftar Opsi Jawaban</p>
                        <ul class="mt-3 space-y-3">
                            @foreach ($question->options as $option)
                                <li class="flex items-start justify-between gap-4 rounded-xl border border-fog px-4 py-3 text-sm text-charcoal">
                                    <div>
                                        <span class="font-semibold text-ink">{{ $option->option_label }}.</span>
                                        <span>{{ $option->option_text }}</span>
                                    </div>
                                    @if ($option->is_correct)
                                        <x-badge variant="success">Jawaban Benar</x-badge>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-card.base>

            <div class="space-y-6">
                @if ($usedInTest)
                    <x-alert variant="warning" title="Soal sudah digunakan">
                        Soal ini sudah digunakan dalam test. Delete permanen dan perubahan struktur soal akan ditolak oleh sistem.
                    </x-alert>
                @endif

                <x-card.base>
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ink">Aksi Cepat</h3>
                        <p class="text-sm text-charcoal">Aktifkan/nonaktifkan soal atau hapus permanen jika soal belum pernah digunakan dalam test.</p>

                        <div class="flex flex-col gap-3">
                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-fog bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2" data-modal-open="#toggle-question-{{ $question->id }}">{{ $question->is_active ? 'Nonaktifkan Soal' : 'Aktifkan Soal' }}</button>
                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-danger bg-white px-4 py-2.5 text-sm font-semibold text-danger shadow-sm transition hover:bg-danger-soft focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2" data-modal-open="#delete-question-{{ $question->id }}">Delete Permanen</button>
                        </div>
                    </div>
                </x-card.base>
            </div>
        </div>

        <x-modal.confirm id="toggle-question-{{ $question->id }}" :title="$question->is_active ? 'Nonaktifkan Soal' : 'Aktifkan Soal'" :description="$question->is_active ? 'Soal aktif ini akan dinonaktifkan.' : 'Soal nonaktif ini akan diaktifkan kembali.'" :confirm-label="$question->is_active ? 'Nonaktifkan' : 'Aktifkan'" :danger="$question->is_active" :action="route('admin.soal.status', $question)" method="PATCH">
            <input type="hidden" name="is_active" value="{{ $question->is_active ? 0 : 1 }}">
        </x-modal.confirm>

        <x-modal.confirm id="delete-question-{{ $question->id }}" title="Delete Permanen Soal" description="Soal yang dihapus permanen tidak dapat dikembalikan." confirm-label="Delete Permanen" :danger="true" :action="route('admin.soal.destroy', $question)" method="DELETE" />
    </div>
</x-layouts.admin>
