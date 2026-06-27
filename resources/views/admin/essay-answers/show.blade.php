<x-layouts.admin title="Nilai Jawaban Essay - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Penilaian"
            title="Nilai Jawaban Essay"
            description="Lihat jawaban essay karyawan dan simpan skor sesuai bobot maksimal soal."
        >
            <x-button.link href="{{ route('admin.essay-answers.index') }}" variant="text">Kembali</x-button.link>
        </x-page.header>

        @if (session('success'))
            <x-alert variant="success" title="Berhasil">{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" title="Gagal">{{ session('error') }}</x-alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)]">
            <x-card.base class="space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-graphite">Training</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $answer->attempt?->training?->title ?? '-' }}</h2>
                    </div>

                    <x-badge :variant="$answer->graded_at ? 'success' : 'warning'">{{ $answer->graded_at ? 'Sudah Dinilai' : 'Menunggu Penilaian' }}</x-badge>
                </div>

                <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Karyawan</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $answer->attempt?->employee?->user?->name ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jenis Test</dt><dd class="mt-2 text-sm font-medium text-ink">{{ $answer->attempt?->test_type === 'pre_test' ? 'Pre-Test' : 'Post-Test' }}</dd></div>
                    <div class="rounded-2xl bg-cloud/60 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Bobot Maksimal</dt><dd class="mt-2 text-sm font-medium text-ink">{{ number_format($maxScore, 2) }}</dd></div>
                </dl>

                <div class="rounded-2xl border border-fog bg-white p-4">
                    <p class="text-sm font-semibold text-ink">Pertanyaan Essay</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-charcoal">{{ $answer->question?->question_text ?? '-' }}</p>
                </div>

                <div class="rounded-2xl border border-fog bg-white p-4">
                    <p class="text-sm font-semibold text-ink">Jawaban Karyawan</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-charcoal">{{ $answer->essay_answer ?: '-' }}</p>
                </div>
            </x-card.base>

            <x-card.base>
                <form method="POST" action="{{ route('admin.essay-answers.score', $answer) }}" class="space-y-4">
                    @csrf

                    <x-form.input
                        label="Skor Essay"
                        name="score"
                        type="number"
                        step="0.01"
                        min="0"
                        max="{{ $maxScore }}"
                        :value="$answer->score"
                        help="Nilai minimal 0 dan maksimal {{ number_format($maxScore, 2) }} sesuai bobot soal."
                    />

                    <div class="flex justify-end gap-3">
                        <x-button.link href="{{ route('admin.essay-answers.index') }}" variant="text">Batal</x-button.link>
                        <x-button.primary type="submit">Simpan Nilai</x-button.primary>
                    </div>
                </form>
            </x-card.base>
        </div>
    </div>
</x-layouts.admin>
