<x-layouts.admin title="Detail Hasil Test - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Penilaian"
            title="Detail Hasil Test"
            description="Informasi lengkap hasil test untuk attempt tertentu."
        >
            <x-button.link href="{{ route('admin.hasil-test.index') }}" variant="text">Kembali</x-button.link>
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
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $attempt->training?->title ?? '-' }}</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge :variant="$attempt->test_type === 'pre_test' ? 'info' : 'success'">
                            {{ $attempt->test_type === 'pre_test' ? 'Pre-Test' : 'Post-Test' }}
                        </x-badge>
                        <x-badge variant="neutral">{{ $attemptLabel }}</x-badge>
                    </div>
                </div>

                <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Karyawan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $attempt->employee?->user?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Divisi</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $attempt->employee?->division?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jabatan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $attempt->employee?->position?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Waktu Mulai</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($attempt->started_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Waktu Submit</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ optional($attempt->submitted_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Passing Grade</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $attempt->training?->passing_grade !== null ? number_format((float) $attempt->training->passing_grade, 0) : '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Nilai MCQ</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $attempt->mcq_score !== null ? number_format((float) $attempt->mcq_score, 2) : '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Nilai Essay</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $attempt->essay_score !== null ? number_format((float) $attempt->essay_score, 2) : '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Nilai Akhir</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">{{ $attempt->final_score !== null ? number_format((float) $attempt->final_score, 2) : 'Belum tersedia' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status Penilaian</dt>
                        <dd class="mt-2">
                            <x-badge :variant="match ($attempt->grading_status) { 'auto_graded' => 'info', 'waiting_manual_review' => 'warning', 'manual_reviewed' => 'success', default => 'neutral' }">
                                {{ $gradingStatusLabel }}
                            </x-badge>
                        </dd>
                    </div>
                    <div class="rounded-2xl bg-cloud/60 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status Kelulusan</dt>
                        <dd class="mt-2 text-sm font-medium text-ink">
                            @if ($passStatusLabel === 'Lulus')
                                <x-badge variant="success">Lulus</x-badge>
                            @elseif ($passStatusLabel === 'Tidak Lulus')
                                <x-badge variant="danger">Tidak Lulus</x-badge>
                            @else
                                <span class="text-xs text-graphite">-</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($answers->isNotEmpty())
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-ink">Daftar Jawaban</h3>
                        <div class="space-y-4">
                            @foreach ($answers as $answer)
                                <div class="rounded-2xl border border-fog bg-white p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-primary-soft text-sm font-semibold text-primary">{{ $loop->iteration }}</span>
                                                <x-badge :variant="$answer->question?->question_type === 'multiple_choice' ? 'info' : 'warning'">
                                                    {{ $answer->question?->question_type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay' }}
                                                </x-badge>
                                                @if ($answer->question?->question_type === 'multiple_choice')
                                                    <x-badge :variant="$answer->is_correct ? 'success' : 'danger'">
                                                        {{ $answer->is_correct ? 'Benar' : 'Salah' }}
                                                    </x-badge>
                                                @endif
                                            </div>
                                            <p class="mt-3 text-sm font-medium text-ink">{{ $answer->question?->question_text ?? '-' }}</p>

                                            @if ($answer->question?->question_type === 'multiple_choice')
                                                <div class="mt-3 space-y-1 text-sm">
                                                    <p class="text-graphite">Jawaban karyawan: <span class="text-charcoal">{{ $answer->selectedOption?->option_text ?? 'Tidak dijawab' }}</span></p>
                                                    <p class="text-graphite">Jawaban benar: <span class="text-charcoal">{{ $answer->question->options->firstWhere('is_correct', true)?->option_text ?? '-' }}</span></p>
                                                    <p class="text-graphite">Bobot soal: <span class="text-charcoal">{{ number_format((float) $answer->question->weight, 2) }}</span></p>
                                                    <p class="text-graphite">Nilai: <span class="text-charcoal">{{ number_format((float) $answer->score, 2) }}</span></p>
                                                </div>
                                            @else
                                                <div class="mt-3 space-y-1 text-sm">
                                                    <p class="text-graphite">Jawaban karyawan:</p>
                                                    <div class="mt-1 rounded-xl bg-cloud/60 p-3 text-sm text-charcoal">{{ $answer->essay_answer ?? '-' }}</div>
                                                    @if ($answer->graded_at !== null)
                                                        <p class="mt-2 text-graphite">Nilai essay: <span class="font-medium text-charcoal">{{ number_format((float) $answer->score, 2) }}</span></p>
                                                    @else
                                                        <p class="mt-2 text-warning font-medium">Menunggu Penilaian</p>
                                                    @endif
                                                    <p class="text-graphite">Bobot soal: <span class="text-charcoal">{{ number_format((float) $answer->question->weight, 2) }}</span></p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-fog bg-cloud/60 px-6 py-12 text-center text-sm text-charcoal">
                        Tidak ada jawaban untuk attempt ini.
                    </div>
                @endif
            </x-card.base>

            <div class="space-y-6">
                <x-card.base class="space-y-4">
                    <h3 class="text-lg font-semibold text-ink">Catatan Sistem</h3>
                    <p class="text-sm text-charcoal">Halaman ini hanya untuk melihat data hasil test. Untuk mengubah nilai essay, silakan buka halaman Penilaian Essay.</p>
                </x-card.base>
            </div>
        </div>
    </div>
</x-layouts.admin>
