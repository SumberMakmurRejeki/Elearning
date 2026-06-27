<x-layouts.employee title="Detail Training - {{ config('app.name') }}">
    <div class="space-y-6">
        @if ($hasError ?? false)
            <x-error-state title="Gagal memuat detail training" description="Data detail training gagal dimuat." action-label="Kembali" />
        @else
            <x-page.header
                eyebrow="Portal Karyawan"
                title="Detail Training"
                description="Informasi training, progress, dan langkah training yang sedang berlangsung untuk akun Anda."
            >
                <x-button.link href="{{ route('employee.training.index') }}">Kembali ke Training Saya</x-button.link>
            </x-page.header>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)]">
                <x-card.base class="space-y-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="space-y-2">
                            <div class="flex flex-wrap gap-2">
                                <x-badge :variant="$progressStatusVariant">{{ $progressStatusLabel }}</x-badge>
                                <x-badge :variant="$trainingStatusVariant">{{ $trainingStatusLabel }}</x-badge>
                            </div>
                            <h1 class="text-2xl font-semibold tracking-tight text-ink md:text-3xl">{{ $training->title }}</h1>
                            <p class="max-w-3xl text-sm leading-6 text-charcoal whitespace-pre-line">{{ $training->description ?: 'Tidak ada deskripsi training.' }}</p>
                        </div>
                    </div>

                    <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-2xl bg-cloud/70 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Ditugaskan</dt>
                            <dd class="mt-2 text-sm font-medium text-ink">{{ $assignedAt }}</dd>
                        </div>
                        <div class="rounded-2xl bg-cloud/70 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Deadline</dt>
                            <dd class="mt-2 text-sm font-medium text-ink">{{ $deadline }}</dd>
                        </div>
                        <div class="rounded-2xl bg-cloud/70 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Passing Grade</dt>
                            <dd class="mt-2 text-sm font-medium text-ink">{{ $passingGradeLabel }}</dd>
                        </div>
                        <div class="rounded-2xl bg-cloud/70 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Materi Aktif</dt>
                            <dd class="mt-2 text-2xl font-semibold text-ink">{{ $activeMaterialsCount }}</dd>
                        </div>
                        <div class="rounded-2xl bg-cloud/70 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Pre-Test Aktif</dt>
                            <dd class="mt-2 text-2xl font-semibold text-ink">{{ $preTestQuestionCount }}</dd>
                        </div>
                        <div class="rounded-2xl bg-cloud/70 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Post-Test Aktif</dt>
                            <dd class="mt-2 text-2xl font-semibold text-ink">{{ $postTestQuestionCount }}</dd>
                        </div>
                    </dl>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($stepCards as $step)
                            @php
                                $stepBadge = match ($step['status']) {
                                    'done' => 'success',
                                    'current' => 'info',
                                    'locked' => 'neutral',
                                    default => 'neutral',
                                };
                            @endphp
                            <div class="rounded-2xl border border-fog bg-white p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-lg font-semibold text-ink">{{ $step['label'] }}</h3>
                                    <x-badge :variant="$stepBadge">{{ $step['value'] }}</x-badge>
                                </div>
                                <p class="mt-3 text-sm text-charcoal">
                                    @if ($step['label'] === 'Pre-Test')
                                        Pre-test menentukan apakah Anda harus menyelesaikan langkah awal sebelum membuka materi.
                                    @elseif ($step['label'] === 'Materi')
                                        Materi menjadi langkah berikutnya setelah pre-test selesai atau bila training tidak memiliki pre-test.
                                    @elseif ($step['label'] === 'Post-Test')
                                        Post-test tersedia setelah materi selesai dan digunakan untuk menentukan hasil akhir.
                                    @else
                                        Hasil training akan tampil setelah proses selesai dan status akhir tersedia.
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                </x-card.base>

                <div class="space-y-6">
                    <x-card.base class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">Status Progress</h2>
                        <p class="text-sm text-charcoal">Status mengikuti data progress milik Anda. Training archived tetap dapat ditampilkan bila sudah pernah ditugaskan.</p>

                        <div class="space-y-3">
                            <div class="rounded-2xl bg-cloud/70 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status Progress</p>
                                <p class="mt-2 text-sm font-semibold text-ink">{{ $progressStatusLabel }}</p>
                            </div>
                            <div class="rounded-2xl bg-cloud/70 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Status Kelulusan</p>
                                <p class="mt-2 text-sm font-semibold text-ink">{{ $finalStatusLabel ?? 'Belum tersedia' }}</p>
                            </div>
                            @if ($showScoreToEmployee)
                                <div class="rounded-2xl bg-cloud/70 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Nilai Akhir</p>
                                    <p class="mt-2 text-sm font-semibold text-ink">{{ $finalScoreLabel }}</p>
                                </div>
                            @endif
                            @if ($hasPostTest)
                                <div class="rounded-2xl bg-cloud/70 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-graphite">Jumlah Attempt Post-Test</p>
                                    <p class="mt-2 text-sm font-semibold text-ink">{{ $attemptCount ?? 0 }}</p>
                                </div>
                            @endif
                        </div>
                    </x-card.base>

                    @if ($hasPostTest && ($retakeAllowed ?? false))
                        <x-card.base class="space-y-4 border-warning/20 bg-warning-soft/10">
                            <h2 class="text-lg font-semibold text-warning">Kesempatan Retake</h2>
                            <p class="text-sm text-charcoal">
                                @if ($retakeAttemptsRemaining !== null)
                                    Sisa kesempatan mengulang post-test: {{ $retakeAttemptsRemaining }}.
                                @else
                                    Retake post-test diizinkan tanpa batas kesempatan.
                                @endif
                                Nilai terbaik Anda akan digunakan sebagai nilai akhir.
                            </p>
                            <form method="POST" action="{{ route('employee.post-test.retake', $training) }}">
                                @csrf
                                <x-button.primary type="submit" class="w-full">Ulangi Post-Test</x-button.primary>
                            </form>
                        </x-card.base>
                    @endif

                    @if ($hasPostTest && ! ($retakeAllowed ?? false) && ! ($isPassed ?? false) && ! ($hasPendingEssay ?? false) && ($attemptCount ?? 0) > 0)
                        <x-card.base class="space-y-4 border-fog">
                            <h2 class="text-lg font-semibold text-ink">Post-Test Selesai</h2>
                            <p class="text-sm text-charcoal">
                                @if (($training->allow_post_test_retake ?? false) && ($training->max_post_test_attempt ?? null) !== null && ($attemptCount ?? 0) >= $training->max_post_test_attempt)
                                    Kesempatan mengulang post-test sudah habis.
                                @else
                                    Anda belum memenuhi syarat untuk mengulang post-test.
                                @endif
                            </p>
                        </x-card.base>
                    @endif

                    <x-card.base class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">Aksi Training</h2>
                        <p class="text-sm text-charcoal">Tombol berikut masih berupa placeholder untuk task langkah selanjutnya.</p>

                    <div class="flex flex-col gap-3">
                        @if ($activeMaterialsCount > 0)
                            <x-button.link href="{{ route('employee.material.index', $training) }}" variant="primary">Lihat Materi</x-button.link>
                        @endif

                        @if ($primaryAction)
                            <x-button.link href="{{ $primaryAction['href'] }}">{{ $primaryAction['label'] }}</x-button.link>
                        @endif

                        @if ($secondaryAction)
                            <x-button.link href="{{ $secondaryAction['href'] }}">{{ $secondaryAction['label'] }}</x-button.link>
                        @endif
                    </div>
                    </x-card.base>
                </div>
            </div>
        @endif
    </div>
</x-layouts.employee>
