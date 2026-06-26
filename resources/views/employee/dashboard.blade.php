<x-layouts.employee title="Dashboard Karyawan - {{ config('app.name') }}">
    <div class="space-y-6">
        <x-page.header
            eyebrow="Portal Karyawan"
            title="Dashboard Karyawan"
            description="Ringkasan training Anda, status terkini, dan akses cepat ke daftar training yang ditugaskan."
        >
            <x-button.outline onclick="window.location.href='{{ route('employee.history.index') }}'">Lihat Riwayat</x-button.outline>
            <x-button.primary onclick="window.location.href='{{ route('employee.training.index') }}'">Training Saya</x-button.primary>
        </x-page.header>

        @if ($hasError)
            <x-error-state title="Gagal memuat dashboard" description="Data dashboard gagal dimuat." action-label="Muat Ulang" />
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($summaryCards as $card)
                    <x-card.stat :label="$card['label']" :value="$card['value']">{{ $card['description'] }}</x-card.stat>
                @endforeach
            </div>

            @if ($hasEmptyState)
                <x-empty-state
                    title="Belum ada training yang diberikan."
                    description="Training Anda akan tampil di sini setelah admin membuat penugasan."
                    action-label="Training Saya"
                    :action-href="route('employee.training.index')"
                />
            @else
                <x-card.base>
                    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-ink">Training Terbaru</h3>
                            <p class="text-sm text-charcoal">Daftar training terbaru yang ditugaskan kepada Anda.</p>
                        </div>
                        <x-button.link href="{{ route('employee.training.index') }}" variant="text">Lihat Semua</x-button.link>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($recentTrainings as $progress)
                            @php
                                $status = (string) $progress->status;
                                $statusLabel = match ($status) {
                                    'not_started' => 'Belum Mulai',
                                    'in_progress', 'in_material', 'pre_test_completed', 'material_completed', 'post_test_completed', 'waiting_essay_review' => 'Sedang Berjalan',
                                    'passed' => 'Lulus',
                                    'failed' => 'Tidak Lulus',
                                    'completed' => 'Selesai',
                                    default => $status,
                                };
                                $badgeVariant = match ($status) {
                                    'not_started' => 'neutral',
                                    'passed' => 'success',
                                    'failed' => 'danger',
                                    'completed' => 'ink',
                                    default => 'info',
                                };
                            @endphp
                            <x-card.base class="border-primary/20 bg-primary-soft/20">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h4 class="text-lg font-semibold text-ink">{{ $progress->training?->title ?? '-' }}</h4>
                                        <p class="mt-2 text-sm text-charcoal">{{ \Illuminate\Support\Str::limit($progress->training?->description ?: 'Training ditugaskan kepada Anda.', 100) }}</p>
                                    </div>
                                    <x-badge :variant="$badgeVariant">{{ $statusLabel }}</x-badge>
                                </div>
                                <p class="mt-4 text-sm text-graphite">Ditugaskan: {{ optional($progress->assignment?->assigned_at)->translatedFormat('d M Y') ?? optional($progress->created_at)->translatedFormat('d M Y') ?? '-' }}</p>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <x-button.link href="{{ route('employee.training.show', $progress->training) }}" variant="primary">Masuk Training</x-button.link>
                                    <x-button.link href="{{ route('employee.training.show', $progress->training) }}">Detail</x-button.link>
                                </div>
                            </x-card.base>
                        @endforeach
                    </div>

                </x-card.base>
            @endif
        @endif
    </div>
</x-layouts.employee>
